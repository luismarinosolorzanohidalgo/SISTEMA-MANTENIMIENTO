<?php
session_start();
if(!isset($_SESSION['rol'])){
    header("Location: login.php");
    exit();
}

require_once "conexion.php";

// ----------------- KPIs -----------------

// Total de equipos
$total_equipos = $conn->query("SELECT COUNT(*) AS total FROM equipos")->fetch_assoc()['total'];
$equipos_operativos = $conn->query("SELECT COUNT(*) AS total FROM equipos WHERE estado='Operativo'")->fetch_assoc()['total'];

// Mantenimientos
$mant_en_proceso = $conn->query("SELECT COUNT(*) AS total FROM mantenimientos WHERE estado='En Proceso'")->fetch_assoc()['total'];
$mant_completados = $conn->query("SELECT COUNT(*) AS total FROM mantenimientos WHERE estado='Completado'")->fetch_assoc()['total'];

// Rotaciones recientes (últimos 5)
$rotaciones = $conn->query("SELECT r.*, e.nombre_equipo FROM rotaciones r 
                            INNER JOIN equipos e ON r.id_equipo=e.id_equipo
                            ORDER BY r.fecha_destino DESC LIMIT 5");

// Usuarios
$total_usuarios = $conn->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard de Reportes</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#0f172a; --glass: rgba(255,255,255,0.06); --accent:#e6eef8; --muted:rgba(255,255,255,0.65);
  --primary:#2563eb; --success:#16a34a; --warn:#f59e0b; --danger:#ef4444;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--accent);display:flex;min-height:100vh;padding:15px;font-size:0.85rem;}
.sidebar{width:250px;background:rgba(255,255,255,0.04);backdrop-filter:blur(14px);border-radius:15px;padding:20px;display:flex;flex-direction:column;gap:18px;position:sticky;top:10px;height:95vh;box-shadow:0 6px 25px rgba(0,0,0,0.4);}
.logo-box img{width:100px}
.user-box{background:rgba(255,255,255,0.06);padding:12px;border-radius:12px;text-align:center;}
.user-box h3{margin-bottom:3px;font-weight:600}
.user-box span{color:var(--muted);font-size:0.75rem}
.nav{display:flex;flex-direction:column;gap:6px}
.nav a{padding:10px 12px;border-radius:10px;text-decoration:none;color:var(--accent);font-weight:500;transition:0.3s;}
.nav a:hover{background:rgba(255,255,255,0.08);transform:translateX(4px)}
.nav .active{background:rgba(255,255,255,0.15)}
.logout-btn{margin-top:auto;padding:10px;background:var(--danger);border-radius:10px;text-align:center;text-decoration:none;color:white;font-weight:500;transition:0.3s;}
.logout-btn:hover{background:#c40000;}
.content{flex:1;margin-left:15px;padding-bottom:30px}
.kpi-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:15px;margin-bottom:20px}
.card{background:var(--glass);padding:20px;border-radius:12px;border:1px solid rgba(255,255,255,0.1);text-align:center;transition:0.25s;}
.card:hover{transform:scale(1.02);box-shadow:0 10px 25px rgba(0,0,0,0.4);}
.card h3{font-size:1.25rem;margin-bottom:5px;color:white;}
.card span{font-size:0.75rem;color:var(--muted);}
.table-section{margin-top:20px;}
table{width:100%;border-collapse:collapse;font-size:0.75rem;border-radius:8px;overflow:hidden;margin-top:10px;}
th,td{padding:6px;border-bottom:1px solid rgba(255,255,255,0.1);}
th{background:var(--primary);color:#fff;font-weight:600;border-bottom:none;}
tr:hover td{background:rgba(255,255,255,0.05);}
.section-title{font-size:1rem;font-weight:600;margin-bottom:15px;color:var(--primary);letter-spacing:1px;}
</style>
</head>
<body>

<div class="sidebar">
  <div class="logo-box"><img src="img/logo.png" alt="logo"></div>
  <div class="user-box"><h3><?= htmlspecialchars($_SESSION['nombre']) ?></h3><span><?= htmlspecialchars(ucfirst($_SESSION['rol'])) ?></span></div>
  <div class="nav">
    <a href="panel.php">📊 Panel</a>
    <a href="lista_equipos.php">📋 Lista de Equipos</a>
    <a href="reportes.php" class="active">📝 Reportes</a>
  </div>
  <a class="logout-btn" href="logout.php">Cerrar sesión</a>
</div>

<div class="content">

  <h2 class="section-title">Resumen General</h2>
  <div class="kpi-cards">
    <div class="card">
      <h3><?= $total_equipos ?></h3>
      <span>Total Equipos</span>
    </div>
    <div class="card">
      <h3><?= $equipos_operativos ?></h3>
      <span>Equipos Operativos</span>
    </div>
    <div class="card">
      <h3><?= $mant_en_proceso ?></h3>
      <span>Mantenimientos en Proceso</span>
    </div>
    <div class="card">
      <h3><?= $mant_completados ?></h3>
      <span>Mantenimientos Completados</span>
    </div>
    <div class="card">
      <h3><?= $rotaciones->num_rows ?></h3>
      <span>Rotaciones Recientes</span>
    </div>
    <div class="card">
      <h3><?= $total_usuarios ?></h3>
      <span>Usuarios Registrados</span>
    </div>
  </div>

  <h2 class="section-title">Últimas Rotaciones</h2>
  <div class="table-section">
    <table>
      <tr><th>Equipo</th><th>Origen</th><th>Destino</th><th>Fecha Origen</th><th>Fecha Destino</th></tr>
      <?php while($r=$rotaciones->fetch_assoc()): ?>
      <tr>
        <td><?= $r['nombre_equipo'] ?></td>
        <td><?= $r['origen'] ?></td>
        <td><?= $r['destino'] ?></td>
        <td><?= $r['fecha_origen'] ?></td>
        <td><?= $r['fecha_destino'] ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>

</div>

</body>
</html>
