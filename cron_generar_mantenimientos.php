<?php
require_once "conexion.php";

// BUSCAR TODOS LOS EQUIPOS CON INTERVALO AUTOMÁTICO
$sql = "
    SELECT 
        e.id_equipo,
        e.nombre_equipo,
        e.categoria,
        COALESCE(e.ultimo_mantenimiento, e.fecha_registro) AS fecha_base,
        c.intervalo_meses
    FROM equipos e
    INNER JOIN categoria_intervalos c ON e.categoria = c.categoria
";

$equipos = $conn->query($sql);

$generados = 0;

while ($eq = $equipos->fetch_assoc()) {

    $fecha_base     = $eq['fecha_base'];
    $intervalo      = $eq['intervalo_meses'];
    $fecha_siguiente = date("Y-m-d", strtotime("+$intervalo months", strtotime($fecha_base)));

    // SI YA SE VENCIO LA FECHA
    if ($fecha_siguiente <= date("Y-m-d")) {

        // 1) Verificar que no exista uno EN PROCESO
        $check = $conn->prepare("
            SELECT id_mantenimiento 
            FROM mantenimientos 
            WHERE id_equipo = ? 
              AND estado = 'En Proceso'
            LIMIT 1
        ");
        $check->bind_param("i", $eq['id_equipo']);
        $check->execute();
        $exists = $check->get_result();

        if ($exists->num_rows === 0) {

            // 2) Crear mantenimiento preventivo automático
            $stmt = $conn->prepare("
                INSERT INTO mantenimientos 
                    (id_equipo, tipo, descripcion, responsable, fecha_inicio, estado)
                VALUES (?, 'Preventivo', 'Mantenimiento automático programado', 'Sistema', CURDATE(), 'En Proceso')
            ");
            $stmt->bind_param("i", $eq['id_equipo']);
            $stmt->execute();

            // 3) Actualizar ultimo mantenimiento
            $update = $conn->prepare("UPDATE equipos SET ultimo_mantenimiento = CURDATE() WHERE id_equipo = ?");
            $update->bind_param("i", $eq['id_equipo']);
            $update->execute();

            $generados++;
        }
    }
}

echo "Mantenimientos automáticos generados: $generados";
