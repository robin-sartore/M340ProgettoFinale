<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role']!='admin') exit("Accesso negato");
require "../config/db.php";

$req = $db->query(
 "SELECT vm_requests.*, users.username, vm_templates.name AS tname
  FROM vm_requests
  JOIN users ON vm_requests.user_id = users.id
  JOIN vm_templates ON vm_requests.template_id = vm_templates.id
  WHERE status='pending'"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
  <h3>Richieste in attesa</h3>

  <!-- Tasto per tornare al dashboard -->
  <a href="dashboard.php" class="btn btn-primary mb-3">Torna al dashboard</a>

  <table class="table table-striped">
    <tr>
      <th>Utente</th>
      <th>VM</th>
      <th>Hostname</th>
      <th>Azioni</th>
    </tr>
    <?php foreach($req as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r['username']) ?></td>
      <td><?= htmlspecialchars($r['tname']) ?></td>
      <td><?= htmlspecialchars($r['hostname']) ?></td>
      <td>
        <a class="btn btn-success btn-sm" href="approve.php?id=<?= $r['id'] ?>">Approva</a>
        <a class="btn btn-danger btn-sm" href="reject.php?id=<?= $r['id'] ?>">Rifiuta</a>
      </td>
    </tr>
    <?php endforeach ?>
  </table>
</div>
</body>
</html>
