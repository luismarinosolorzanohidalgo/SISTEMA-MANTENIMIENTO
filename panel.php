<?php
session_start();

if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

$nombre = $_SESSION['nombre'];
$rol = $_SESSION['rol'];

$pagina_actual = basename($_SERVER['PHP_SELF']); // Detecta en qué archivo estás
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Poppins", sans-serif;
            background: #0f172a;
            color: #f8fafc;
            display: flex;
            min-height: 100vh;
            overflow: hidden;
        }

        /* SIDEBAR */
        .sidebar {
            width: 260px;
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(16px);
            padding: 25px 20px;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-box {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo-box img {
            width: 100px;
            filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.5));
        }

        .user-box {
            margin-bottom: 30px;
            background: rgba(255, 255, 255, 0.12);
            padding: 15px;
            border-radius: 12px;
            text-align: center;
        }

        .user-box h3 {
            margin: 5px 0 2px;
            font-size: 18px;
        }

        .user-box span {
            opacity: 0.8;
        }

        .nav a {
            display: block;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            text-decoration: none;
            color: #f8fafc;
            background: rgba(255, 255, 255, 0.05);
            transition: 0.3s;
            font-size: 15px;
        }

        .nav a:hover {
            background: rgba(255, 255, 255, 0.18);
            transform: translateX(5px);
        }

        .active {
            background: rgba(255, 255, 255, 0.25) !important;
            font-weight: 700;
            transform: translateX(8px);
        }

        .logout {
            margin-top: auto;
            padding: 12px;
            text-align: center;
            background: #dc2626;
            color: white;
            border-radius: 10px;
            text-decoration: none;
            transition: 0.3s;
        }

        .logout:hover {
            background: #b91c1c;
        }

        /* CONTENIDO */
        .content {
            flex: 1;
            padding: 50px;
            overflow-y: auto;
            color: #fff;
            animation: fadeIn 0.6s ease;
        }

        h1 {
            margin: 0 0 25px;
            font-size: 38px;
            font-weight: 700;
            letter-spacing: 1px;
            background: linear-gradient(90deg, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            color: transparent;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 20px;
        }

        .card {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 22px;
            padding: 35px;
            text-align: center;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(14px);
            transition: 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: "";
            position: absolute;
            top: -40%;
            left: -40%;
            width: 180%;
            height: 180%;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.18) 0%, rgba(0, 0, 0, 0) 70%);
            opacity: 0;
            transition: 0.5s;
        }

        .card:hover::before {
            opacity: 1;
            transform: scale(1.2);
        }

        .card:hover {
            transform: translateY(-12px) scale(1.03);
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.65);
        }

        .card h3 {
            margin-bottom: 6px;
            font-size: 22px;
            font-weight: 600;
        }

        .btn {
            display: inline-block;
            padding: 14px 20px;
            background: linear-gradient(135deg, #2563eb, #1e3a8a);
            border-radius: 12px;
            color: #fff;
            text-decoration: none;
            transition: 0.25s ease;
            font-weight: 700;
            letter-spacing: 0.5px;
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.45);
        }

        .btn:hover {
            background: linear-gradient(135deg, #1e40af, #0f172a);
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(30, 64, 175, 0.6);
        }

        footer {
            margin-top: 50px;
            text-align: center;
            opacity: 0.5;
            font-size: 14px;
            padding-bottom: 20px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="logo-box">
            <img src="img/logo.png" alt="Logo">
        </div>

        <div class="user-box">
            <h3><?php echo $nombre; ?></h3>
            <span><?php echo ucfirst($rol); ?></span>
        </div>

        <div class="nav">
            <a href="panel.php" class="<?= $pagina_actual == 'panel.php' ? 'active' : '' ?>">Panel</a>
            <a href="lista_equipos.php" class="<?= $pagina_actual == 'lista_equipos.php' ? 'active' : '' ?>">Lista de Equipos</a>
            <a href="mantenimientos.php" class="<?= $pagina_actual == 'lista_equipos.php' ? 'active' : '' ?>">Mantenimientos</a>
            <!-- NUEVO: ROTACIONES -->
            <a href="rotaciones.php" class="<?= $pagina_actual == 'rotaciones.php' ? 'active' : '' ?>">Rotaciones</a>

            <?php if ($rol === "Administrador"): ?>
                <a href="usuarios.php" class="<?= $pagina_actual == 'usuarios.php' ? 'active' : '' ?>">Gestión de Usuarios</a>
                <a href="reportes.php" class="<?= $pagina_actual == 'reportes.php' ? 'active' : '' ?>">Reportes del Sistema</a>
                <a href="actividades.php" class="<?= $pagina_actual == 'actividades.php' ? 'active' : '' ?>">Actividades de Mantenimiento</a>
            <?php endif; ?>
        </div>

        <a href="logout.php" class="logout">Cerrar Sesión</a>
    </div>

    <div class="content">
        <h1>Panel de Control</h1>

        <div class="grid">

            <div class="card">
                <h3>Lista de Equipos</h3>
                <a class="btn" href="lista_equipos.php">Ver</a>
            </div>

            <!-- TARJETA DE ROTACIONES -->
            <div class="card">
                <h3>Rotaciones de Equipos</h3>
                <a class="btn" href="rotaciones.php">Gestionar</a>
            </div>
            <!-- TARJETA DE MANTENIMIENTOS -->
            <div class="card">
                <h3>Mantenimientos</h3>
                <a class="btn" href="rotaciones.php">Gestionar</a>
            </div>

            <?php if ($rol === "Administrador"): ?>
                <div class="card">
                    <h3>Gestión de Usuarios</h3>
                    <a class="btn" href="usuarios.php">Administrar</a>
                </div>

                <div class="card">
                    <h3>Reportes del Sistema</h3>
                    <a class="btn" href="reportes.php">Ver</a>
                </div>
                <div class="card">
                    <h3>Actividades de Mantenimiento</h3>
                    <a class="btn" href="actividades.php">Ver</a>
                </div>
            <?php endif; ?>
        </div>

        <footer>
            © <?php echo date("Y"); ?> Sistema de Registro de Equipos
        </footer>
    </div>

</body>

</html>