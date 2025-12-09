<?php
session_start();
session_unset();   // svuota le variabili di sessione
session_destroy(); // distrugge la sessione
header("Location: login.php");
exit;
