<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require "config/db.php";

$error = '';

// Carica template
$tpl = $db->query("SELECT * FROM vm_templates ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $template_id = (int)$_POST['template'];  // Cast a int per sicurezza
    $hostname = trim($_POST['hostname']);

    // Validazione base
    if (empty($hostname)) {
        $error = "Inserisci un hostname.";
    } elseif (strlen($hostname) < 3 || strlen($hostname) > 30) {
        $error = "Hostname deve essere tra 3 e 30 caratteri.";
    } elseif (!preg_match('/^[a-zA-Z0-9-]+$/', $hostname)) {
        $error = "Hostname: solo lettere, numeri e trattino (-).";
    } else {
        // Inserisci richiesta
        try {
            $stmt = $db->prepare("
                INSERT INTO vm_requests (user_id, template_id, hostname, status)
                VALUES (?, ?, ?, 'pending')
            ");
            $stmt->execute([$_SESSION['user']['id'], $template_id, $hostname]);
            header("Location: dashboard.php?success=1");
            exit;
        } catch (PDOException $e) {
            $error = "Errore salvataggio richiesta. Riprova.";
            error_log("DB error in request_vm: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Richiedi VM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>Richiedi una nuova VM</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Tipo di macchina</label>
                            <select name="template" class="form-select" required>
                                <option value="">-- Scegli un template --</option>
                                <?php foreach ($tpl as $t): ?>
                                    <option value="<?= $t['id'] ?>">
                                        <?= htmlspecialchars($t['name']) ?>
                                        (<?= $t['cpu'] ?> CPU, <?= $t['ram']/1024 ?> GB RAM, <?= $t['disk'] ?> GB disco)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hostname</label>
                            <input type="text" name="hostname" class="form-control"
                                   placeholder="es. web-server-01" maxlength="30" required>
                            <div class="form-text">Solo lettere, numeri e trattino. 3-30 caratteri.</div>
                        </div>

                        <button type="submit" class="btn btn-success w-100">Invia richiesta</button>
                    </form>

                    <div class="mt-3 text-center">
                        <a href="dashboard.php" class="btn btn-secondary">Annulla</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>