<?php
require 'db_config.php';

$message = '';
$scrutin_id = isset($_GET['scrutin_id']) ? intval($_GET['scrutin_id']) : 0;
$nom_scrutin = '';
// récupérer les candidats via candidatures
$candidats = [];
if ($scrutin_id) {
    $stmt = $pdo->prepare("SELECT c.id, c.nom
                           FROM candidats c
                           INNER JOIN candidatures ca ON ca.candidat_id = c.id
                           WHERE ca.scrutin_id = ?");
    $stmt->execute([$scrutin_id]);
    $candidats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT nom FROM scrutins WHERE id = ?");
    $stmt->execute([$scrutin_id]);
    $scrutin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($scrutin) {
        $nom_scrutin = $scrutin['nom'];
    } 

}

// soumission du vote
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $code = trim($_POST['code']);
    $type_vote = $_POST['type_vote'] ?? null;

    // vérifier le code
    $stmt = $pdo->prepare("SELECT id FROM codes WHERE code = ?");
    $stmt->execute([$code]);
    $code_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$code_info) {
        $message = "Code invalide.";
    } else {
     // vérifier si utilisé POUR CE SCRUTIN
    $stmt = $pdo->prepare("SELECT id FROM codes_utilises WHERE code_id = ? AND scrutin_id = ?");
    $stmt->execute([$code_info['id'], $scrutin_id]);
    $deja_utilise = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($deja_utilise) {
        $message = "Vous avez déjà voté pour ce scrutin avec ce code.";
      } else {
        // OK → enregistrer le vote
      // Initialiser tous les candidats à 0
        $vote_arr = [];
        foreach ($candidats as $c) {
            $vote_arr[$c['id']] = 0;
        }

        if ($type_vote === "pour") {

            $c_id = intval($_POST['candidat'] ?? 0);
            if ($c_id && isset($vote_arr[$c_id])) {
                $vote_arr[$c_id] = 1;
            } else {
                $message = "Veuillez choisir un candidat pour voter POUR.";
            }

        } elseif ($type_vote === "contre") {

            $c_id = intval($_POST['candidat'] ?? 0);
            if ($c_id && isset($vote_arr[$c_id])) {
                $vote_arr[$c_id] = -1;
            } else {
                $message = "Veuillez choisir un candidat pour voter CONTRE.";
            }

        } elseif ($type_vote === "neutre") {
            // tous restent à 0 → rien à faire
        }

        // si pas d’erreur
        if ($message === "") {
            $stmt = $pdo->prepare("INSERT INTO votes (scrutin_id, code_utilise, vote) 
                                   VALUES (?, ?, ?)");
            $stmt->execute([$scrutin_id, $code, json_encode($vote_arr)]);

            $stmt = $pdo->prepare("INSERT INTO codes_utilises (code_id, scrutin_id) VALUES (?, ?)");
            $stmt->execute([$code_info['id'], $scrutin_id]);

            $message = "Vote enregistré. Merci !";
        }

      }
            }
// fin
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Vote – <?= htmlspecialchars($nom_scrutin) ?></title>
<style>
body {
    font-family: "Segoe UI", Arial, sans-serif;
    margin: 20px;
    background: #f2f2f7;
}

h1 {
    text-align: center;
}

.vote-card {
    max-width: 480px;
    margin: auto;
    background: white;
    padding: 20px 25px;
    border-radius: 14px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}

.label-code {
    font-weight: bold;
}

.input-code {
    width: 80%;
    padding: 10px;
    font-size: 1em;
    border-radius: 6px;
    border: 1px solid #ccc;
    margin-bottom: 15px;
}

.vote-options {
    margin-bottom: 20px;
}

.vote-type {
    display: block;
    background: #fafafa;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 8px;
    cursor: pointer;
    border: 1px solid #ddd;
}

.vote-type:hover {
    background: #f0f0f0;
}

.vote-type input {
    margin-right: 8px;
}

.vote-select label {
    display: block;
    margin-top: 10px;
    margin-bottom: 4px;
}

.vote-select select {
    width: 100%;
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

.btn-submit {
    width: 100%;
    margin-top: 20px;
    padding: 12px;
    font-size: 1.1em;
    border: none;
    border-radius: 10px;
    background: #007aff;
    color: white;
    cursor: pointer;
}

.btn-submit:hover {
    background: #0066d6;
}

.message {
    color: #c00;
    text-align: center;
    font-weight: bold;
}
</style>

</head>
<body>

<h1>Scrutin : <?= htmlspecialchars($nom_scrutin) ?></h1>

<?php if($message): ?>
<p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<?php if($scrutin_id && $candidats): ?>

<form method="post" class="vote-card">

    <label class="label-code">Code :</label>
    <input type="text" name="code" class="input-code" required>

    <h2>Type de vote</h2>

    <div class="vote-options">
        <label class="vote-type">
            <input type="radio" name="type_vote" value="pour" required>
            <span>Voter POUR un candidat</span>
        </label>

        <label class="vote-type">
            <input type="radio" name="type_vote" value="contre">
            <span>Voter CONTRE un candidat</span>
        </label>

        <label class="vote-type">
            <input type="radio" name="type_vote" value="neutre">
            <span>Ne pas se prononcer</span>
        </label>
    </div>

    <div class="vote-select">
        <div>
            <label>Choisir le candidat (cas POUR/CONTRE) :</label>
            <select name="candidat">
                <option value="">-- Aucun --</option>
                <?php foreach($candidats as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <button type="submit" class="btn-submit">Envoyer le vote</button>

</form>

<?php else: ?>
<p>Scrutin introuvable.</p>
<?php endif; ?>

</body>
</html>

