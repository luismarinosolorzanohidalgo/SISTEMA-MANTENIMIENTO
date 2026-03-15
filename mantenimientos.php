<?php
session_start();
if (!isset($_SESSION['rol'])) {
  header("Location: login.php");
  exit();
}
require_once "conexion.php";

/*
  Sistema mixto:
  - categorías / intervalos predefinidos (si tabla categoria_intervalos está vacía se insertan)
  - calcula próximos mantenimientos por categoría (no lo inserta hasta que el usuario 'Programe' o se ejecute Generar ahora)
  - botón "Programar" convierte un próximo en mantenimiento real y permite asignar responsable
  - botón "Generar ahora" crea todos los mantenimientos vencidos automáticamente (responsable = 'Sistema')
*/

/* ------------------------------
   Configuración por defecto
   ------------------------------ */
$defaults = [
  ['categoria' => 'Laptop', 'intervalo' => 4],
  ['categoria' => 'PC', 'intervalo' => 6],
  ['categoria' => 'Impresora', 'intervalo' => 3],
  ['categoria' => 'Router', 'intervalo' => 8],
  ['categoria' => 'Switch', 'intervalo' => 8],
  ['categoria' => 'Monitor', 'intervalo' => 10],
  ['categoria' => 'Otro', 'intervalo' => 6],
];

$materiales = [
  'Laptop' => ['Brocha', 'Aire comprimido', 'Pasta térmica'],
  'PC' => ['Aspiradora', 'Brocha', 'Pasta térmica'],
  'Impresora' => ['Alcohol isopropílico', 'Hisopos', 'Lubricante para rodillos'],
  'Router' => ['Paño seco', 'Aire comprimido'],
  'Switch' => ['Aire comprimido', 'Limpiador antiestático'],
  'Monitor' => ['Paño microfibra', 'Spray antiestático'],
  'Otro' => ['Brocha'],
];


$check = $conn->query("SELECT COUNT(*) AS c FROM categoria_intervalos");
if ($check) {
  $c = $check->fetch_assoc()['c'] ?? 0;
  if ($c == 0) {
    $stmt = $conn->prepare("INSERT INTO categoria_intervalos (categoria, intervalo_meses, tipo_mantenimiento) VALUES (?, ?, 'Preventivo')");
    foreach ($defaults as $d) {
      $stmt->bind_param("si", $d['categoria'], $d['intervalo']);
      $stmt->execute();
    }
    $stmt->close();
  }
}

/* ------------------------------
   Manejo upload
   ------------------------------ */
$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

/* ------------------------------
   Acciones GET: generar automáticos ahora
   ------------------------------ */
if (isset($_GET['generar_auto']) && $_GET['generar_auto'] == '1') {
  // Genera mantenimientos automáticos vencidos (responsable = 'Sistema')
  $sqlEquipos = "
        SELECT e.id_equipo, e.nombre_equipo, e.categoria,
               COALESCE(e.ultimo_mantenimiento, e.fecha_registro, CURDATE()) AS fecha_base,
               c.intervalo_meses
        FROM equipos e
        INNER JOIN categoria_intervalos c ON e.categoria = c.categoria
    ";
  $eqs = $conn->query($sqlEquipos);
  $gen = 0;
  while ($eq = $eqs->fetch_assoc()) {
    $fecha_base = $eq['fecha_base'];
    $intervalo = intval($eq['intervalo_meses']);
    $fecha_siguiente = date("Y-m-d", strtotime("+$intervalo months", strtotime($fecha_base)));
    if ($fecha_siguiente <= date("Y-m-d")) {
      // Verificar no duplicar (En Proceso)
      $chk = $conn->prepare("SELECT id_mantenimiento FROM mantenimientos WHERE id_equipo=? AND estado='En Proceso' LIMIT 1");
      $chk->bind_param("i", $eq['id_equipo']);
      $chk->execute();
      $res = $chk->get_result();
      if ($res->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO mantenimientos (id_equipo, tipo, descripcion, responsable, fecha_inicio, estado) VALUES (?, 'Preventivo', 'Mantenimiento automático programado', 'Sistema', CURDATE(), 'En Proceso')");
        $stmt->bind_param("i", $eq['id_equipo']);
        $stmt->execute();
        $stmt->close();
        // actualizar ultimo_mantenimiento para no generar otra vez inmediatamente
        $upd = $conn->prepare("UPDATE equipos SET ultimo_mantenimiento=CURDATE() WHERE id_equipo=?");
        $upd->bind_param("i", $eq['id_equipo']);
        $upd->execute();
        $upd->close();
        $gen++;
      }
      $chk->close();
    }
  }
  header("Location: mantenimientos.php?msg=generados&n=$gen");
  exit();
}
if (isset($_GET['generar_auto']) && $_GET['generar_auto'] == '1') {

    // Insertar todos los mantenimientos vencidos que no tengan "En Proceso"
    $sqlInsert = "
        INSERT INTO mantenimientos (id_equipo, tipo, descripcion, responsable, fecha_inicio, estado)
        SELECT e.id_equipo, 'Preventivo', 'Mantenimiento automático programado', 'Sistema', CURDATE(), 'En Proceso'
        FROM equipos e
        INNER JOIN categoria_intervalos c ON e.categoria = c.categoria
        LEFT JOIN mantenimientos m
            ON m.id_equipo = e.id_equipo AND m.estado = 'En Proceso'
        WHERE m.id_mantenimiento IS NULL
        AND DATE_ADD(COALESCE(e.ultimo_mantenimiento, e.fecha_registro, CURDATE()), INTERVAL c.intervalo_meses MONTH) <= CURDATE()
    ";

    $gen = $conn->query($sqlInsert);
    $numGenerados = $conn->affected_rows;

    // Actualizar ultimo_mantenimiento para los equipos generados
    $sqlUpd = "
        UPDATE equipos e
        INNER JOIN (
            SELECT id_equipo
            FROM mantenimientos
            WHERE responsable='Sistema' AND fecha_inicio=CURDATE()
        ) t ON e.id_equipo = t.id_equipo
        SET e.ultimo_mantenimiento = CURDATE()
    ";
    $conn->query($sqlUpd);

    header("Location: mantenimientos.php?msg=generados&n=$numGenerados");
    exit();
}

/* ------------------------------
   Manejo POST (nuevo / editar / programar desde automático)
   ------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $modo = $_POST['modo'] ?? 'nuevo';
  $id_equipo = intval($_POST['id_equipo'] ?? 0);
  $tipo = $_POST['tipo'] ?? 'Preventivo';
  $descripcion = trim($_POST['descripcion'] ?? '');
  $responsable = trim($_POST['responsable'] ?? '');
  $fecha_inicio = $_POST['fecha_inicio'] ?: null;
  $fecha_fin = $_POST['fecha_fin'] ?: null;

  // archivo opcional
  $filename = null;
  if (!empty($_FILES['documento']['name'] ?? '')) {
    $tmp = $_FILES['documento']['tmp_name'];
    $orig = basename($_FILES['documento']['name']);
    $ext = pathinfo($orig, PATHINFO_EXTENSION);
    $safe = 'doc_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (move_uploaded_file($tmp, $upload_dir . $safe)) {
      $filename = 'uploads/' . $safe;
    }
  }

  if ($modo === 'nuevo') {
    $stmt = $conn->prepare("INSERT INTO mantenimientos (id_equipo, tipo, descripcion, responsable, fecha_inicio, fecha_fin, documento, estado) VALUES (?, ?, ?, ?, ?, ?, ?, 'En Proceso')");
    $stmt->bind_param("issssss", $id_equipo, $tipo, $descripcion, $responsable, $fecha_inicio, $fecha_fin, $filename);
    $ok = $stmt->execute();
    $stmt->close();
    header("Location: mantenimientos.php?msg=creado");
    exit();
  }

  if ($modo === 'editar') {
    $id_m = intval($_POST['id_mantenimiento']);
    if ($filename) {
      $stmt = $conn->prepare("UPDATE mantenimientos SET id_equipo=?, tipo=?, descripcion=?, responsable=?, fecha_inicio=?, fecha_fin=?, documento=? WHERE id_mantenimiento=?");
      $stmt->bind_param("issssssi", $id_equipo, $tipo, $descripcion, $responsable, $fecha_inicio, $fecha_fin, $filename, $id_m);
    } else {
      $stmt = $conn->prepare("UPDATE mantenimientos SET id_equipo=?, tipo=?, descripcion=?, responsable=?, fecha_inicio=?, fecha_fin=? WHERE id_mantenimiento=?");
      $stmt->bind_param("isssssi", $id_equipo, $tipo, $descripcion, $responsable, $fecha_inicio, $fecha_fin, $id_m);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: mantenimientos.php?msg=actualizado");
    exit();
  }

  if ($modo === 'programar') {
    // Programar un mantenimiento desde el cálculo automático: crea registro con datos prellenados
    // Espera id_equipo, fecha_programada, responsable (puede venir vacío)
    $fecha_programada = $_POST['fecha_programada'] ?: date("Y-m-d");
    $responsable = trim($_POST['responsable']) ?: 'Pendiente';
    $descripcion = trim($_POST['descripcion']) ?: 'Mantenimiento programado desde calendario automático';

    $stmt = $conn->prepare("INSERT INTO mantenimientos (id_equipo, tipo, descripcion, responsable, fecha_inicio, estado) VALUES (?, 'Preventivo', ?, ?, ?, 'En Proceso')");
    $stmt->bind_param("isss", $id_equipo, $descripcion, $responsable, $fecha_programada);
    $stmt->execute();
    $stmt->close();

    // opcional: actualizar ultimo_mantenimiento cuando se complete (no ahora)
    header("Location: mantenimientos.php?msg=programado");
    exit();
  }
}

/* ------------------------------
   Finalizar y eliminar (GET)
   ------------------------------ */
if (isset($_GET['finalizar'])) {
  $id = intval($_GET['finalizar']);
  $hoy = date("Y-m-d");
  $stmt = $conn->prepare("UPDATE mantenimientos SET estado='Completado', fecha_fin=? WHERE id_mantenimiento=?");
  $stmt->bind_param("si", $hoy, $id);
  $stmt->execute();
  $stmt->close();

  // actualizar ultimo_mantenimiento del equipo asociado
  $res = $conn->query("SELECT id_equipo FROM mantenimientos WHERE id_mantenimiento=$id");
  if ($res && $row = $res->fetch_assoc()) {
    $upd = $conn->prepare("UPDATE equipos SET ultimo_mantenimiento = ? WHERE id_equipo = ?");
    $upd->bind_param("si", $hoy, $row['id_equipo']);
    $upd->execute();
    $upd->close();
  }

  header("Location: mantenimientos.php?msg=finalizado");
  exit();
}

if (isset($_GET['eliminar'])) {
  $id = intval($_GET['eliminar']);
  $res = $conn->query("SELECT documento FROM mantenimientos WHERE id_mantenimiento=$id");
  if ($res && $r = $res->fetch_assoc()) {
    if (!empty($r['documento']) && file_exists(__DIR__ . '/' . $r['documento'])) unlink(__DIR__ . '/' . $r['documento']);
  }
  $conn->query("DELETE FROM mantenimientos WHERE id_mantenimiento=$id");
  header("Location: mantenimientos.php?msg=eliminado");
  exit();
}

/* ------------------------------
   Consultas para mostrar
   ------------------------------ */
$equipos = $conn->query("SELECT id_equipo, nombre_equipo, categoria FROM equipos ORDER BY nombre_equipo ASC");

// Mantenimientos (manuales)
$mantenimientos = $conn->query("
    SELECT m.*, e.nombre_equipo 
    FROM mantenimientos m
    LEFT JOIN equipos e ON m.id_equipo = e.id_equipo
    ORDER BY FIELD(m.estado,'En Proceso','Completado'), m.fecha_inicio DESC
");

// Próximos calculados por categoría (no insertados)
$proximos = $conn->query("
    SELECT 
        e.id_equipo,
        e.nombre_equipo,
        e.categoria,
        COALESCE(e.ultimo_mantenimiento, e.fecha_registro, CURDATE()) AS fecha_base,
        c.intervalo_meses,
        DATE_ADD(COALESCE(e.ultimo_mantenimiento, e.fecha_registro, CURDATE()), INTERVAL c.intervalo_meses MONTH) AS proximo
    FROM equipos e
    INNER JOIN categoria_intervalos c ON e.categoria = c.categoria
    ORDER BY proximo ASC
");

?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Mantenimientos (Automático + Manual)</title>

  <!-- Bootstrap & SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Fuente -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --bg: #eef4fb;
      --glass: rgba(255, 255, 255, 0.55);
      --primary: #1e3a8a;
      --accent: #c8a548;
      --radius: 14px;
    }

    body {
      background: var(--bg);
      font-family: 'Poppins', sans-serif;
      color: #0f172a;
      padding: 24px;
    }

    /* Container glass */
    .header-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      margin-bottom: 18px;
    }

    .glass {
      background: var(--glass);
      border-radius: var(--radius);
      padding: 18px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
      border: 1px solid rgba(15, 23, 42, 0.04);
    }

    .table thead th {
      background: linear-gradient(90deg, var(--primary), #3b82f6);
      color: #fff;
      border: none;
    }



    .btn-ghost {
      background: transparent;
      border: 1px solid rgba(15, 23, 42, 0.06);
    }

    .badge-enpro {
      background: #f59e0b;
      color: #fff;
      padding: 6px 10px;
      border-radius: 8px;
      font-weight: 700;
    }

    .badge-comp {
      background: #10b981;
      color: #fff;
      padding: 6px 10px;
      border-radius: 8px;
      font-weight: 700;
    }

    .card-materials {
      background: rgba(255, 255, 255, 0.85);
      padding: 12px;
      border-radius: 10px;
      box-shadow: inset 0 1px 0 rgba(0, 0, 0, 0.02);
    }

    .small-muted {
      color: #6b7280;
      font-size: 13px;
    }

    .program-btn {
      background: linear-gradient(135deg, #06b6d4, #2563eb);
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 6px 10px;
      font-weight: 700;
    }

    .btn-volver,
    .btn-primary {
      display: inline-block;
      margin-top: 15px;
      padding: 8px 18px;
      background: linear-gradient(90deg, var(--primary), #3b82f6);
      border-radius: 8px;
      color: #fff;
      text-decoration: none;
      font-weight: 500;
      border: none;
      /* Para btn-primary */
      transition: 0.2s;
      text-align: center;
      cursor: pointer;
    }

    .btn-volver:hover,
    .btn-primary:hover {
      opacity: 0.85;
    }


    @media(max-width:900px) {
      .header-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }
    }
  </style>
</head>

<body>

  <div class="container">

    <div class="header-row">
      <div>
        <h2 style="margin:0;color:var(--primary)">🛠 Mantenimientos </h2>
        <div class="small-muted">Automático por categoría + Manual. Programa, asigna responsable y controla el estado.</div>
      </div>

      <div class="d-flex gap-2">
        <a href="mantenimientos.php?generar_auto=1" class="btn btn-ghost">Generar ahora (auto)</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm">+ Nuevo mantenimiento</button>
        <a href="panel.php" class="btn-volver">← Volver</a>
      </div>
    </div>

    <!-- Tabla manual / CRUD -->
    <!--<div class="glass mb-4">
      <h5 class="text-primary">Registros - Mantenimientos</h5>
      <div class="table-responsive mt-3">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Equipo</th>
              <th>Tipo</th>
              <th>Responsable</th>
              <th>Inicio</th>
              <th>Fin</th>
              <th>Estado</th>
              <th>Documento</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($m = $mantenimientos->fetch_assoc()): ?>
              <tr>
                <td><strong><?= htmlspecialchars($m['nombre_equipo'] ?? '—') ?></strong><br><span class="small-muted">ID: <?= $m['id_mantenimiento'] ?></span></td>
                <td><?= htmlspecialchars($m['tipo']) ?></td>
                <td><?= htmlspecialchars($m['responsable'] ?: '—') ?></td>
                <td><?= $m['fecha_inicio'] ?: '—' ?></td>
                <td><?= $m['fecha_fin'] ?: '—' ?></td>
                <td>
                  <?php if ($m['estado'] === 'Completado'): ?>
                    <span class="badge-comp">Completado</span>
                  <?php else: ?>
                    <span class="badge-enpro">En Proceso</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($m['documento'])): ?>
                    <a href="<?= htmlspecialchars($m['documento']) ?>" target="_blank">Ver</a>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
                <td style="min-width:220px">
                  <button class="btn btn-sm btn-outline-primary" onclick='openEdit(<?= json_encode($m) ?>)'>Editar</button>
                  <?php if ($m['estado'] !== 'Completado'): ?>
                    <a href="?finalizar=<?= $m['id_mantenimiento'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Finalizar mantenimiento?')">Finalizar</a>
                  <?php endif; ?>
                  <a href="?eliminar=<?= $m['id_mantenimiento'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Eliminar mantenimiento?')">Eliminar</a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div> -->

    <!-- Próximos calculados (automáticos) -->
    <div class="glass mb-4">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="text-primary">Próximos mantenimientos calculados</h5>
        <div class="small-muted">Convierte cualquiera en un mantenimiento real con "Programar".</div>
      </div>

      <div class="table-responsive mt-3">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Equipo</th>
              <th>Categoría</th>
              <th>Fecha base</th>
              <th>Intervalo (meses)</th>
              <th>Próximo</th>
              <th>Materiales</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($p = $proximos->fetch_assoc()):
              $cat = $p['categoria'];
              $mats = $materiales[$cat] ?? ($materiales['Otro'] ?? []);
            ?>
              <tr>
                <td><strong><?= htmlspecialchars($p['nombre_equipo']) ?></strong><br><span class="small-muted">ID: <?= $p['id_equipo'] ?></span></td>
                <td><?= htmlspecialchars($cat) ?></td>
                <td><?= date("d/m/Y", strtotime($p['fecha_base'])) ?></td>
                <td><?= intval($p['intervalo_meses']) ?></td>
                <td><strong><?= date("d/m/Y", strtotime($p['proximo'])) ?></strong></td>
                <td>
                  <div class="card-materials">
                    <?php foreach ($mats as $mm): ?>
                      <div style="font-size:13px">• <?= htmlspecialchars($mm) ?></div>
                    <?php endforeach; ?>
                  </div>
                </td>
                <td>
                  <button class="program-btn" onclick='openProgram(<?= json_encode($p) ?>)'>Programar</button>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- Modal (Nuevo / Editar) -->
  <div class="modal fade" id="modalForm" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <form class="modal-content" method="post" enctype="multipart/form-data" id="formMant">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalTitle">Registrar Mantenimiento</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="modo" id="modo" value="nuevo">
          <input type="hidden" name="id_mantenimiento" id="id_mantenimiento" value="">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Equipo</label>
              <select name="id_equipo" id="id_equipo" class="form-select" required>
                <option value="">Seleccione equipo...</option>
                <?php
                $equipos->data_seek(0);
                while ($e = $equipos->fetch_assoc()):
                ?>
                  <option value="<?= $e['id_equipo'] ?>"><?= htmlspecialchars($e['nombre_equipo']) ?> — <?= htmlspecialchars($e['categoria']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label">Tipo</label>
              <select name="tipo" id="tipo" class="form-select">
                <option value="Preventivo">Preventivo</option>
                <option value="Correctivo">Correctivo</option>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label">Responsable</label>
              <select name="responsable" id="responsable" class="form-control" required>
                <option value="">-- Responsable --</option>
                <option value="Jairo">Jairo</option>
                <option value="Carlos">Carlos</option>
                <option value="Miguel">Miguel</option>
                <option value="Luis">Luis</option>
                <option value="Jorge">Jorge</option>
                <option value="Juan">Juan</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Fecha inicio</label>
              <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Fecha fin (opcional)</label>
              <input type="date" name="fecha_fin" id="fecha_fin" class="form-control">
            </div>

            <div class="col-12">
              <label class="form-label">Descripción</label>
              <textarea name="descripcion" id="descripcion" class="form-control" rows="3" placeholder="Observaciones"></textarea>
            </div>

            <div class="col-md-6">
              <label class="form-label">Documento (opcional)</label>
              <input type="file" name="documento" class="form-control">
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Guardar</button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Programar desde automático -->
  <div class="modal fade" id="modalProgram" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content" method="post" id="formProgram">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Programar mantenimiento</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="modo" value="programar">
          <input type="hidden" name="id_equipo" id="prog_id_equipo" value="">

          <div class="mb-2">
            <label class="form-label">Fecha programada</label>
            <input type="date" name="fecha_programada" id="prog_fecha" class="form-control" required>
          </div>

          <div class="mb-2">
            <label class="form-label">Responsable</label>
            <input type="text" name="responsable" id="prog_responsable" class="form-control" placeholder="Nombre técnico">
          </div>

          <div class="mb-2">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" id="prog_descripcion" class="form-control" rows="3">Mantenimiento programado desde cálculo automático</textarea>
          </div>

          <div class="small-muted">Puedes asignar responsable y ajustar la fecha antes de guardar.</div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-primary" type="submit">Programar</button>
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    /* SweetAlert mensajes */
    const params = new URLSearchParams(window.location.search);
    if (params.get('msg') === 'creado') {
      Swal.fire({
        icon: 'success',
        title: 'Registrado',
        toast: true,
        position: 'top-end',
        timer: 1500,
        showConfirmButton: false
      });
    }
    if (params.get('msg') === 'actualizado') {
      Swal.fire({
        icon: 'success',
        title: 'Actualizado',
        toast: true,
        position: 'top-end',
        timer: 1500,
        showConfirmButton: false
      });
    }
    if (params.get('msg') === 'finalizado') {
      Swal.fire({
        icon: 'success',
        title: 'Finalizado',
        toast: true,
        position: 'top-end',
        timer: 1500,
        showConfirmButton: false
      });
    }
    if (params.get('msg') === 'eliminado') {
      Swal.fire({
        icon: 'success',
        title: 'Eliminado',
        toast: true,
        position: 'top-end',
        timer: 1500,
        showConfirmButton: false
      });
    }
    if (params.get('msg') === 'programado') {
      Swal.fire({
        icon: 'success',
        title: 'Programado',
        toast: true,
        position: 'top-end',
        timer: 1500,
        showConfirmButton: false
      });
    }
    if (params.get('msg') === 'generados') {
      const n = params.get('n') || 0;
      Swal.fire({
        icon: 'success',
        title: `Generados: ${n}`,
        text: 'Mantenimientos automáticos creados',
        toast: true,
        position: 'top-end',
        timer: 2000,
        showConfirmButton: false
      });
    }

    /* Abrir modal edición (manual) */
    function openEdit(data) {
      document.getElementById('modalTitle').innerText = 'Editar mantenimiento';
      document.getElementById('modo').value = 'editar';
      document.getElementById('id_mantenimiento').value = data.id_mantenimiento || '';
      document.getElementById('id_equipo').value = data.id_equipo || '';
      document.getElementById('tipo').value = data.tipo || 'Preventivo';
      document.getElementById('responsable').value = data.responsable || '';
      document.getElementById('fecha_inicio').value = data.fecha_inicio || '';
      document.getElementById('fecha_fin').value = data.fecha_fin || '';
      document.getElementById('descripcion').value = data.descripcion || '';
      var modal = new bootstrap.Modal(document.getElementById('modalForm'));
      modal.show();
    }

    /* Abrir modal programar (desde calculados) */
    function openProgram(p) {
      document.getElementById('prog_id_equipo').value = p.id_equipo || '';
      // sugerir fecha = p.proximo
      let fecha = p.proximo ? p.proximo.split(' ')[0] : new Date().toISOString().slice(0, 10);
      document.getElementById('prog_fecha').value = fecha;
      document.getElementById('prog_responsable').value = '';
      document.getElementById('prog_descripcion').value = 'Mantenimiento programado desde cálculo automático';
      var modal = new bootstrap.Modal(document.getElementById('modalProgram'));
      modal.show();
    }

    /* Reset modal cuando se cierra (nuevo) */
    var modalFormEl = document.getElementById('modalForm');
    modalFormEl.addEventListener('hidden.bs.modal', function() {
      document.getElementById('modalTitle').innerText = 'Registrar Mantenimiento';
      document.getElementById('modo').value = 'nuevo';
      document.getElementById('id_mantenimiento').value = '';
      document.getElementById('id_equipo').value = '';
      document.getElementById('tipo').value = 'Preventivo';
      document.getElementById('responsable').value = '';
      document.getElementById('fecha_inicio').value = '';
      document.getElementById('fecha_fin').value = '';
      document.getElementById('descripcion').value = '';
    });

    /* Confirm antes de generar automáticos (opcional) */
    document.querySelectorAll('a[href*="generar_auto=1"]').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        if (!confirm('Generar ahora los mantenimientos vencidos (responsable = Sistema)?')) e.preventDefault();
      });
    });
  </script>

</body>

</html>