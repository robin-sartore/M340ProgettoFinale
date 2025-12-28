<?php
session_start();

// solo admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    exit;
}

require "config/db.php";
// aggiorna stato richiesta
$db->prepare("UPDATE vm_requests SET status='rejected' WHERE id=?")
   ->execute([$_GET['id']]);

header("Location: admin.php");
exit;
?>