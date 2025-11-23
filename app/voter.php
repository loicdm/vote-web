<?php
require 'db_config.php';

$message = '';
$scrutin_id = isset($_GET['scrutin_id']) ? intval($_GET['scrutin_id']) : 0;

// récupérer les candidats pour le scrutin via la table candidatures
$candidats = [];
if ($scrutin_id) {
    $stmt = $pdo->prepare("SELECT c.id, c.nom
                           FROM candidats c
                           INNER JOIN candidatures ca ON ca.candidat_id = c.id
                           WHERE ca.scrutin_id = ?");
    $stmt->execute([$scrutin_id]);
    $candidats = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// soumission du vote
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);
    
    // vérifier si le code existe et n'a pas été utilisé
    $stmt = $pdo->prepare("SELECT id, utilise FROM codes WHERE code = ?");
    $stmt->execute([$code]);
    $code_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$code_info) {
        $message = "Code invalide.";
    } elseif ($code_info['utilise']) {
        $message = "Code déjà utilisé.";
    } else {
        // construire le vote
        $vote_arr = [];
        foreach($candidats as $c) {
            $vote_arr[$c['id']] = isset($_POST['vote'][$c['id']]) ? intval($_POST['vote'][$c['id']]) : 0;
        }

        // enregistrer le vote unique en JSON
        $stmt = $pdo->prepare("INSERT INTO votes (scrutin_id, code_utilise, vote) VALUES (?, ?, ?)");
        $stmt->execute([$scrutin_id, $code, json_encode($vote_arr)]);

        // marquer le code comme utilisé
        $stmt = $pdo->prepare("UPDATE codes SET utilise = 1 WHERE id = ?");
        $stmt->execute([$code_info['id']]);

        $message = "Vote enregistré. Merci !";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Voter</title>
<style>
body { font-family: Arial; margin: 20px; }
label { display: block; margin-top: 10px; }
button { margin-top: 10px; }
</style>
</head>
<body>
<h1>Voter pour le scrutin</h1>
<?php if($message) echo "<p style='color:red;'>$message</p>"; ?>

<?php if($scrutin_id && $candidats): ?>
<form method="post">
<label>Code universel :</label>
<input type="text" name="code" required>

<h2>Candidats :</h2>
<?php foreach($candidats as $c): ?>
    <label>
        <?= htmlspecialchars($c['nom']) ?> :
        <select name="vote[<?= $c['id'] ?>]">
            <option value="1">Pour</option>
            <option value="0" selected>Pas d'avis</option>
            <option value="-1">Contre</option>
        </select>
    </label>
<?php endforeach; ?>

<button type="submit">Envoyer le vote</button>
</form>
<?php else: ?>
<p>Scrutin introuvable.</p>
<?php endif; ?>

</body>
</html>

