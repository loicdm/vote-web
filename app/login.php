<?php
require 'auth.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    // Comparaison avec les variables d'environnement
    if ($user === getenv('ADMIN_USER') && $pass === getenv('ADMIN_PASS')) {
        $_SESSION['logged_in'] = true;
        header("Location: admin_scrutins.php");
        exit;
    } else {
        $message = "Login ou mot de passe incorrect.";
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Connexion Admin</title>
<style>
body { font-family: Arial; background: #f2f2f7; display: flex; justify-content: center; align-items: center; height: 100vh; }
.login-card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.1); width: 300px; }
input { width: 100%; padding: 10px; margin-bottom: 10px; border-radius: 6px; border: 1px solid #ccc; }
button { width: 100%; padding: 10px; border: none; border-radius: 6px; background: #007aff; color: white; font-weight: bold; cursor: pointer; }
button:hover { background: #0066d6; }
.message { color: red; text-align: center; margin-bottom: 10px; }
</style>
</head>
<body>
<div class="login-card">
<h2>Connexion Admin</h2>
<?php if($message) echo "<p class='message'>$message</p>"; ?>
<form method="post">
    <input type="text" name="username" placeholder="Nom d'utilisateur" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <button type="submit">Se connecter</button>
</form>
</div>
</body>
</html>

