<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role']!='admin') exit;
require "../config/db.php";

$db->prepare("UPDATE vm_requests SET status='approved' WHERE id=?")
   ->execute([$_GET['id']]);

// qui poi inserirai chiamata API ProxMox
header("Location: admin.php");
