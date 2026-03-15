<?php
session_start();
if(!isset($_SESSION['rol'])){
    echo json_encode(['success'=>false,'error'=>'No autorizado']);
    exit();
}
require_once "conexion.php";

$id = intval($_POST['id_mantenimiento'] ?? 0);
$accion = $_POST['accion'] ?? '';

if(!$id){ echo json_encode(['success'=>false,'error'=>'ID inválido']); exit(); }

if($accion=='editar'){
    $tipo = $conn->real_escape_string($_POST['tipo']);
    $desc = $conn->real_escape_string($_POST['descripcion']);
    $resp = $conn->real_escape_string($_POST['responsable']);
    if($conn->query("UPDATE mantenimientos SET tipo='$tipo', descripcion='$desc', responsable='$resp' WHERE id_mantenimiento=$id")){
        echo json_encode(['success'=>true]);
    }else{
        echo json_encode(['success'=>false,'error'=>$conn->error]);
    }
}
elseif($accion=='finalizar'){
    if($conn->query("UPDATE mantenimientos SET estado='Completado' WHERE id_mantenimiento=$id")){
        echo json_encode(['success'=>true]);
    }else{
        echo json_encode(['success'=>false,'error'=>$conn->error]);
    }
}
elseif($accion=='eliminar'){
    if($conn->query("DELETE FROM mantenimientos WHERE id_mantenimiento=$id")){
        echo json_encode(['success'=>true]);
    }else{
        echo json_encode(['success'=>false,'error'=>$conn->error]);
    }
}
else{
    echo json_encode(['success'=>false,'error'=>'Acción desconocida']);
}
?>
