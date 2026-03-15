<?php
session_start();
if(!isset($_SESSION['rol'])){
    header("Location: login.php");
    exit();
}
require_once "conexion.php";

// FILTRO
$tipoFiltro = $_POST['tipo_filtro'] ?? '';
$where = '';
if($tipoFiltro && $tipoFiltro!=='todos'){
    $where="WHERE tipo='$tipoFiltro'";
}

// CONSULTA
$result = $conn->query("SELECT * FROM mantenimientos $where ORDER BY fecha_inicio DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mantenimientos | Sistema</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Fuente -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
/* =====================================================
   ESTILO MODERNO Y PROFESIONAL - LIQUID GLASS UI
===================================================== */

/* ========================
   MODAL PREMIUM LIQUID GLASS
========================= */

.modal {
    display: none; /* Por defecto oculto */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.45); /* Fondo semitransparente */
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    animation: fadeIn 0.3s ease;
}

.modal-content {
    background: rgba(255,255,255,0.5);
    border-radius: 25px;
    border: 1px solid rgba(255,255,255,0.6);
    backdrop-filter: blur(25px);
    padding: 25px 30px;
    min-width: 320px;
    max-width: 500px;
    box-shadow: 0 12px 35px rgba(0,0,0,0.2);
    animation: popup 0.35s ease;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Animaciones */
@keyframes popup {
    from { transform: scale(0.7); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Encabezado personalizado sin barra */
.modal-header {
    text-align: center;
    color: #fff;
    font-weight: 700;
    font-size: 18px;
    letter-spacing: 0.5px;
    padding: 12px 0;
    border-radius: 20px 20px 0 0;
    background: linear-gradient(135deg,#1e3a8a,#3b82f6);
    margin: -25px -30px 10px -30px; /* Extiende el header full width */
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Inputs y selects tipo glass */
.modal input,
.modal select,
.modal textarea {
    width: 100%;
    padding: 10px 12px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.6);
    background: rgba(255,255,255,0.35);
    font-size: 14px;
    transition: all 0.3s ease;
}

.modal input:focus,
.modal select:focus,
.modal textarea:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 10px rgba(59,130,246,0.3);
    outline: none;
}

/* Botones */
.modal .btn {
    width: 100%;
    margin-top: 12px;
    padding: 10px 0;
    border-radius: 14px;
    font-weight: 700;
    color: white;
    border: none;
    cursor: pointer;
    transition: 0.3s;
}

.modal .btn-save {
    background: #1e3a8a;
    box-shadow: 0 6px 12px rgba(30,58,138,0.4);
}

.modal .btn-save:hover {
    background: #0f172a;
    transform: translateY(-2px);
}

.modal .btn-cancel {
    background: #ef4444;
    margin-top: 8px;
    box-shadow: 0 4px 10px rgba(239,68,68,0.3);
}

.modal .btn-cancel:hover {
    background: #dc2626;
    transform: translateY(-2px);
}

body {
    background: linear-gradient(135deg, #e0e7ff, #f0f4f8);
    font-family: 'Inter', sans-serif;
    padding: 20px;
    color: #1f2937;
    transition: background 0.5s ease;
}

/* TITULO */
h2 {
    text-align: center;
    color: #1e3a8a;
    margin-bottom: 30px;
    font-weight: 800;
    letter-spacing: 1px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

/* FILTROS */
.filter-container {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 25px;
}

.filter-container select, 
.filter-container button {
    padding: 10px 14px;
    border-radius: 12px;
    border: 1px solid #cbd5e1;
    font-size: 14px;
    transition: all 0.3s ease;
    outline: none;
}

.filter-container select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 8px rgba(59, 130, 246, 0.3);
}

.btn-filter {
    background: linear-gradient(135deg, #1e3a8a, #3b82f6);
    color: white;
    border: none;
    cursor: pointer;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(59,130,246,0.3);
}

.btn-filter:hover {
    background: linear-gradient(135deg, #1e40af, #2563eb);
    transform: translateY(-2px);
}

.btn-clear {
    background: #6b7280;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: 500;
}

.btn-clear:hover {
    background: #374151;
    transform: translateY(-2px);
}

/* CARD */
.glass-card {
    background: rgba(255, 255, 255, 0.75);
    border-radius: 22px;
    padding: 20px;
    backdrop-filter: blur(15px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.glass-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
}

/* TABLA */
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
    transition: all 0.3s ease;
}

th, td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #e2e8f0;
    transition: background 0.3s ease, transform 0.2s ease;
}

th {
    background: #1e3a8a;
    color: white;
    font-weight: 600;
    letter-spacing: 0.5px;
}

tbody tr:hover {
    background: rgba(59, 130, 246, 0.12);
    transform: scale(1.01);
}

/* BOTONES EN TABLA */
td .btn {
    margin: 2px;
    font-size: 13px;
    border-radius: 8px;
    padding: 5px 10px;
    cursor: pointer;
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-edit {
    background: #0ea5e9;
    color: white;
}

.btn-edit:hover {
    background: #0284c7;
    transform: translateY(-2px);
}

.btn-finalizar {
    background: #22c55e;
    color: white;
}

.btn-finalizar:hover {
    background: #16a34a;
    transform: translateY(-2px);
}

.btn-delete {
    background: #ef4444;
    color: white;
}

.btn-delete:hover {
    background: #dc2626;
    transform: translateY(-2px);
}

.btn-doc {
    background: #3b82f6;
    color: white;
    padding: 5px 12px;
    border-radius: 8px;
    text-decoration: none;
    display: inline-block;
    font-weight: 500;
}

.btn-volver {
    margin-top: 20px;
    padding: 10px 22px;
    background: #1e3a8a;
    color: white;
    border: none;
    border-radius: 14px;
    font-weight: 600;
    transition: 0.3s;
}

.btn-volver:hover {
    background: #0f172a;
    transform: translateY(-2px);
}

/* MODAL LIQUID GLASS */
.modal {
    display: none;
    position: fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background: rgba(0,0,0,0.45);
    justify-content: center;
    align-items: center;
    z-index: 1000;
    animation: fadeIn 0.3s ease;
}

.modal-content {
    background: rgba(255,255,255,0.5);
    border-radius: 22px;
    border: 1px solid rgba(255,255,255,0.6);
    backdrop-filter: blur(25px);
    padding: 25px;
    min-width: 320px;
    max-width: 480px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    animation: popup 0.3s ease;
}

@keyframes popup {
    from { transform: scale(0.7); opacity:0; }
    to { transform: scale(1); opacity:1; }
}

@keyframes fadeIn {
    from { opacity:0; }
    to { opacity:1; }
}

.modal-header {
    background: linear-gradient(135deg,#1e3a8a,#3b82f6);
    color:white;
    border-radius:15px 15px 0 0;
    padding:12px;
    font-weight:700;
    text-align:center;
    letter-spacing:0.5px;
}

.modal input, .modal select {
    width:100%;
    padding:10px;
    margin:8px 0;
    border-radius:10px;
    border:1px solid rgba(255,255,255,0.6);
    background: rgba(255,255,255,0.35);
    font-size:14px;
    transition: all 0.3s ease;
}

.modal input:focus, .modal select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 8px rgba(59,130,246,0.3);
}

.modal .btn {
    width:100%;
    margin-top:12px;
    padding:10px;
    border-radius:12px;
    background:#1e3a8a;
    color:white;
    border:none;
    font-weight:700;
    transition: 0.3s;
}

.modal .btn:hover {
    background:#0f172a;
    transform: translateY(-2px);
}

.modal .btn-cancel {
    background:#ef4444;
    margin-top:8px;
}


</style>
</head>
<body>

<h2>📋 Mantenimientos</h2>

<div class="filter-container">
<form method="POST" style="display:flex;gap:10px;flex-wrap:wrap;">
    <select name="tipo_filtro">
        <option value="todos" <?= $tipoFiltro=='todos'?'selected':'' ?>>Todos</option>
        <option value="Correctivo" <?= $tipoFiltro=='Correctivo'?'selected':'' ?>>Correctivo</option>
        <option value="Preventivo" <?= $tipoFiltro=='Preventivo'?'selected':'' ?>>Preventivo</option>
    </select>
    <button type="submit" class="btn-filter">Filtrar</button>
    <button type="button" class="btn-clear" onclick="window.location='actividades.php'">Limpiar</button>
</form>
</div>

<div class="glass-card">
<div class="table-responsive">
<table class="table align-middle">
<thead>
<tr>
<th>ID</th><th>Equipo</th><th>Tipo</th><th>Descripción</th><th>Responsable</th><th>Fecha Inicio</th><th>Fecha Fin</th><th>Documento</th><th>Estado</th><th>Acciones</th>
</tr>
</thead>
<tbody>
<?php while($row=$result->fetch_assoc()): ?>
<tr id="row-<?= $row['id_mantenimiento'] ?>">
<td><?= $row['id_mantenimiento'] ?></td>
<td><?= $row['id_equipo'] ?></td>
<td><?= $row['tipo'] ?></td>
<td><?= $row['descripcion'] ?></td>
<td><?= $row['responsable'] ?></td>
<td><?= $row['fecha_inicio'] ?></td>
<td><?= $row['fecha_fin'] ?></td>
<td>
<?php if($row['documento']): ?>
<a href="<?= $row['documento'] ?>" target="_blank" class="btn-doc">Ver</a>
<?php else: ?>Sin Documento<?php endif; ?>
</td>
<td id="estado-<?= $row['id_mantenimiento'] ?>"><?= $row['estado'] ?></td>
<td>
<button class="btn btn-edit" onclick="openModal(<?= $row['id_mantenimiento'] ?>,'<?= $row['tipo'] ?>','<?= htmlspecialchars($row['descripcion'],ENT_QUOTES) ?>','<?= htmlspecialchars($row['responsable'],ENT_QUOTES) ?>')">Editar</button>
<button class="btn btn-finalizar" onclick="finalizar(<?= $row['id_mantenimiento'] ?>)">Finalizar</button>
<button class="btn btn-delete" onclick="eliminar(<?= $row['id_mantenimiento'] ?>)">Eliminar</button>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>

<button class="btn-volver" onclick="window.location='panel.php'">← Volver</button>

<!-- MODAL EDITAR -->
<div id="modalEdit" class="modal">
<div class="modal-content">
<div class="modal-header">Editar Mantenimiento</div>
<input type="hidden" id="edit_id">
<label>Tipo</label>
<select id="edit_tipo">
<option value="Correctivo">Correctivo</option>
<option value="Preventivo">Preventivo</option>
</select>
<label>Descripción</label>
<input type="text" id="edit_desc" placeholder="Descripción">
<label>Responsable</label>
<input type="text" id="edit_resp" placeholder="Responsable">
<button class="btn" onclick="guardarEdicion()">Guardar Cambios</button>
<button class="btn btn-cancel" onclick="cerrarModal()">Cancelar</button>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openModal(id,tipo,desc,res){
    document.getElementById('edit_id').value=id;
    document.getElementById('edit_tipo').value=tipo;
    document.getElementById('edit_desc').value=desc;
    document.getElementById('edit_resp').value=res;
    document.getElementById('modalEdit').style.display='flex';
}
function cerrarModal(){document.getElementById('modalEdit').style.display='none';}

// EDITAR
function guardarEdicion(){
    const id=document.getElementById('edit_id').value;
    const tipo=document.getElementById('edit_tipo').value;
    const desc=document.getElementById('edit_desc').value;
    const resp=document.getElementById('edit_resp').value;

    fetch('acciones_mantenimiento.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`id_mantenimiento=${id}&accion=editar&tipo=${tipo}&descripcion=${desc}&responsable=${resp}`
    }).then(r=>r.json()).then(d=>{
        if(d.success){
            Swal.fire('¡Actualizado!','Mantenimiento editado.','success').then(()=>location.reload());
        }else{
            Swal.fire('Error',d.error,'error');
        }
    });
}

// FINALIZAR
function finalizar(id){
    Swal.fire({
        title:'¿Finalizar mantenimiento?',
        icon:'question',
        showCancelButton:true,
        confirmButtonText:'Sí, finalizar',
        cancelButtonText:'Cancelar'
    }).then((result)=>{
        if(result.isConfirmed){
            fetch('acciones_mantenimiento.php',{
                method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body:'id_mantenimiento='+id+'&accion=finalizar'
            }).then(r=>r.json()).then(d=>{
                if(d.success){
                    document.getElementById('estado-'+id).innerText='Completado';
                    Swal.fire('¡Finalizado!','Mantenimiento completado.','success');
                }else{
                    Swal.fire('Error',d.error,'error');
                }
            });
        }
    });
}

// ELIMINAR
function eliminar(id){
    Swal.fire({
        title:'¿Eliminar mantenimiento?',
        text:'Esta acción no se puede deshacer.',
        icon:'warning',
        showCancelButton:true,
        confirmButtonText:'Sí, eliminar',
        cancelButtonText:'Cancelar'
    }).then((result)=>{
        if(result.isConfirmed){
            fetch('acciones_mantenimiento.php',{
                method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body:'id_mantenimiento='+id+'&accion=eliminar'
            }).then(r=>r.json()).then(d=>{
                if(d.success){
                    document.getElementById('row-'+id).remove();
                    Swal.fire('Eliminado!','Mantenimiento eliminado.','success');
                }else{
                    Swal.fire('Error',d.error,'error');
                }
            });
        }
    });
}
</script>
</body>
</html>
