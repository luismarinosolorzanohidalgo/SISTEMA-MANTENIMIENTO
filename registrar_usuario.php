<?php
session_start();

if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

require_once "conexion.php";

$nombre_sesion = $_SESSION['nombre'];
$rol_sesion = $_SESSION['rol'];
$pagina_actual = basename($_SERVER['PHP_SELF']); 

// =================== OBTENER ROLES DEL ENUM ===================
$roles = [];
$q = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'rol'");
$r = $q->fetch_assoc();

if ($r) {
    preg_match("/^enum\((.*)\)$/", $r['Type'], $m);
    foreach (explode(",", $m[1]) as $v) {
        $roles[] = trim(str_replace("'", "", $v));
    }
}

// =================== REGISTRO DE USUARIO ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre   = $_POST['nombre'];
    $usuario  = $_POST['usuario'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol      = $_POST['rol'];

    $insert = $conn->prepare("
        INSERT INTO usuarios (nombre, usuario, password, rol)
        VALUES (?, ?, ?, ?)
    ");

    $insert->bind_param("ssss", $nombre, $usuario, $password, $rol);

    if ($insert->execute()) {
        header("Location: registrar_usuario.php?msg=ok");
        exit();
    } else {
        header("Location: registrar_usuario.php?msg=error");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Usuario</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* ==================== GLOBAL ==================== */
        body {
            margin: 0;
            padding: 0;
            font-family: "Poppins", sans-serif;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #f8fafc;
            display: flex;
            min-height: 100vh;
            overflow: hidden;
        }

        /* ==================== SIDEBAR ==================== */
        .sidebar {
            width: 260px;
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(14px);
            border-right: 1px solid rgba(255,255,255,0.12);
            padding: 25px 20px;
            display: flex;
            flex-direction: column;
        }

        .logo-box {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo-box img {
            width: 95px;
            filter: drop-shadow(0 0 8px rgba(255,255,255,0.4));
        }

        .user-box {
            background: rgba(255,255,255,0.12);
            padding: 15px;
            border-radius: 14px;
            margin-bottom: 25px;
            text-align: center;
        }

        .nav a {
            display: block;
            padding: 12px 15px;
            border-radius: 10px;
            color: #f8fafc;
            text-decoration: none;
            background: rgba(255,255,255,0.05);
            margin-bottom: 10px;
            transition: 0.3s ease;
        }

        .nav a:hover {
            background: rgba(255,255,255,0.18);
            transform: translateX(6px);
        }

        .active {
            background: rgba(255,255,255,0.25) !important;
            font-weight: 700;
            transform: translateX(8px);
        }
/* ===== SELECT MEJORADO ===== */
select {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 18px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.3);
    background: rgba(255, 255, 255, 0.1);
    color: #f1f5f9; /* texto más visible */
    font-size: 15px;
    appearance: none; /* quitar estilo nativo */
    -webkit-appearance: none;
    -moz-appearance: none;
    cursor: pointer;
    transition: all 0.3s ease;
    background-image: url("data:image/svg+xml;charset=US-ASCII,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16'><polygon points='0,0 16,0 8,8' fill='white'/></svg>");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 12px;
}

select:hover, select:focus {
    background: rgba(255, 255, 255, 0.15);
    border-color: #38bdf8;
    color: #f8fafc;
    box-shadow: 0 0 8px rgba(56, 189, 248, 0.5);
    outline: none;
}

option {
    background: #1e293b; /* color del fondo del dropdown */
    color: #f8fafc; /* color del texto */
}

        .logout {
            margin-top: auto;
            background: #dc2626;
            color: white;
            padding: 12px;
            text-align: center;
            border-radius: 10px;
            text-decoration: none;
        }

        /* ==================== CONTENIDO ==================== */
        .content {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }

        .content h1 {
            font-size: 28px;
            margin-bottom: 25px;
        }

        .card {
            width: 480px;
            margin: auto;
            background: rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 28px;
            backdrop-filter: blur(12px);
        }

        /* Inputs */
        input, select {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: none;
            outline: none;
            margin-bottom: 15px;
            background: rgba(255,255,255,0.17);
            color: white;
        }

        /* Buttons */
        .btn {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            border: none;
        }

        .btn-save {
            background: #10b981;
            color: white;
        }

        .btn-save:hover {
            background: #059669;
        }

        .btn-back {
            background: #475569;
            color: white;
            margin-top: 10px;
        }

        .btn-back:hover {
            background: #334155;
        }
    </style>
</head>
<body>

<!-- ========== SIDEBAR ========== -->
<div class="sidebar">

    <div class="logo-box">
        <img src="img/logo.png" alt="Logo">
    </div>

    <div class="user-box">
        <h3><?= $nombre_sesion ?></h3>
        <span><?= ucfirst($rol_sesion) ?></span>
    </div>

    <div class="nav">
        <a href="panel.php" class="<?= $pagina_actual == 'panel.php' ? 'active' : '' ?>">Panel</a>
        <a href="registrar_equipo.php" class="<?= $pagina_actual == 'registrar_equipo.php' ? 'active' : '' ?>">Registrar Equipo</a>
        <a href="lista_equipos.php" class="<?= $pagina_actual == 'lista_equipos.php' ? 'active' : '' ?>">Lista de Equipos</a>

        <?php if ($rol_sesion === "Administrador"): ?>
        <a href="usuarios.php" class="<?= $pagina_actual == 'usuarios.php' ? 'active' : '' ?>">Gestión de Usuarios</a>
        <a href="reportes.php" class="<?= $pagina_actual == 'reportes.php' ? 'active' : '' ?>">Reportes del Sistema</a>
        <?php endif; ?>
    </div>

    <a href="logout.php" class="logout">Cerrar Sesión</a>

</div>

<!-- ========== CONTENIDO ========== -->
<div class="content">

    <h1>Registrar Nuevo Usuario</h1>

    <div class="card">

        <form action="" method="POST">

            <label>Nombre Completo</label>
            <input type="text" name="nombre" required>

            <label>Usuario</label>
            <input type="text" name="usuario" required>

            <label>Contraseña</label>
            <input type="password" name="password" required>

            <label>Rol</label>
            <select name="rol" required>
                <option value="" disabled selected>Seleccione un rol</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= $r ?>"><?= ucfirst($r) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-save">Registrar Usuario</button>
        </form>

        <a href="usuarios.php">
            <button class="btn btn-back">Volver</button>
        </a>

    </div>
</div>

<!-- ========== SWEET ALERTS ========== -->
<?php if (isset($_GET['msg']) && $_GET['msg'] === 'ok'): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Usuario registrado',
    text: 'El usuario fue creado correctamente.',
    confirmButtonColor: '#10b981',
    showClass: { popup: 'animate__animated animate__zoomIn' }
});
</script>
<?php endif; ?>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'error'): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: 'No se pudo registrar el usuario.',
    confirmButtonColor: '#dc2626'
});
</script>
<?php endif; ?>

</body>
</html>
