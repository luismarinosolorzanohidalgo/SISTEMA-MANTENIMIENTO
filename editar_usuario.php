<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

require_once "conexion.php";

if (!isset($_GET['id'])) {
    header("Location: usuarios.php");
    exit();
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    header("Location: usuarios.php");
    exit();
}

// Cuando envía el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST['nombre'];
    $user = $_POST['usuario'];
    $rol = $_POST['rol'];

    $update = $conn->prepare("UPDATE usuarios SET nombre=?, usuario=?, rol=? WHERE id_usuario=?");
    $update->bind_param("sssi", $nombre, $user, $rol, $id);

    if ($update->execute()) {
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
    <title>Editar Usuario</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Poppins", sans-serif;
            background: #0f172a;
            color: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* ===== SELECT MEJORADO ===== */
        select {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 18px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            color: #f1f5f9;
            /* texto más visible */
            font-size: 15px;
            appearance: none;
            /* quitar estilo nativo */
            -webkit-appearance: none;
            -moz-appearance: none;
            cursor: pointer;
            transition: all 0.3s ease;
            background-image: url("data:image/svg+xml;charset=US-ASCII,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16'><polygon points='0,0 16,0 8,8' fill='white'/></svg>");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 12px;
        }

        select:hover,
        select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #38bdf8;
            color: #f8fafc;
            box-shadow: 0 0 8px rgba(56, 189, 248, 0.5);
            outline: none;
        }

        option {
            background: #1e293b;
            /* color del fondo del dropdown */
            color: #f8fafc;
            /* color del texto */
        }

        .card {
            background: rgba(255, 255, 255, 0.08);
            padding: 35px;
            width: 420px;
            border-radius: 18px;
            backdrop-filter: blur(12px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: none;
            margin-top: 5px;
            font-size: 15px;
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 15px;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .back {
            display: block;
            text-align: center;
            color: #f8fafc;
            margin-top: 15px;
            text-decoration: none;
        }
    </style>

</head>

<body>

    <div class="card">
        <h2>Editar Usuario</h2>

        <form id="formEditar">

            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" value="<?= $usuario['nombre'] ?>" required>
            </div>

            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="usuario" value="<?= $usuario['usuario'] ?>" required>
            </div>

            <div class="form-group">
                <label>Rol</label>
                <select name="rol">
                    <option value="Administrador" <?= $usuario['rol'] == 'Administrador' ? 'selected' : '' ?>>Administrador</option>
                    <option value="Tecnico" <?= $usuario['rol'] == 'Tecnico' ? 'selected' : '' ?>>Técnico</option>
                    <option value="Usuario" <?= $usuario['rol'] == 'Usuario' ? 'selected' : '' ?>>Usuario</option>
                </select>
            </div>

            <button type="submit" class="btn">Actualizar Usuario</button>
        </form>

        <a class="back" href="usuarios.php">← Volver</a>
    </div>

    <script>
        document.getElementById("formEditar").addEventListener("submit", function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            fetch("", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(data => {

                    if (data.status === "success") {

                        Swal.fire({
                            icon: "success",
                            title: "Actualizado correctamente",
                            text: "Los cambios se guardaron con éxito.",
                            confirmButtonColor: "#2563eb"
                        }).then(() => {
                            window.location = "usuarios.php";
                        });

                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Ocurrió un error al actualizar.",
                        });
                    }
                });
        });
    </script>

</body>

</html>