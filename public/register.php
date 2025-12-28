<?php
session_start();
require "config/db.php";

if ($_POST) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = 'user';

    // Controllo username duplicato
    $stmt = $db->prepare("SELECT id FROM users WHERE username=?");
    $stmt->execute([$username]);

    if ($stmt->fetch()) {
        $error = "Username già esistente";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?,?,?)");
        $stmt->execute([$username, $hash, $role]);

        // Redirect diretto al login
        header("Location: login.php?registered=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registrati</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5 col-4">

    <h3>Registrazione</h3>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <input class="form-control mb-2" name="username" placeholder="Username" required>
        <input class="form-control mb-2" type="password" name="password" placeholder="Password" required>
        <button class="btn btn-primary w-100">Registrati</button>
    </form>

    <p class="mt-2">
        Hai già un account? <a href="login.php">Login</a>
    </p>

</div>
</body>
</html>
