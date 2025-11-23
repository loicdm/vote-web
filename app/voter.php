<?php
require 'db_config.php';

// --- voter.php ---
$scrutin_id = $_GET['scrutin_id'] ?? null;
if (!$scrutin_id) {
    die('Scrutin introuvable.');
}

// Récupérer les candidats liés à ce scrutin
$stmt = $pdo->prepare("SELECT c.id, c.nom FROM candidats c INNER JOIN candidatures ca ON c.id = ca.candidat_id WHERE ca.scrutin_id = ?");
$stmt->execute([$scrutin_id]);
$candidats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$erreur = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $votes_post = $_POST['vote'] ?? [];

    // Vérifier le code disponible
    $stmt = $pdo->prepare("SELECT id FROM codes WHERE scrutin_id = ? AND code = ? AND utilise = 0");
    $stmt->execute([$scrutin_id, $code]);
    $code_disponible = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$code_disponible) {
        $erreur = 'Code invalide ou déjà utilisé.';
    } else {
        $pdo->beginTransaction();
        try {
            // Enregistrer les votes
            $stmt_vote = $pdo->prepare("INSERT INTO votes (scrutin_id, candidat_id, valeur) VALUES (?, ?, ?)");
            foreach ($candidats as $c) {
                $val = $votes_post[$c['id']] ?? 0;
                $stmt_vote->execute([$scrutin_id, $c['id'], $val]);
            }

            // Marquer le code comme utilisé
            $stmt_code = $pdo->prepare("UPDATE codes SET utilise = 1 WHERE id = ?");
            $stmt_code->execute([$code_disponible['id']]);

            $pdo->commit();
            $success = 'Vote enregistré avec succès !';
        } catch (Exception $e) {
            $pdo->rollBack();
            $erreur = 'Erreur lors de l'enregistrement du vote.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Voter - Scrutin <?= htmlspecialchars($scrutin_id) ?></title>
<style>
body { font-family: Arial; margin: 20px; }
.candidat { margin-bottom: 10px; }
button { margin-top: 20px; }
</style>
</head>
<body>
<h1>Scrutin: <?= htmlspecialchars($scrutin_id) ?></h1>
<?php if($erreur) echo "<p style='color:red;'>$erreur</p>"; ?>
<?php if($success) echo "<p style='color:green;'>$success</p>"; ?>
<form method="post">
<label>Code à usage unique:</label><br>
<input type='text' name='code' required><br><br>
<?php foreach($candidats as $c): ?>
<div class='candidat'>
<strong><?= htmlspecialchars($c['nom']) ?></strong><br>
<label><input type='radio' name='vote[<?= $c['id'] ?>]' value='1'> Pour</label>
<label><input type='radio' name='vote[<?= $c['id'] ?>]' value='-1'> Contre</label>
<label><input type='radio' name='vote[<?= $c['id'] ?>]' value='0' checked> Ne se prononce pas</label>
</div>
<?php endforeach; ?>
<button type='submit'>Voter</button>
</form>
</body>
</html>

