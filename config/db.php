<?php
// config/db.php
/*
try {
    $db = new PDO("sqlite:" . __DIR__ . "/../sql/portal.db");
   

    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Errore DB: " . $e->getMessage());
}*/
try {
    $db = new PDO(
        "mysql:host=localhost;dbname=progetto;charset=utf8",
         "root",
    ""      
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Errore DB: " . $e->getMessage());
}

$proxmox = [
    'host' => '192.168.56.15',
    'user' => 'root@pam',
    'token_id' => 'token-flask',
    'token_secret' => 'ed398414-ae55-4ef5-8e13-a7050941c3ce',
    'node' => 'px1'
];
?>