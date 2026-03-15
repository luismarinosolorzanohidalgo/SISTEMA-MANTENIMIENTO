<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

require_once "conexion.php";

// Obtenemos todas las ubicaciones
$sql = "SELECT id_ubicacion, nombre FROM ubicaciones ORDER BY nombre ASC";
$result = $conn->query($sql);

// -------------------- Detectar categoría automáticamente --------------------
function detectarCategoriaPorNombre($nombre) {
    $n = mb_strtolower($nombre);
    $map = [
        'laptop' => ['laptop','macbook','mac book','vivobook','acer','asus','dell','lenovo','hp pavilion','macbook air'],
        'pc' => ['pc','escritorio','desktop','optiplex','thinkcentre','elite','workstation','cpu'],
        'router' => ['router','tp-link','huawei','archer','ax3','ax'],
        'switch' => ['switch','sg350','cisco switch'],
        'monitor' => ['monitor','display','screen','ultrawide','24mh','curve'],
        'impresora' => ['impresora','printer','epson','brother','l3110','hl-'],
        'tablet' => ['tablet','galaxy tab','ipad','tab a'],
        'proyector' => ['proyector','benq','projector','ms550'],
        'servidor' => ['servidor','server']
    ];

    foreach ($map as $cat => $keywords) {
        foreach ($keywords as $kw) {
            if (strpos($n, $kw) !== false) return ucfirst($cat);
        }
    }
    return 'Otro';
}

// -------------------- Procesar formulario --------------------
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $codigo_patrimonial = trim($_POST['codigo_patrimonial']);
    $nombre             = trim($_POST['nombre_equipo']);
    $marca              = trim($_POST['marca']);
    $modelo             = trim($_POST['modelo']);
    $serie              = trim($_POST['numero_serie']);
    $ubicacion          = trim($_POST['ubicacion']);
    $estado             = trim($_POST['estado']);
    $fecha_inicio_uso   = trim($_POST['fecha_inicio_uso']);
    $tiempo_garantia    = trim($_POST['tiempo_garantia']);
    $categoria          = detectarCategoriaPorNombre($nombre);

    if ($codigo_patrimonial == "" || $nombre == "" || $ubicacion == "") {
        $mensaje = "<div class='alert error'>Completa los campos obligatorios.</div>";
    } else {
        $sql = $conn->prepare("INSERT INTO equipos 
            (codigo_patrimonial, nombre_equipo, categoria, marca, modelo, numero_serie, ubicacion, estado, fecha_inicio_uso, tiempo_garantia, fecha_registro)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $sql->bind_param("ssssssssss", $codigo_patrimonial, $nombre, $categoria, $marca, $modelo, $serie, $ubicacion, $estado, $fecha_inicio_uso, $tiempo_garantia);

        if ($sql->execute()) {
            $mensaje = "<div class='alert success'>Equipo registrado correctamente.</div>";
        } else {
            $mensaje = "<div class='alert error'>Error al registrar el equipo.</div>";
        }
    }
}

$nombre_usuario = $_SESSION['nombre'] ?? 'Usuario';
$rol = $_SESSION['rol'] ?? 'Invitado';
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Registrar Equipo</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
/* ===== BODY ===== */
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    color: #f8fafc;
    display: flex;
    padding: 20px;
    min-height: 100vh;
}

/* ===== SELECT MEJORADO ===== */
select {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 18px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.3);
    background: rgba(255, 255, 255, 0.1);
    color: #f1f5f9;
    font-size: 15px;
    appearance: none;
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
option {background: #1e293b; color: #f8fafc;}

/* ===== SIDEBAR ===== */
.sidebar {
    width: 280px;
    background: rgba(255, 255, 255, 0.06);
    backdrop-filter: blur(18px);
    border-radius: 20px;
    padding: 25px 20px;
    display: flex;
    flex-direction: column;
    gap: 25px;
    height: 95vh;
    position: sticky;
    top: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    transition: all 0.3s ease;
}
.logo-box {text-align: center;}
.logo-box img {width: 120px; filter: drop-shadow(0 0 8px rgba(56, 189, 248, 0.6));}
.user-box {background: rgba(255, 255, 255, 0.08); padding: 15px; border-radius: 16px; text-align:center; box-shadow:0 4px 10px rgba(0,0,0,0.3);}
.nav a {display:block; padding:12px 16px; margin-bottom:10px; border-radius:12px; text-decoration:none; color:#f8fafc; font-weight:500; transition:all 0.3s ease; position:relative;}
.nav a::before {content:''; position:absolute; left:-5px; top:50%; transform:translateY(-50%); width:6px; height:6px; background:#38bdf8; border-radius:50%; opacity:0; transition:0.3s;}
.nav a:hover {background: rgba(255,255,255,0.12); transform:translateX(8px);}
.nav a:hover::before {opacity:1; transform:translateY(-50%) scale(1.2);}
.active {background: rgba(255,255,255,0.18); font-weight:700; transform:translateX(8px);}
.logout-btn {margin-top:auto; text-align:center; background:#ef4444; padding:12px; border-radius:14px; color:white; text-decoration:none; font-weight:600; transition:0.3s;}
.logout-btn:hover {background:#dc2626; transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,0,0,0.4);}

/* ===== CONTENT ===== */
.content {flex:1; margin-left:30px; overflow-y:auto;}
h1 {margin-bottom:20px; font-size:32px; font-weight:700; background:linear-gradient(90deg,#38bdf8,#818cf8); -webkit-background-clip:text; color:transparent;}
.form-box {background: rgba(255,255,255,0.06); padding:30px; border-radius:18px; max-width:750px; margin-top:20px; box-shadow:0 12px 30px rgba(0,0,0,0.45);}
input, select {width:100%; padding:12px 15px; margin-bottom:18px; border-radius:12px; border:none; background: rgba(255,255,255,0.1); color:white; font-size:15px; transition: all 0.3s ease;}
input:focus, select:focus {outline:none; background: rgba(255,255,255,0.15); box-shadow:0 0 10px rgba(56,189,248,0.5);}
label {font-weight:600; margin-bottom:8px; display:block; color:#cbd5e1;}
.btn {padding:14px 18px; background: linear-gradient(135deg,#2563eb,#1e3a8a); border:none; border-radius:12px; color:white; font-weight:700; cursor:pointer; width:100%; transition:all 0.3s ease; box-shadow:0 6px 18px rgba(37,99,235,0.45);}
.btn:hover {background:linear-gradient(135deg,#1e40af,#0f172a); transform:translateY(-2px); box-shadow:0 12px 28px rgba(30,64,175,0.6);}
.alert {padding:14px; border-radius:12px; margin-bottom:20px; font-weight:500;}
.success {background: rgba(16,185,129,0.2); border-left:4px solid #10b981;}
.error {background: rgba(239,68,68,0.2); border-left:4px solid #ef4444;}
@media (max-width:900px){body{flex-direction:column; padding:10px;} .sidebar{width:100%; height:auto; flex-direction:row; overflow-x:auto; gap:15px;} .content{margin-left:0; margin-top:20px;}}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <div class="logo-box"><img src="img/logo.png"></div>
  <div class="user-box">
    <h3><?= htmlspecialchars($nombre_usuario) ?></h3>
    <span><?= htmlspecialchars(ucfirst($rol)) ?></span>
  </div>
  <div class="nav">
    <a href="panel.php" class="<?= $pagina_actual=='panel.php'?'active':'' ?>">📊 Panel</a>
    <a href="registrar_equipo.php" class="active">🖨 Registrar Equipo</a>
    <a href="lista_equipos.php">📋 Lista de Equipos</a>
  </div>
  <a class="logout-btn" href="logout.php">Cerrar sesión</a>
</div>

<!-- CONTENIDO -->
<div class="content">
<h1>Registrar Equipo</h1>
<?= $mensaje ?>
<div class="form-box">
<form method="POST">
    <label>Código Patrimonial *</label>
    <input type="text" name="codigo_patrimonial" placeholder="Ej: EQ-2025-001" required>

    <label>Nombre del equipo *</label>
    <input type="text" name="nombre_equipo" required>

    <label>Marca</label>
    <input type="text" name="marca">

    <label>Modelo</label>
    <input type="text" name="modelo">

    <label>Número de Serie</label>
    <input type="text" name="numero_serie">

    <label>Ubicación *</label>
    <select name="ubicacion" required>
        <option value="">-- Seleccione una ubicación --</option>
        <?php
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                echo '<option value="'.$row['nombre'].'">'.$row['nombre'].'</option>';
            }
        } else {
            echo '<option value="">No hay ubicaciones disponibles</option>';
        }
        ?>
    </select>

    <label>Estado</label>
    <select name="estado">
        <option value="Operativo">Operativo</option>
        <option value="En mantenimiento">En mantenimiento</option>
        <option value="Dañado">Dañado</option>
    </select>

    <button class="btn" type="submit">Registrar Equipo</button>
</form>
</div>
</div>

</body>
</html>
