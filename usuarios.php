<?php
session_start();

if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

require_once "conexion.php";

$nombre = $_SESSION['nombre'];
$rol = $_SESSION['rol'];
$pagina_actual = basename($_SERVER['PHP_SELF']); 

$result = $conn->query("SELECT * FROM usuarios ORDER BY id_usuario DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>

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

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: rgba(255,255,255,0.07);
            backdrop-filter: blur(16px);
            padding: 25px 20px;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255,255,255,0.1);
        }

        .logo-box {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo-box img {
            width: 100px;
            filter: drop-shadow(0 0 8px rgba(255,255,255,0.5));
        }

        .user-box {
            margin-bottom: 30px;
            background: rgba(255,255,255,0.12);
            padding: 15px;
            border-radius: 12px;
            text-align: center;
        }

        .nav a {
            display: block;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            text-decoration: none;
            color: #f8fafc;
            background: rgba(255,255,255,0.05);
            transition: 0.3s;
        }

        .nav a:hover {
            background: rgba(255,255,255,0.18);
            transform: translateX(5px);
        }

        .active {
            background: rgba(255,255,255,0.25) !important;
            font-weight: 700;
            transform: translateX(8px);
        }

        .logout {
            margin-top: auto;
            padding: 12px;
            background: #dc2626;
            color: white;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
        }

        .content {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }

        .table-container {
            background: rgba(255,255,255,0.08);
            padding: 25px;
            border-radius: 18px;
            backdrop-filter: blur(10px);
            margin-top: 20px;
        }

        /* BOTÓN NUEVO PARA AGREGAR USUARIO */
        .add-btn {
            display: inline-block;
            background: #10b981;
            color: #fff;
            padding: 12px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.25s;
            margin-bottom: 15px;
        }

        .add-btn:hover {
            background: #059669;
            transform: translateY(-3px);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            background: rgba(255,255,255,0.15);
        }

        .btn-sm {
            padding: 6px 10px;
            border-radius: 8px;
            text-decoration: none;
            color: white;
            font-size: 13px;
        }

        .edit { background: #2563eb; }
        .delete { background: #dc2626; }
        .reset { background: #16a34a; }

    </style>
</head>
<body>

<div class="sidebar">
    <div class="logo-box">
        <img src="img/logo.png" alt="Logo">
    </div>

    <div class="user-box">
        <h3><?= $nombre ?></h3>
        <span><?= ucfirst($rol) ?></span>
    </div>

    <div class="nav">
        <a href="panel.php" class="<?= $pagina_actual == 'panel.php' ? 'active' : '' ?>">Panel</a>
        <a href="registrar_equipo.php" class="<?= $pagina_actual == 'registrar_equipo.php' ? 'active' : '' ?>">Registrar Equipo</a>
        <a href="lista_equipos.php" class="<?= $pagina_actual == 'lista_equipos.php' ? 'active' : '' ?>">Lista de Equipos</a>

        <?php if ($rol === "Administrador"): ?>
        <a href="usuarios.php" class="<?= $pagina_actual == 'usuarios.php' ? 'active' : '' ?>">Gestión de Usuarios</a>
        <a href="reportes.php" class="<?= $pagina_actual == 'reportes.php' ? 'active' : '' ?>">Reportes del Sistema</a>
        <?php endif; ?>
    </div>

    <a href="logout.php" class="logout">Cerrar Sesión</a>
</div>

<div class="content">
    <h1>Gestión de Usuarios</h1>

    <!-- 🔥 BOTÓN NUEVO -->
    <a href="registrar_usuario.php" class="add-btn">➕ Agregar Usuario</a>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>

                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id_usuario'] ?></td>
                    <td><?= $row['nombre'] ?></td>
                    <td><?= $row['usuario'] ?></td>
                    <td><?= $row['rol'] ?></td>

                    <td>
                        <a href="editar_usuario.php?id=<?= $row['id_usuario'] ?>" class="btn-sm edit">Editar</a>

                        <a href="reset_password.php?id=<?= $row['id_usuario'] ?>" class="btn-sm reset">Restablecer</a>

                        <a href="eliminar_usuario.php?id=<?= $row['id_usuario'] ?>" class="btn-sm delete" onclick="return confirm('¿Eliminar usuario?')">Eliminar</a>
                    </td>
                </tr>
                <?php endwhile; ?>

            </tbody>
        </table>
    </div>

</div>

</body>
</html>
