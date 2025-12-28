<?php
session_start();
require "config/db.php";

if ($_POST) {
  $stmt = $db->prepare("SELECT * FROM users WHERE username=?");
  $stmt->execute([$_POST['username']]);
  $u = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($u && password_verify($_POST['password'], $u['password'])) {
    $_SESSION['user'] = $u;
    header("Location: dashboard.php");
    exit;
  }
  $error = "Credenziali errate";
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5 col-4">
  <h3>Login</h3>
  <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
  <?php if (isset($_GET['registered'])): ?>
    <div class="alert alert-success">
        Registrazione completata! Effettua il login.
    </div>
<?php endif; ?>

  <form method="POST">
    <input class="form-control mb-2" name="username" placeholder="Username" required>
    <input class="form-control mb-2" type="password" name="password" placeholder="Password" required>
    <button class="btn btn-primary w-100">Accedi</button>
  </form>
  <p class="mt-2">Non hai un account? <a href="register.php">Registrati</a></p>

</div>

</body>
</html>
