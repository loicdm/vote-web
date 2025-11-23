<?php
require 'db_config.php';

// --- creer_scrutin.php ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_scrutin = trim($_POST['nomScrutin']);
    $liste_candidats = trim($_POST['listeCandidats']);

    if ($nom_scrutin && $liste_candidats) {
        $pdo->beginTransaction();
        try {
            // Créer le scrutin
            $stmt = $pdo->prepare("INSERT INTO scrutins (nom) VALUES (?)");
            $stmt->execute([$nom_scrutin]);
            $scrutin_id = $pdo->lastInsertId();

            // Ajouter les candidats et les relier au scrutin
            $candidats = array_filter(array_map('trim', explode("\n", $liste_candidats)));
            foreach ($candidats as $c_nom) {
                // Vérifier si le candidat existe déjà
                $stmt_c = $pdo->prepare("SELECT id FROM candidats WHERE nom = ?");
                $stmt_c->execute([$c_nom]);
                $candidat = $stmt_c->fetch(PDO::FETCH_ASSOC);
                if ($candidat) {
                    $c_id = $candidat['id'];
                } else {
                    $stmt_insert = $pdo->prepare("INSERT INTO candidats (nom) VALUES (?)");
                    $stmt_insert->execute([$c_nom]);
                    $c_id = $pdo->lastInsertId();
                }
                // Lier candidat au scrutin
                $stmt_link = $pdo->prepare("INSERT INTO candidatures (scrutin_id, candidat_id) VALUES (?, ?)");
                $stmt_link->execute([$scrutin_id, $c_id]);
            }

            $pdo->commit();
            header('Location: voter.php?scrutin_id=' . $scrutin_id);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Erreur lors de la création du scrutin: ' . $e->getMessage();
        }
    } else {
        $error = 'Veuillez remplir tous les champs correctement.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Créer un Scrutin</title>
<style>
body { font-family: Arial; margin: 20px; }
textarea { width: 300px; height: 150px; }
</style>
</head>
<body>
<h1>Créer un Scrutin</h1>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="post">
<label>Nom du scrutin:</label><br>
<input type="text" name="nomScrutin" required><br><br>
<label>Liste des candidats (un par ligne):</label><br>
<textarea name="listeCandidats" required></textarea><br><br>
<button type="submit">Créer le scrutin</button>
</form>
</body>
</html>

