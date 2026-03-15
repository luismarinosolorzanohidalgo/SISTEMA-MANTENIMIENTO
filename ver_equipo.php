<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

require_once "conexion.php";

if (!isset($_GET['id'])) {
    die("ID de equipo no proporcionado.");
}

$id = intval($_GET['id']);

// Obtener datos del equipo
$sql = "SELECT * FROM equipos WHERE id_equipo = $id LIMIT 1";
$result = $conn->query($sql);
if ($result->num_rows == 0) die("Equipo no encontrado.");
$equipo = $result->fetch_assoc();

// Obtener historial de rotaciones
$sql_rot = "SELECT * FROM rotaciones WHERE id_equipo = $id ORDER BY fecha_destino DESC";
$rotaciones = $conn->query($sql_rot);

// Obtener historial de mantenimientos
$sql_mant = "SELECT * FROM mantenimientos WHERE id_equipo = $id ORDER BY fecha_inicio DESC";
$mantenimientos = $conn->query($sql_mant);

// Última ubicación registrada
$ultima_ubicacion = $equipo['ubicacion'];
if ($rotaciones->num_rows > 0) {
    $row = $rotaciones->fetch_assoc();
    $ultima_ubicacion = $row['destino'];
}

// Sidebar info
$nombre = $_SESSION['nombre'] ?? 'Usuario';
$rol = $_SESSION['rol'] ?? 'Invitado';
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Detalle del Equipo</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#0f172a; --glass: rgba(255,255,255,0.06); --accent:#e6eef8; --muted:rgba(255,255,255,0.65);
  --primary:#2563eb; --success:#16a34a; --warn:#f59e0b; --danger:#ef4444;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--accent);display:flex;min-height:100vh;padding:15px;font-size:0.8rem;}
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

.content{flex:1;margin-left:15px}
.section-title{font-size:1rem;font-weight:600;margin-bottom:15px;color:var(--primary);letter-spacing:1px;}
.glass-card{background:var(--glass);padding:15px;border-radius:12px;border:1px solid rgba(255,255,255,0.1);margin-bottom:15px;transition:0.25s;}
.glass-card:hover{transform:scale(1.01);box-shadow:0 10px 25px rgba(0,0,0,0.4);}
.info-item{margin-bottom:8px;}
.label{font-size:0.7rem;opacity:0.75;}
.value{font-size:0.8rem;font-weight:600;margin-top:2px;}

table{width:100%;border-collapse:collapse;font-size:0.75rem;border-radius:8px;overflow:hidden;}
th,td{padding:6px;border-bottom:1px solid rgba(255,255,255,0.1);}
th{background:var(--primary);color:#fff;font-weight:600;border-bottom:none;}
tr:hover td{background:rgba(255,255,255,0.05);}

.badge{padding:2px 6px;border-radius:5px;font-weight:600;font-size:0.7rem;}
.badge-success{background:var(--success);color:#fff;}
.badge-warn{background:var(--warn);color:#fff;}
.badge-danger{background:var(--danger);color:#fff;}

.btn-volver{display:inline-block;margin-top:15px;padding:8px 18px;background:var(--primary);border-radius:8px;color:#fff;text-decoration:none;font-weight:500;transition:0.2s;}
.btn-volver:hover{opacity:0.85;}
</style>
</head>
<body>

<div class="sidebar">
  <div class="logo-box"><img src="img/logo.png" alt="logo"></div>
  <div class="user-box"><h3><?= htmlspecialchars($nombre) ?></h3><span><?= htmlspecialchars(ucfirst($rol)) ?></span></div>
  <div class="nav">
    <a href="panel.php">📊 Panel</a>
    <a href="registrar_equipo.php">🖨 Registrar Equipo</a>
    <a href="lista_equipos.php" class="active">📋 Lista de Equipos</a>
  </div>
  <a class="logout-btn" href="logout.php">Cerrar sesión</a>
</div>

<div class="content">
  <h2 class="section-title">Detalles del Equipo</h2>
  <div class="glass-card">
    <div class="info-item"><div class="label">Nombre del Equipo:</div><div class="value"><?= $equipo['nombre_equipo'] ?></div></div>
    <div class="info-item"><div class="label">Marca:</div><div class="value"><?= $equipo['marca'] ?></div></div>
    <div class="info-item"><div class="label">Modelo:</div><div class="value"><?= $equipo['modelo'] ?></div></div>
    <div class="info-item"><div class="label">Número de Serie:</div><div class="value"><?= $equipo['numero_serie'] ?></div></div>
    <div class="info-item"><div class="label">Estado:</div><div class="value"><?= $equipo['estado'] ?></div></div>
    <div class="info-item"><div class="label">Última Ubicación:</div><div class="value"><?= $ultima_ubicacion ?></div></div>
    <div class="info-item"><div class="label">Fecha de Registro:</div><div class="value"><?= $equipo['fecha_registro'] ?></div></div>
  </div>

  <h2 class="section-title">Historial de Rotaciones</h2>
  <div class="glass-card">
    <table>
      <tr><th>Origen</th><th>Fecha</th><th>Destino</th><th>Fecha</th></tr>
      <?php if($rotaciones->num_rows>0):
        $rotaciones->data_seek(0);
        while($rot=$rotaciones->fetch_assoc()): ?>
          <tr>
            <td><?= $rot['origen'] ?></td>
            <td><?= $rot['fecha_origen'] ?></td>
            <td><?= $rot['destino'] ?></td>
            <td><?= $rot['fecha_destino'] ?></td>
          </tr>
      <?php endwhile; else: ?>
        <tr><td colspan="4" style="text-align:center;">Sin rotaciones registradas.</td></tr>
      <?php endif; ?>
    </table>
  </div>

  <h2 class="section-title">Historial de Mantenimientos</h2>
  <div class="glass-card">
    <table>
      <thead>
        <tr>
          <th>Tipo</th>
          <th>Responsable</th>
          <th>Inicio</th>
          <th>Fin</th>
          <th>Estado</th>
          <th>Documento</th>
        </tr>
      </thead>
      <tbody>
        <?php if($mantenimientos->num_rows > 0):
          $mantenimientos->data_seek(0);
          while($m = $mantenimientos->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($m['tipo']) ?></td>
              <td><?= htmlspecialchars($m['responsable'] ?: '—') ?></td>
              <td><?= $m['fecha_inicio'] ?: '—' ?></td>
              <td><?= $m['fecha_fin'] ?: '—' ?></td>
              <td>
                <?php
                  if($m['estado'] === 'Completado'){
                    echo '<span class="badge badge-success">Completado</span>';
                  }elseif($m['estado'] === 'En Proceso'){
                    echo '<span class="badge badge-warn">En Proceso</span>';
                  }else{
                    echo '<span class="badge badge-danger">'.htmlspecialchars($m['estado']).'</span>';
                  }
                ?>
              </td>
              <td>
                <?php if(!empty($m['documento'])): ?>
                  <a href="<?= htmlspecialchars($m['documento']) ?>" target="_blank" style="color:#2563eb;">Ver</a>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
            </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="6" style="text-align:center;">Sin mantenimientos registrados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <a href="lista_equipos.php" class="btn-volver">← Volver</a>
</div>

</body>
</html>
