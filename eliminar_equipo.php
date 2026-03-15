<?php
session_start();

// Seguridad: solo usuarios logueados
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

require_once "conexion.php";

// Validar ID por GET
if (!isset($_GET['id'])) {
    header("Location: lista_equipos.php?msg=error_id");
    exit();
}

$id = intval($_GET['id']);

// Verificar si el equipo existe
$consulta = $conn->prepare("SELECT id_equipo FROM equipos WHERE id_equipo = ?");
$consulta->bind_param("i", $id);
$consulta->execute();
$resultado = $consulta->get_result();

if ($resultado->num_rows === 0) {
    header("Location: lista_equipos.php?msg=no_existe");
    exit();
}

// Proceder a eliminar
$delete = $conn->prepare("DELETE FROM equipos WHERE id_equipo = ?");
$delete->bind_param("i", $id);

if ($delete->execute()) {
    header("Location: lista_equipos.php?msg=eliminado");
} else {
    header("Location: lista_equipos.php?msg=error");
}

exit();
?>
