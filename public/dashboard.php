<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit; }
require "../config/db.php";
$uid = $_SESSION['user']['id'];

$req = $db->prepare(
 "SELECT vm_requests.*, vm_templates.name AS tname 
  FROM vm_requests JOIN vm_templates 
  ON vm_requests.template_id = vm_templates.id
  WHERE user_id=?"
);
$req->execute([$uid]);
$rows = $req->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
  <span class="navbar-brand ms-3">ProxMox Portal</span>
  <a href="logout.php" class="btn btn-danger me-3">Logout</a>
</nav>

<div class="container mt-4">
  <a href="request_vm.php" class="btn btn-success">Richiedi VM</a>
  <?php if($_SESSION['user']['role']=='admin'): ?>
    <a href="admin.php" class="btn btn-warning">Admin</a>
  <?php endif; ?>

  <hr>
  <h4>Le tue richieste</h4>
  <table class="table">
    <tr><th>VM</th><th>Hostname</th><th>Stato</th></tr>
    <?php foreach($rows as $r): ?>
      <tr>
        <td><?= $r['tname'] ?></td>
        <td><?= $r['hostname'] ?></td>
        <td><?= $r['status'] ?></td>
      </tr>
    <?php endforeach ?>
  </table>
</div>
</body>
</html>
