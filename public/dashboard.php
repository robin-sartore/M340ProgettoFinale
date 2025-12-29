<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require "config/db.php";
$uid = $_SESSION['user']['id'];

// Recupera le richieste dell'utente
$req = $db->prepare("
    SELECT 
        vm_requests.*, 
        vm_templates.name AS tname
    FROM vm_requests 
    JOIN vm_templates ON vm_requests.template_id = vm_templates.id
    WHERE vm_requests.user_id = ?
    ORDER BY vm_requests.id DESC
");
$req->execute([$uid]);
$rows = $req->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - ProxMox Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand">ProxMox Portal</span>
        <div>
            <span class="navbar-text me-3">Benvenuto, <?= htmlspecialchars($_SESSION['user']['username']) ?></span>
            <a href="logout.php" class="btn btn-outline-danger">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">Le tue macchine virtuali</h2>
            
            <a href="request_vm.php" class="btn btn-success btn-lg mb-4">Richiedi nuova VM</a>
            
            <?php if ($_SESSION['user']['role'] == 'admin'): ?>
                <a href="admin.php" class="btn btn-warning btn-lg mb-4 ms-2">Pannello Admin</a>
            <?php endif; ?>

            <?php if (empty($rows)): ?>
                <div class="alert alert-info">
                    Non hai ancora richiesto nessuna VM. <a href="request_vm.php">Richiedine una ora!</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Tipo VM</th>
                                <th>Hostname</th>
                                <th>Stato</th>
                                <th>Dettagli Accesso</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $r): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($r['tname']) ?></strong></td>
                                    <td><?= htmlspecialchars($r['hostname']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $r['status'] == 'approved' ? 'success' : ($r['status'] == 'pending' ? 'warning' : 'danger') ?>">
                                            <?= ucfirst($r['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($r['status'] == 'approved'): ?>
                                            <?php if (!empty($r['ip']) && strpos($r['ip'], 'attesa') === false && $r['ip'] !== 'Non rilevato'): ?>
                                                <div class="alert alert-success py-2 mb-0">
                                                    <strong>Accesso SSH pronto:</strong><br>
                                                    <code>ssh ubuntu@<?= $r['ip'] ?></code><br>
                                                    <strong>Password:</strong> <code>Password/1</code><br>
                                                    VMID: <strong><?= $r['vmid'] ?></strong>
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-warning py-2 mb-0">
                                                    VM creata (VMID: <strong><?= $r['vmid'] ?></strong>)<br>
                                                    IP in fase di assegnazione... (attendi 1-2 minuti e aggiorna la pagina)
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <em>In attesa di approvazione dall'amministratore</em>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>