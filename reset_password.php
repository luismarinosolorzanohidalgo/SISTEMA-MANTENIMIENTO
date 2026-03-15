<?php
session_start();

if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

require_once "conexion.php";

// Validar ID
if (!isset($_GET['id'])) {
    header("Location: usuarios.php");
    exit();
}

$id = intval($_GET['id']);

// Si envía el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nueva = $_POST['password'];

    if (strlen($nueva) < 4) {
        echo json_encode(["status" => "short"]);
        exit();
    }

    $hash = password_hash($nueva, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id_usuario = ?");
    $stmt->bind_param("si", $hash, $id);

    if ($stmt->execute()) {
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
    <title>Restablecer Contraseña</title>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            background: #0f172a;
            color: #f8fafc;
            font-family: "Poppins", sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background: rgba(255,255,255,0.08);
            padding: 30px;
            width: 350px;
            border-radius: 14px;
            box-shadow: 0px 8px 20px rgba(0,0,0,0.4);
            backdrop-filter: blur(10px);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            background: rgba(255,255,255,0.15);
            border: none;
            padding: 12px;
            border-radius: 8px;
            margin-top: 8px;
            color: white;
            font-size: 15px;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            margin-top: 18px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 12px;
            color: #e2e8f0;
            text-decoration: none;
        }
    </style>

</head>
<body>

<div class="card">
    <h2>Nueva Contraseña</h2>

    <form id="formReset">

        <label>Escribe la nueva contraseña</label>
        <input type="password" name="password" placeholder="Nueva contraseña" required>

        <button class="btn" type="submit">Actualizar Contraseña</button>

    </form>

    <a class="back" href="usuarios.php">← Volver</a>
</div>

<script>
document.getElementById("formReset").addEventListener("submit", function(e){
    e.preventDefault();

    let form = new FormData(this);

    fetch("", { method: "POST", body: form })
    .then(r => r.json())
    .then(data => {

        if (data.status === "short") {
            Swal.fire({
                icon: "warning",
                title: "Contraseña muy corta",
                text: "Debe tener al menos 4 caracteres.",
            });
            return;
        }

        if (data.status === "success") {
            Swal.fire({
                icon: "success",
                title: "Contraseña Actualizada",
                text: "La contraseña se cambió correctamente.",
                confirmButtonColor: "#2563eb"
            }).then(() => {
                window.location = "usuarios.php";
            });
        } 
        else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo actualizar la contraseña.",
            });
        }
    });
});
</script>

</body>
</html>
