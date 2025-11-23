<?php
require 'db_config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_scrutin = trim($_POST['nom_scrutin']);
    $candidats_raw = trim($_POST['candidats']); // textarea renvoie une chaîne
    // séparer par ligne, enlever espaces et lignes vides
    $candidats = array_filter(array_map('trim', explode("\n", $candidats_raw)));

    if ($nom_scrutin && count($candidats)) {
        // créer le scrutin
        $stmt = $pdo->prepare("INSERT INTO scrutins (nom) VALUES (?)");
        $stmt->execute([$nom_scrutin]);
        $scrutin_id = $pdo->lastInsertId();

        // préparer les statements
        $stmt_sel = $pdo->prepare("SELECT id FROM candidats WHERE nom = ?");
        $stmt_ins = $pdo->prepare("INSERT INTO candidats (nom) VALUES (?)");
        $stmt_cand = $pdo->prepare("INSERT INTO candidatures (scrutin_id, candidat_id) VALUES (?, ?)");

        foreach ($candidats as $c) {
            if (!$c) continue;
            // vérifier si le candidat existe déjà
            $stmt_sel->execute([$c]);
            $row = $stmt_sel->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $candidat_id = $row['id'];
            } else {
                $stmt_ins->execute([$c]);
                $candidat_id = $pdo->lastInsertId();
            }

            // insérer dans candidatures
            $stmt_cand->execute([$scrutin_id, $candidat_id]);
        }

        $message = "Scrutin créé avec succès !";
    } else {
        $message = "Veuillez saisir un nom de scrutin et au moins un candidat.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Créer un scrutin</title>
<style>
body { font-family: Arial; margin: 20px; }
label { display: block; margin-top: 10px; }
textarea { width: 300px; }
button { margin-top: 10px; }
</style>
</head>
<body>
<h1>Créer un scrutin</h1>
<?php if($message) echo "<p style='color:green;'>$message</p>"; ?>

<form method="post">
<label>Nom du scrutin :</label>
<input type="text" name="nom_scrutin" required>

<label>Candidats (un par ligne) :</label>
<textarea name="candidats" rows="5" placeholder="Ex: Jean&#10;Claude&#10;Alice"></textarea>

<button type="submit">Créer le scrutin</button>
</form>
</body>
</html>

