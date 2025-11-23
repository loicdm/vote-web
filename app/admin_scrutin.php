<?php
require 'db_config.php';

$message = '';

// Supprimer un scrutin
if (isset($_POST['supprimer_id'])) {
    $stmt = $pdo->prepare("DELETE FROM scrutins WHERE id = ?");
    $stmt->execute([$_POST['supprimer_id']]);
    $message = 'Scrutin supprimé.';
}

// Générer des codes supplémentaires
if (isset($_POST['generer_id']) && isset($_POST['nb_codes'])) {
    $nb = intval($_POST['nb_codes']);
    if ($nb > 0) {
        $stmt_code = $pdo->prepare("INSERT INTO codes (scrutin_id, code) VALUES (?, ?)");
        for ($i=0; $i<$nb; $i++) {
            $code = bin2hex(random_bytes(4));
            $stmt_code->execute([$_POST['generer_id'], $code]);
        }
        $message = $nb . ' codes générés.';
    }
}

// Liste des scrutins
$scrutins = $pdo->query("SELECT id, nom, date_creation FROM scrutins ORDER BY date_creation DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Administration des scrutins</title>
<style>
body { font-family: Arial; margin: 20px; }
table { border-collapse: collapse; width: 100%; }
td, th { border: 1px solid #ccc; padding: 8px; text-align: left; }
button { margin-top: 5px; }
</style>
</head>
<body>
<h1>Administration des scrutins</h1>
<?php if($message) echo "<p style='color:green;'>$message</p>"; ?>
<table>
<tr><th>ID</th><th>Nom</th><th>Date création</th><th>Actions</th></tr>
<?php foreach($scrutins as $s): ?>
<tr>
<td><?= $s['id'] ?></td>
<td><?= htmlspecialchars($s['nom']) ?></td>
<td><?= $s['date_creation'] ?></td>
<td>
<form method='post' style='display:inline;'>
<input type='hidden' name='supprimer_id' value='<?= $s['id'] ?>'>
<button type='submit' onclick="return confirm('Confirmer la suppression ?')">Supprimer</button>
</form>
<form method='post' style='display:inline; margin-left:5px;'>
<input type='hidden' name='generer_id' value='<?= $s['id'] ?>'>
<input type='number' name='nb_codes' min='1' placeholder='Nombre de codes'>
<button type='submit'>Générer codes</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>

