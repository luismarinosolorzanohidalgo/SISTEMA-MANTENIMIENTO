<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "equipos_db"; // <-- Base de datos del sistema de equipos

$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conn->connect_errno) {
    die("Error al conectar a la base de datos: " . $conn->connect_error);
}

// Forzar uso de UTF-8
$conn->set_charset("utf8mb4");
?>
