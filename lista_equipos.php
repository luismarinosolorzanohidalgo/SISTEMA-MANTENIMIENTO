<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}
require_once "conexion.php";

$intervalos = [
    "laptop"=>4,"pc"=>6,"cpu"=>6,"computadora"=>6,
    "router"=>8,"switch"=>8,"monitor"=>10,"impresora"=>6,
    "tablet"=>6,"proyector"=>6,"servidor"=>3,"otro"=>7
];

function proximaFecha($fecha,$meses){ return date("Y-m-d",strtotime("+$meses months",strtotime($fecha))); }

function detectarCategoriaPorNombre($nombre){
    $n = mb_strtolower($nombre);
    $map = [
        'laptop'=>['laptop','macbook','mac book','vivobook','acer','asus','dell','lenovo','hp pavilion','macbook air'],
        'pc'=>['pc','escritorio','desktop','optiplex','thinkcentre','elite','workstation','cpu'],
        'router'=>['router','tp-link','huawei','archer','ax3','ax'],
        'switch'=>['switch','sg350','cisco switch','switch de red'],
        'monitor'=>['monitor','display','screen','ultrawide','24mh','curve'],
        'impresora'=>['impresora','printer','epson','brother','l3110','hl-'],
        'tablet'=>['tablet','galaxy tab','ipad','tab a'],
        'proyector'=>['proyector','benq','projector','ms550'],
        'servidor'=>['servidor','server']
    ];
    foreach($map as $cat=>$keywords){
        foreach($keywords as $kw){
            if(strpos($n,$kw)!==false) return ucfirst($cat);
        }
    }
    return 'Otro';
}

// Flash
if(!isset($_SESSION['flash'])) $_SESSION['flash']=[];
function set_flash($type,$text){$_SESSION['flash'][]=['type'=>$type,'text'=>$text];}
function show_flash(){if(!empty($_SESSION['flash'])){foreach($_SESSION['flash'] as $f){$cls=$f['type']=='success'?'flash-success':'flash-error';echo "<div class=\"flash {$cls}\">".htmlspecialchars($f['text'])."</div>";}unset($_SESSION['flash']);}}

// Autofill
if(isset($_GET['action']) && $_GET['action']==='autofill'){
    $q=$conn->query("SELECT id_equipo,nombre_equipo,categoria FROM equipos"); $updated=0;
    if($q){while($r=$q->fetch_assoc()){$det=detectarCategoriaPorNombre($r['nombre_equipo']);if(trim($r['categoria'])=='' || strtolower(trim($r['categoria']))=='otro'){$stmt=$conn->prepare("UPDATE equipos SET categoria=? WHERE id_equipo=?");$stmt->bind_param("si",$det,$r['id_equipo']);if($stmt->execute()) $updated++;$stmt->close();}}}
    set_flash('success',"Autorrelleno completado. Categorías actualizadas: {$updated}");
    header("Location: lista_equipos.php"); exit();
}

// ====================== PAGINACIÓN ======================
$filas_por_pagina = isset($_GET['rows']) ? (int)$_GET['rows'] : 10;
$pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$filas_por_pagina = in_array($filas_por_pagina,[10,20,30]) ? $filas_por_pagina : 10;
$offset = ($pagina-1) * $filas_por_pagina;

// Búsqueda
$search = $_GET['search'] ?? '';
$search_sql = $conn->real_escape_string($search);
$where = $search_sql ? "WHERE nombre_equipo LIKE '%$search_sql%' OR categoria LIKE '%$search_sql%' OR marca LIKE '%$search_sql%'" : "";

// Total de registros
$total_res = $conn->query("SELECT COUNT(*) AS total FROM equipos $where");
$total_row = $total_res->fetch_assoc()['total'];
$total_paginas = ceil($total_row / $filas_por_pagina);

// Consulta con LIMIT
$sql="SELECT id_equipo,nombre_equipo,categoria,marca,modelo,numero_serie,estado,ubicacion,fecha_registro 
      FROM equipos 
      $where
      ORDER BY id_equipo DESC 
      LIMIT $offset, $filas_por_pagina";
$res=$conn->query($sql);

$nombre=$_SESSION['nombre']??'Usuario';
$rol=$_SESSION['rol']??'Invitado';
$pagina_actual=basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Lista de Equipos</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#0f172a; --glass:rgba(255,255,255,0.06); --accent:#e6eef8; --muted:rgba(255,255,255,0.65);
  --primary:#2563eb; --success:#16a34a; --warn:#f59e0b; --danger:#ef4444;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--accent);display:flex;min-height:100vh;padding:15px;font-size:0.8rem;}
.sidebar{width:250px;background:var(--glass);backdrop-filter:blur(14px);border-radius:15px;padding:20px;display:flex;flex-direction:column;gap:18px;position:sticky;top:10px;height:95vh;box-shadow:0 6px 25px rgba(0,0,0,0.4);}
.logo-box img{width:100px}
.user-box{background:rgba(255,255,255,0.08);padding:12px;border-radius:12px;text-align:center;transition:0.3s;}
.user-box:hover{background:rgba(255,255,255,0.12);}
.user-box h3{margin-bottom:3px;font-weight:600}
.user-box span{color:var(--muted);font-size:0.75rem}
.nav{display:flex;flex-direction:column;gap:6px}
.nav a{padding:10px 12px;border-radius:10px;text-decoration:none;color:var(--accent);font-weight:500;transition:0.3s;}
.nav a:hover{background:rgba(255,255,255,0.08);transform:translateX(4px)}
.nav .active{background:rgba(255,255,255,0.15)}
.logout-btn{margin-top:auto;padding:10px;background:var(--danger);border-radius:10px;text-align:center;text-decoration:none;color:white;font-weight:500;transition:0.3s;}
.logout-btn:hover{background:#c40000;}
.content{flex:1;margin-left:15px}
.header{display:flex;justify-content:space-between;margin-bottom:15px;flex-wrap:wrap;font-size:0.85rem;}
.btn{padding:6px 12px;border-radius:8px;color:white;text-decoration:none;font-weight:600;transition:0.3s;cursor:pointer;font-size:0.8rem;margin:2px;}
.btn-ghost{border:1px solid rgba(255,255,255,0.2);background:transparent}
.btn-ghost:hover{background:rgba(255,255,255,0.1)}
.btn-primary{background:linear-gradient(90deg,#2563eb,#3b82f6);box-shadow:0 3px 8px rgba(0,0,0,0.3);}
.btn-primary:hover{background:linear-gradient(90deg,#1e4db7,#1e70e0);transform:translateY(-1px);}
.flash{padding:8px;border-radius:8px;margin-bottom:10px;font-weight:500;font-size:0.75rem;}
.flash-success{background:rgba(16,185,129,0.15);border-left:3px solid #10b981}
.flash-error{background:rgba(239,68,68,0.15);border-left:3px solid #ef4444}
.table-wrap{background:rgba(255,255,255,0.03);padding:10px;border-radius:12px;overflow-x:auto;}
table{width:100%;border-collapse:collapse;font-size:0.75rem;}
thead th{padding:8px;background:rgba(255,255,255,0.08);text-align:left;font-weight:500}
tbody td{padding:6px;border-bottom:1px solid rgba(255,255,255,0.05);}
tbody tr:hover{background:rgba(255,255,255,0.05)}
.badge{padding:4px 8px;border-radius:6px;font-weight:500;font-size:0.7rem}
.badge-ok{background:rgba(34,197,94,0.15);color:#22c55e}
.badge-prox{background:rgba(250,204,21,0.15);color:#f59e0b}
.badge-venc{background:rgba(239,68,68,0.15);color:#ef4444}
.link-action{padding:4px 8px;border-radius:6px;color:white;text-decoration:none;font-size:0.7rem;margin-right:2px;display:inline-block;transition:0.3s;}
.ver{background:#3b82f6}
.ver:hover{background:#1e40af}
.editar{background:#10b981}
.editar:hover{background:#065f46}
.eliminar{background:#ef4444}
.eliminar:hover{background:#b91c1c}
.actions{display:flex;gap:2px;flex-wrap:wrap;}
.pagination{margin-top:10px; display:flex; justify-content:space-between; align-items:center;}
.pagination a{padding:5px 8px; margin:2px; border-radius:5px; text-decoration:none; color:white;}
.pagination a.active{background:#2563eb;}
.pagination a.inactive{background:#1e293b;}
#searchInput{padding:5px 10px;border-radius:6px;border:1px solid rgba(255,255,255,0.2);background:rgba(255,255,255,0.05);color:white;margin-bottom:10px;width:200px;}
</style>
</head>
<body>
<div class="sidebar">
  <div class="logo-box"><img src="img/logo.png" alt="logo"></div>
  <div class="user-box"><h3><?= htmlspecialchars($nombre) ?></h3><span><?= htmlspecialchars(ucfirst($rol)) ?></span></div>
  <div class="nav">
    <a href="panel.php" class="<?= $pagina_actual=='panel.php'?'active':'' ?>">📊 Panel</a>
    <a href="registrar_equipo.php" class="<?= $pagina_actual=='registrar_equipo.php'?'active':'' ?>">🖨 Registrar Equipo</a>
    <a href="lista_equipos.php" class="active">📋 Lista de Equipos</a>
  </div>
  <a class="logout-btn" href="logout.php">Cerrar sesión</a>
</div>

<div class="content">
  <div class="header">
    <h1>Lista de Equipos — Plan de Mantenimiento</h1>
    <div>
      <a href="lista_equipos.php?action=autofill" class="btn btn-ghost">Autorrellenar</a>
      <button class="btn btn-primary">Generar Plan</button>
    </div>
  </div>

  <?php show_flash(); ?>

  <input type="text" id="searchInput" placeholder="Buscar equipos..." value="<?= htmlspecialchars($search) ?>">

  <div class="table-wrap">
    <table id="equiposTable">
      <thead>
        <tr>
          <th>ID</th><th>Equipo</th><th>Cat</th><th>Marca</th><th>Modelo</th>
          <th>Serie</th><th>Ubicación</th><th>Int</th><th>Próx. Mant.</th>
          <th>Estado</th><th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php if($res && $res->num_rows): while($r=$res->fetch_assoc()):
        $categoria=trim($r['categoria'])?:'Otro';
        $key=strtolower($categoria);
        $meses=$intervalos[$key]??$intervalos['otro'];
        $proxima=proximaFecha($r['fecha_registro'],$meses);
        $hoy=date('Y-m-d');
        if($proxima<$hoy){$estado="VENCIDO";$badge="badge-venc";}
        elseif(strtotime($proxima)-strtotime($hoy)<=30*86400){$estado="PRÓXIMO";$badge="badge-prox";}
        else{$estado="OK";$badge="badge-ok";}
      ?>
      <tr>
        <td><?= $r['id_equipo'] ?></td>
        <td><?= htmlspecialchars($r['nombre_equipo']) ?></td>
        <td><?= htmlspecialchars($categoria) ?></td>
        <td><?= htmlspecialchars($r['marca']) ?></td>
        <td><?= htmlspecialchars($r['modelo']) ?></td>
        <td><?= htmlspecialchars($r['numero_serie']) ?></td>
        <td><?= htmlspecialchars($r['ubicacion']) ?></td>
        <td><?= $meses ?>m</td>
        <td><?= $proxima ?></td>
        <td><span class="badge <?= $badge ?>"><?= $estado ?></span></td>
        <td class="actions">
          <a class="link-action ver" href="ver_equipo.php?id=<?= $r['id_equipo'] ?>">Ver</a>
          <a class="link-action editar" href="editar_equipo.php?id=<?= $r['id_equipo'] ?>">Editar</a>
          <a class="link-action eliminar" onclick="return confirm('¿Eliminar?')" href="eliminar_equipo.php?id=<?= $r['id_equipo'] ?>">Eliminar</a>
        </td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="11">No hay equipos registrados.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="pagination">
    <div>
      <form method="get" style="display:flex; align-items:center; gap:5px;">
        <label for="rows">Filas por página:</label>
        <select name="rows" id="rows" onchange="this.form.submit()">
          <option value="10" <?= $filas_por_pagina==10?'selected':'' ?>>10</option>
          <option value="20" <?= $filas_por_pagina==20?'selected':'' ?>>20</option>
          <option value="30" <?= $filas_por_pagina==30?'selected':'' ?>>30</option>
        </select>
        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
      </form>
    </div>

    <div>
      <?php for($i=1;$i<=$total_paginas;$i++): ?>
        <a href="?page=<?= $i ?>&rows=<?= $filas_por_pagina ?>&search=<?= urlencode($search) ?>" class="<?= $i==$pagina?'active':'inactive' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  </div>
</div>

<script>
const searchInput = document.getElementById('searchInput');
searchInput.addEventListener('input', function(){
    const val = this.value.toLowerCase();
    const rows = document.querySelectorAll('#equiposTable tbody tr');
    rows.forEach(r=>{
        r.style.display = Array.from(r.children).some(td=>td.textContent.toLowerCase().includes(val)) ? '' : 'none';
    });
});
</script>
</body>
</html>
