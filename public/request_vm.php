<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit; }
require "../config/db.php";

if ($_POST) {
  $stmt = $db->prepare(
    "INSERT INTO vm_requests (user_id,template_id,hostname,status)
     VALUES (?,?,?,'pending')"
  );
  $stmt->execute([$_SESSION['user']['id'],$_POST['template'],$_POST['hostname']]);
  header("Location: dashboard.php"); exit;
}

$tpl = $db->query("SELECT * FROM vm_templates")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Richiedi VM</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5 col-4">
  <h3>Richiesta VM</h3>
  <form method="POST">
    <select class="form-select mb-2" name="template">
      <?php foreach($tpl as $t): ?>
      <option value="<?= $t['id'] ?>">
        <?= $t['name'] ?> (<?= $t['cpu'] ?> CPU / <?= $t['ram'] ?>MB)
      </option>
      <?php endforeach ?>
    </select>
    <input class="form-control mb-2" name="hostname" placeholder="Hostname" required>
    <button class="btn btn-primary w-100">Invia</button>
  </form>
</div>
</body>
</html>
