<?php
require 'db_config.php';

$message = '';
$codes_affiches = [];

// Supprimer un scrutin
if (isset($_POST['supprimer_id'])) {
    $stmt = $pdo->prepare("DELETE FROM scrutins WHERE id = ?");
    $stmt->execute([$_POST['supprimer_id']]);
    $message = 'Scrutin supprimé.';
}

// Supprimer tous les scrutins
if (isset($_POST['supprimer_tous_scrutins'])) {
 // TRUNCATE votes, candidatures, codes, scrutins
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0"); // désactiver temporairement les contraintes FK
    $pdo->exec("TRUNCATE TABLE votes");
    $pdo->exec("TRUNCATE TABLE candidatures");
    $pdo->exec("TRUNCATE TABLE candidats"); 
    $pdo->exec("TRUNCATE TABLE codes");
    $pdo->exec("TRUNCATE TABLE scrutins");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1"); // réactiver les contraintes FK
    $message = 'Tous les scrutins et toutes les tables associées ont été réinitialisés.';
}

if (isset($_POST['export_scrutin_id'])) {
$scrutin_id = intval($_POST['export_scrutin_id']);


// Récupérer les candidats du scrutin
$stmt = $pdo->prepare("SELECT c.id, c.nom FROM candidats c
JOIN candidatures ca ON ca.candidat_id = c.id
WHERE ca.scrutin_id = ? ORDER BY c.id ASC");
$stmt->execute([$scrutin_id]);
$candidats = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Récupérer les votes du scrutin
$stmt = $pdo->prepare("SELECT vote FROM votes WHERE scrutin_id = ? ORDER BY id ASC");
$stmt->execute([$scrutin_id]);
$votes = $stmt->fetchAll(PDO::FETCH_ASSOC);


header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="scrutin_' . $scrutin_id . '.csv"');
$output = fopen('php://output', 'w');


// En-tête CSV
$header = array_map(function($c) { return $c['nom']; }, $candidats);
fputcsv($output, $header);


// Lignes de votes
foreach ($votes as $v) {
$vote_arr = json_decode($v['vote'], true);
$ligne = [];
foreach ($candidats as $c) {
$ligne[] = isset($vote_arr[$c['id']]) ? $vote_arr[$c['id']] : 0;
}
fputcsv($output, $ligne);
}


fclose($output);
exit;
}

// Générer des codes universels lisibles (adjectif-nom-nombre)
$adjectifs = ['Bleu','Rapide','Fort','Sage','Vif','Calme','Fou','Joli','Grand','Petit',
              'Rouge','Noir','Blanc','Vert','Jaune','Orange','Violet','Brave','Gentil','Féroce'];
$noms = ['Lion','Tigre','Renard','Aigle','Loup','Ours','Chat','Chien','Panther','Dragon',
         'Griffon','Serpent','Corbeau','Faucon','Requin','Phénix','Cerf','Singe','Taureau','Hibou'];

if (isset($_POST['generer_codes']) && isset($_POST['nb_codes'])) {
    $nb = intval($_POST['nb_codes']);
    if ($nb > 0) {
        $stmt_code = $pdo->prepare("INSERT INTO codes (code) VALUES (?)");
        for ($i=0; $i<$nb; $i++) {
            do {
                $adj = $adjectifs[array_rand($adjectifs)];
                $nom = $noms[array_rand($noms)];
                $nombre = str_pad(random_int(0,99),2,'0',STR_PAD_LEFT);
                $code = "$adj-$nom-$nombre";

                $stmt = $pdo->prepare("SELECT id FROM codes WHERE code=?");
                $stmt->execute([$code]);
                $existe = $stmt->fetch(PDO::FETCH_ASSOC);
            } while($existe);
            $stmt_code->execute([$code]);
        }
        $message = "$nb codes universels générés.";
    }
}

// Afficher tous les codes
if (isset($_POST['afficher_codes'])) {
    $stmt_codes = $pdo->query("SELECT id, code, utilise FROM codes ORDER BY id ASC");
    $codes_affiches = $stmt_codes->fetchAll(PDO::FETCH_ASSOC);
}

// Supprimer un code spécifique
if (isset($_POST['supprimer_code'])) {
    $stmt = $pdo->prepare("DELETE FROM codes WHERE id = ?");
    $stmt->execute([$_POST['supprimer_code']]);
    $message = 'Code supprimé.';
}

// Supprimer tous les codes
if (isset($_POST['supprimer_tous_codes'])) {
    $pdo->exec("TRUNCATE TABLE codes");
    $message = 'Tous les codes ont été supprimés.';
}

// Supprimer les codes utilisés
if (isset($_POST['supprimer_codes_utilises'])) {
    $pdo->exec("DELETE FROM codes WHERE utilise = 1");
    $message = 'Tous les codes utilisés ont été supprimés.';
}

// Liste des scrutins avec nombre de votes
$scrutins = $pdo->query("SELECT s.id, s.nom, s.date_creation, COUNT(v.id) AS nb_votes
                        FROM scrutins s
                        LEFT JOIN votes v ON v.scrutin_id = s.id
                        GROUP BY s.id
                        ORDER BY s.date_creation DESC")->fetchAll(PDO::FETCH_ASSOC);
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
button { margin-top: 5px; margin-right: 5px; }
a { text-decoration: none; color: blue; }
a:hover { text-decoration: underline; }
</style>
</head>
<body>
<h1>Administration des scrutins</h1>
<?php if($message) echo "<p style='color:green;'>$message</p>"; ?>


<h2>Liste des scrutins</h2>
<form method='post' style='margin-bottom:10px;'>
<button type='submit' name='supprimer_tous_scrutins' onclick="return confirm('Supprimer tous les scrutins ?')">Supprimer tous les scrutins</button>
</form>
<table>
<tr><th>ID</th><th>Nom</th><th>Date création (Votes)</th><th>Actions</th></tr>
<?php foreach($scrutins as $s): ?>
<tr>
<td><?= $s['id'] ?></td>
<td>
<a href="voter.php?scrutin_id=<?= $s['id'] ?>" target="_blank">
<?= htmlspecialchars($s['nom']) ?>
</a>
</td>
<td><?= $s['date_creation'] ?> (<?= $s['nb_votes'] ?> votes)</td>
<td>
<form method="post" style="display:inline;">
<input type="hidden" name="supprimer_id" value="<?= $s['id'] ?>">
<button type="submit" onclick="return confirm('Confirmer la suppression ?')">Supprimer</button>
</form>
<form method='post' style='display:inline;'>
<input type='hidden' name='export_scrutin_id' value='<?= $s['id'] ?>'>
<button type='submit'>Exporter CSV</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>


<h2>Codes universels</h2>
<form method="post">
<input type='number' name='nb_codes' min='1' placeholder='Nombre de codes à générer'>
<button type='submit' name='generer_codes'>Générer</button>
<button type='submit' name='afficher_codes'>Afficher tous les codes</button>
<button type='submit' name='supprimer_tous_codes' onclick="return confirm('Supprimer tous les codes ?')">Supprimer tous les codes</button>
<button type='submit' name='supprimer_codes_utilises' onclick="return confirm('Supprimer tous les codes utilisés ?')">Supprimer codes utilisés</button>
</form>

<?php if($codes_affiches): ?>
<h3>Liste des codes universels</h3>
<table>
<tr><th>ID</th><th>Code</th><th>Utilisé</th><th>Supprimer</th></tr>
<?php foreach($codes_affiches as $c): ?>
<tr>
<td><?= $c['id'] ?></td>
<td><?= htmlspecialchars($c['code']) ?></td>
<td><?= $c['utilise'] ? 'Oui' : 'Non' ?></td>
<td>
<form method='post' style='display:inline;'>
<input type='hidden' name='supprimer_code' value='<?= $c['id'] ?>'>
<button type='submit'>Supprimer</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

</body>
</html>

