<?php
// --- admin_scrutins.php ---
require 'db_config.php';

$message = '';
$codes_affiches = [];

// Supprimer un scrutin
if (isset($_POST['supprimer_id'])) {
    $stmt = $pdo->prepare("DELETE FROM scrutins WHERE id = ?");
    $stmt->execute([$_POST['supprimer_id']]);
    $message = 'Scrutin supprimé.';
}

// Générer des codes universels
if (isset($_POST['generer_codes']) && isset($_POST['nb_codes'])) {
    $nb = intval($_POST['nb_codes']);
    if ($nb > 0) {
        $stmt_code = $pdo->prepare("INSERT INTO codes (code) VALUES (?)");
        for ($i = 0; $i < $nb; $i++) {
            $code = bin2hex(random_bytes(4));
            $stmt_code->execute([$code]);
        }
        $message = $nb . ' codes universels générés.';
    }
}

// Afficher tous les codes
if (isset($_POST['afficher_codes'])) {
    $stmt_codes = $pdo->query("SELECT code, utilise FROM codes ORDER BY id ASC");
    $codes_affiches = $stmt_codes->fetchAll(PDO::FETCH_ASSOC);
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
<h2>Liste des scrutins</h2>
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
</td>
</tr>
<?php endforeach; ?>
</table>

<h2>Codes universels</h2>
<form method='post'>
<input type='number' name='nb_codes' min='1' placeholder='Nombre de codes à générer'>
<button type='submit' name='generer_codes'>Générer</button>
<button type='submit' name='afficher_codes'>Afficher tous les codes</button>
</form>

<?php if($codes_affiches): ?>
<h3>Liste des codes universels</h3>
<table>
<tr><th>Code</th><th>Utilisé</th></tr>
<?php foreach($codes_affiches as $c): ?>
<tr>
<td><?= htmlspecialchars($c['code']) ?></td>
<td><?= $c['utilise'] ? 'Oui' : 'Non' ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

</body>
</html>

