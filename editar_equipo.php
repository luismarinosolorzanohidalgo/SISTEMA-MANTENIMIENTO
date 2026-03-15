<?php
session_start();

// Seguridad
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

require_once "conexion.php";

// ===============================
// VALIDAR ID
// ===============================
if (!isset($_GET['id'])) {
    header("Location: lista_equipos.php?msg=error_id");
    exit();
}

$id = intval($_GET['id']);

// ===============================
// OBTENER DATOS DEL EQUIPO
// ===============================
$stmt = $conn->prepare("SELECT * FROM equipos WHERE id_equipo = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: lista_equipos.php?msg=no_existe");
    exit();
}

$equipo = $result->fetch_assoc();


// ===============================
// ACTUALIZAR AL GUARDAR
// ===============================
$actualizado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre_equipo = $_POST['nombre_equipo'];
    $categoria = $_POST['categoria'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $numero_serie = $_POST['numero_serie'];
    $estado = $_POST['estado'];
    $ubicacion = $_POST['ubicacion'];

    $update = $conn->prepare("
        UPDATE equipos SET 
            nombre_equipo = ?,
            categoria = ?,
            marca = ?,
            modelo = ?,
            numero_serie = ?,
            estado = ?,
            ubicacion = ?
        WHERE id_equipo = ?
    ");

    $update->bind_param(
        "sssssssi",
        $nombre_equipo,
        $categoria,
        $marca,
        $modelo,
        $numero_serie,
        $estado,
        $ubicacion,
        $id
    );

    if ($update->execute()) {
        $actualizado = "ok";
    } else {
        $actualizado = "error";
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Equipo</title>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 0;
            background: #0f172a;
            font-family: "Poppins", sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #fff;
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

        .container {
            width: 450px;
            background: rgba(255, 255, 255, 0.07);
            padding: 35px 40px;
            border-radius: 20px;
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.45);
            animation: fadeIn 0.7s ease;
        }

        h1 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 28px;
            background: linear-gradient(90deg, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            color: transparent;
        }

        label {
            font-size: 15px;
            margin-top: 12px;
            display: block;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            border-radius: 10px;
            border: none;
            outline: none;
            font-size: 15px;
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        input:focus,
        select:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 8px #60a5fa;
        }

        .btns {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
        }

        .btn {
            padding: 12px 20px;
            background: linear-gradient(135deg, #2563eb, #1e3a8a);
            border-radius: 12px;
            color: #fff;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: 0.25s ease;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4);
        }

        .cancel {
            background: #dc2626;
        }

        .cancel:hover {
            background: #b91c1c;
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.4);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Editar Equipo</h1>

        <form method="POST">

            <label>Nombre del Equipo:</label>
            <input type="text" name="nombre_equipo" value="<?= $equipo['nombre_equipo'] ?>" required>

            <label>Categoría:</label>
            <input type="text" name="categoria" value="<?= $equipo['categoria'] ?>" required>

            <label>Marca:</label>
            <input type="text" name="marca" value="<?= $equipo['marca'] ?>" required>

            <label>Modelo:</label>
            <input type="text" name="modelo" value="<?= $equipo['modelo'] ?>" required>

            <label>Número de Serie:</label>
            <input type="text" name="numero_serie" value="<?= $equipo['numero_serie'] ?>" required>

            <label>Estado:</label>
            <select name="estado" required>
                <option value="Operativo" <?= $equipo['estado'] == "Operativo" ? "selected" : "" ?>>Operativo</option>
                <option value="En mantenimiento" <?= $equipo['estado'] == "En mantenimiento" ? "selected" : "" ?>>En mantenimiento</option>
                <option value="Dañado" <?= $equipo['estado'] == "Dañado" ? "selected" : "" ?>>Dañado</option>
            </select>

            <label>Ubicación:</label>
            <input type="text" name="ubicacion" value="<?= $equipo['ubicacion'] ?>" required>

            <div class="btns">
                <button type="submit" class="btn">Actualizar</button>
                <a href="lista_equipos.php" class="btn cancel">Cancelar</a>
            </div>

        </form>
    </div>

    <?php if ($actualizado === "ok"): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Equipo actualizado',
                text: 'Los datos se guardaron correctamente.',
                timer: 1800,
                showConfirmButton: false
            }).then(() => {
                window.location.href = "lista_equipos.php";
            });
        </script>
    <?php elseif ($actualizado === "error"): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error al actualizar',
                text: 'Ocurrió un problema. Inténtalo de nuevo.',
            });
        </script>
    <?php endif; ?>

</body>

</html>