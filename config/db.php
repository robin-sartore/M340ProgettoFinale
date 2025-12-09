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
        "root",        // user MySQL
        ""             // password (di solito vuota in XAMPP/MAMP)
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Errore DB: " . $e->getMessage());
}
?>