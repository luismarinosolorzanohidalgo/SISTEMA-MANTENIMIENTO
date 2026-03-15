<?php
session_start();

// Seguridad
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

require_once "conexion.php";

// =============================
// CONSULTAS PRINCIPALES
// =============================

// Equipos
$equipos = $conn->query("SELECT id_equipo, nombre_equipo FROM equipos ORDER BY nombre_equipo ASC");

// Ubicaciones
$ubicaciones = $conn->query("SELECT id_ubicacion, nombre FROM ubicaciones ORDER BY nombre ASC");

// Rotaciones
$rotaciones = $conn->query("
    SELECT r.*, e.nombre_equipo 
    FROM rotaciones r
    INNER JOIN equipos e ON r.id_equipo = e.id_equipo
    ORDER BY r.fecha_registro DESC
");

// =============================
// GUARDAR ROTACIÓN
// =============================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_equipo     = intval($_POST['id_equipo']);
    $origen        = $conn->real_escape_string($_POST['origen']);
    $fecha_origen  = $_POST['fecha_origen'];
    $destino       = $conn->real_escape_string($_POST['destino']);
    $fecha_destino = $_POST['fecha_destino'];

    $sql = "INSERT INTO rotaciones (id_equipo, origen, fecha_origen, destino, fecha_destino, fecha_registro)
            VALUES ($id_equipo, '$origen', '$fecha_origen', '$destino', '$fecha_destino', NOW())";

    if ($conn->query($sql)) {
        header("Location: rotaciones.php?success=1");
        exit();
    } else {
        header("Location: rotaciones.php?error=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Rotación de Equipos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Fuente -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ==========================================================
           LIQUID GLASS UI — Premium Corporate Style
        ========================================================== */

        body {
            background: linear-gradient(135deg, #e9f1fc, #fdfdff);
            font-family: "Inter", sans-serif;
        }

        h3 {
            font-weight: 800;
            color: #0f2c68;
        }

        /* Tarjeta Principal */
        .glass-card {
            background: rgba(255, 255, 255, 0.32);
            border-radius: 22px;
            border: 1px solid rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transition: .3s ease;
        }

        .glass-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.22);
        }

        /* Tabla */
        .table thead th {
            background: rgba(30, 58, 138, 0.92);
            color: #fff;
            font-weight: 600;
            border: none;
        }

        .table tbody tr:hover {
            background: rgba(59, 130, 246, 0.12);
        }

        td {
            color: #0f1f3d;
        }

        /* Botones */
        .btn-primary {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            border: none;
            border-radius: 14px;
            font-weight: 600;
            padding: 10px 18px;
            box-shadow: 0 6px 15px rgba(59, 130, 246, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #1e40af, #2563eb);
        }

        .btn-outline-light {
            background: rgba(255, 255, 255, 0.35);
            backdrop-filter: blur(6px);
            border-radius: 12px;
            font-weight: 600;
            color: #1e3a8a;
            border: none;
        }

        /* Modal */
        .modal-content {
            background: rgba(255, 255, 255, 0.45);
            border-radius: 22px;
            border: 1px solid rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(25px);
        }

        .modal-header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            border-radius: 20px 20px 0 0;
            color: #fff;
        }

        /* Input glass */
        .form-control,
        .form-select {
            background: rgba(255, 255, 255, 0.35);
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 10px rgba(37, 99, 235, 0.4);
        }
    </style>
</head>

<body>

    <div class="container py-4">

        <div class="d-flex justify-content-between mb-4">
            <h3>📦 Rotación de Equipos</h3>

            <div>
                <a href="panel.php" class="btn btn-outline-light">← Volver</a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRotacion">➕ Registrar Rotación</button>
            </div>
        </div>

        <!-- TABLA -->
        <div class="glass-card p-3">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Equipo</th>
                            <th>Origen</th>
                            <th>Fecha Origen</th>
                            <th>Destino</th>
                            <th>Fecha Destino</th>
                            <th>Registrado</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($row = $rotaciones->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= $row['nombre_equipo'] ?></strong></td>
                                <td><?= $row['origen'] ?></td>
                                <td><?= date("d/m/Y", strtotime($row['fecha_origen'])) ?></td>
                                <td><?= $row['destino'] ?></td>
                                <td><?= date("d/m/Y", strtotime($row['fecha_destino'])) ?></td>
                                <td><?= date("d/m/Y H:i", strtotime($row['fecha_registro'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>

    <!-- MODAL -->
    <div class="modal fade" id="modalRotacion">
        <div class="modal-dialog">
            <div class="modal-content">

                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Registrar Rotación</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <label class="form-label">Equipo</label>
                        <select name="id_equipo" class="form-select mb-3" required>
                            <option value="">Seleccione...</option>
                            <?php
                            $equipos->data_seek(0);
                            while ($e = $equipos->fetch_assoc()):
                            ?>
                                <option value="<?= $e['id_equipo'] ?>"><?= $e['nombre_equipo'] ?></option>
                            <?php endwhile; ?>
                        </select>

                        <label class="form-label">Origen</label>
                        <select name="origen" class="form-select mb-3" required>
                            <option value="">Seleccione origen...</option>
                            <?php
                            $ubicaciones->data_seek(0);
                            while ($u = $ubicaciones->fetch_assoc()):
                            ?>
                                <option><?= $u['nombre'] ?></option>
                            <?php endwhile; ?>
                        </select>

                        <label class="form-label">Fecha Origen</label>
                        <input type="date" name="fecha_origen" class="form-control mb-3" required>

                        <label class="form-label">Destino</label>
                        <select name="destino" class="form-select mb-3" required>
                            <option value="">Seleccione destino...</option>
                            <?php
                            $ubicaciones->data_seek(0);
                            while ($u = $ubicaciones->fetch_assoc()):
                            ?>
                                <option><?= $u['nombre'] ?></option>
                            <?php endwhile; ?>
                        </select>

                        <label class="form-label">Fecha Destino</label>
                        <input type="date" name="fecha_destino" class="form-control" required>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary px-4">Guardar</button>
                        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        <?php if (isset($_GET['success'])): ?>
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: "Rotación registrada",
                showConfirmButton: false,
                timer: 2500,
                background: "#1f2937",
                color: "#fff"
            });
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            Swal.fire({
                icon: "error",
                title: "Hubo un error al registrar",
                confirmButtonColor: "#EF4444"
            });
        <?php endif; ?>
    </script>

</body>
</html>
