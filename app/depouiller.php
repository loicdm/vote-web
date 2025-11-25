<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Générer un nom unique pour éviter les collisions
        $uploadedFile = $uploadDir . uniqid('csv_', true) . '.csv';

        if (move_uploaded_file($_FILES['csv_file']['tmp_name'], $uploadedFile)) {
            // Récupérer les arguments et les sécuriser
            $nom_election = escapeshellarg($_POST['nom_election']);

            // Chemin vers le programme C compilé
            $Program = 'depouilleur';

            // Construire la commande
            $cmd = "$Program " . "$nom_election " . escapeshellarg($uploadedFile);

            // Exécuter le programme et récupérer la sortie
            $output = shell_exec($cmd);

            echo "<h3>Sortie du programme de dépouillement :</h3>";
            echo "<pre>" . $output . "</pre>";

            // Supprimer le CSV
            if (file_exists($uploadedFile)) {
                unlink($uploadedFile);
                echo "<p>Le fichier CSV a été supprimé du serveur.</p>";
            }
        } else {
            echo "<p>Erreur lors de l'upload du fichier.</p>";
        }
    } else {
        echo "<p>Aucun fichier sélectionné ou erreur d'upload.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Upload CSV et dépouillement</title>
</head>
<body>
    <h2>Uploader un fichier CSV et dépouiller</h2>
    <form method="post" enctype="multipart/form-data">
        <label>Fichier CSV : </label>
        <input type="file" name="csv_file" accept=".csv" required><br><br>

        <label>Nom du scrutin : </label>
        <input type="text" name="nom_election" required><br><br>

        <input type="submit" value="Exécuter le dépouillement">
    </form>
</body>
</html>

