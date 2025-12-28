<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    exit("Accesso negato");
}

require "config/db.php";

// Recupera richieste pending
$req = $db->query("
    SELECT 
        vm_requests.id,
        vm_requests.hostname,
        users.username AS requester,
        vm_templates.name AS template_name,
        vm_templates.cpu,
        vm_templates.ram,
        vm_templates.disk
    FROM vm_requests
    JOIN users ON vm_requests.user_id = users.id
    JOIN vm_templates ON vm_requests.template_id = vm_templates.id
    WHERE vm_requests.status = 'pending'
    ORDER BY vm_requests.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pannello Admin - ProxMox Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand">ProxMox Portal - Admin</span>
        <a href="dashboard.php" class="btn btn-outline-light">Torna al Dashboard</a>
    </div>
</nav>

<div class="container mt-5">
    <h2>Richieste VM in attesa</h2>

    <?php if (empty($req)): ?>
        <div class="alert alert-info mt-4">
            Nessuna richiesta in attesa al momento.
        </div>
    <?php else: ?>
        <table class="table table-striped table-hover mt-4">
            <thead class="table-dark">
                <tr>
                    <th>Richiedente</th>
                    <th>Tipo VM</th>
                    <th>Hostname</th>
                    <th>Risorse</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($req as $r): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($r['requester']) ?></strong></td>
                        <td><?= htmlspecialchars($r['template_name']) ?></td>
                        <td><code><?= htmlspecialchars($r['hostname']) ?></code></td>
                        <td>
                            <?= $r['cpu'] ?> CPU<br>
                            <?= $r['ram']/1024 ?> GB RAM<br>
                            <?= $r['disk'] ?> GB disco
                        </td>
                        <td>
                            <a href="approve.php?id=<?= $r['id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Approvare?')">
                                Approva
                            </a>
                            <a href="reject.php?id=<?= $r['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Rifiutare?')">
                                Rifiuta
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-secondary mt-3">← Torna al Dashboard</a>
</div>
</body>
</html> 