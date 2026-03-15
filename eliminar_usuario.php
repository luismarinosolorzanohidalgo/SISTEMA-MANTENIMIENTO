<?php
session_start();

if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

require_once "conexion.php";

// Validar ID recibido
if (!isset($_GET['id'])) {
    header("Location: usuarios.php");
    exit();
}

$id = intval($_GET['id']);

// ===============================
// SI CONFIRMA DESDE EL AJAX
// ===============================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $delete = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    $delete->bind_param("i", $id);

    if ($delete->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error"]);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Usuario</title>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            background: #0f172a;
            font-family: "Poppins", sans-serif;
        }
    </style>
</head>
<body>

<script>
// Al cargar la página, mostrar confirmación
Swal.fire({
    title: "¿Eliminar Usuario?",
    text: "Esta acción no se puede deshacer.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar"
}).then((result) => {
    if (result.isConfirmed) {

        // Enviar POST para eliminar
        fetch("", { method: "POST" })
        .then(r => r.json())
        .then(data => {

            if (data.status === "success") {
                Swal.fire({
                    icon: "success",
                    title: "Usuario eliminado",
                    text: "El usuario ha sido eliminado correctamente.",
                    confirmButtonColor: "#2563eb"
                }).then(() => {
                    window.location = "usuarios.php";
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo eliminar el usuario.",
                }).then(() => {
                    window.location = "usuarios.php";
                });
            }

        });

    } else {
        // Si cancela, retornar a usuarios
        window.location = "usuarios.php";
    }
});
</script>

</body>
</html>
