<?php
$host = "localhost";
$user = "root";
$pass = "1234";
$db   = "proyecto_exhorto";
$port = 3307;  // puerto*******************

$con = new mysqli($host, $user, $pass, $db, $port);

if ($con->connect_error) {
    die("Error de conexión: " . $con->connect_error);
}

$con->set_charset("utf8mb4");
?>
