<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

// Proteger con sesión
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "No autenticado"]);
    exit;
}

// Filtros opcionales
$fecha_desde = isset($_GET['desde']) ? trim($_GET['desde']) : '';
$fecha_hasta = isset($_GET['hasta']) ? trim($_GET['hasta']) : '';
$estado      = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$usuario_id  = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : 0;

$whereParts = [];
$params     = [];

// Filtro por fecha (asumiendo formato YYYY-MM-DD)
if ($fecha_desde !== '') {
    $whereParts[] = "hp.fecha_movimiento >= :desde";
    $params[':desde'] = $fecha_desde . " 00:00:00";
}
if ($fecha_hasta !== '') {
    $whereParts[] = "hp.fecha_movimiento <= :hasta";
    $params[':hasta'] = $fecha_hasta . " 23:59:59";
}

// Filtro por estado nuevo
if ($estado !== '') {
    $whereParts[] = "hp.estado_nuevo = :estado";
    $params[':estado'] = $estado;
}

// Filtro por usuario
if ($usuario_id > 0) {
    $whereParts[] = "hp.usuario_id = :usuario_id";
    $params[':usuario_id'] = $usuario_id;
}

$where = '';
if (!empty($whereParts)) {
    $where = 'WHERE ' . implode(' AND ', $whereParts);
}

try {
    $sql = "
        SELECT
            hp.id,
            hp.fecha_movimiento,
            hp.estado_anterior,
            hp.estado_nuevo,
            u.nombre        AS usuario_nombre,
            op.id           AS orden_id,
            op.cantidad,
            op.referencia,
            p.codigo        AS modelo,
            p.nombre        AS producto_nombre,
            p.color
        FROM historial_produccion hp
        INNER JOIN ordenes_produccion op ON op.id = hp.orden_id
        INNER JOIN productos p           ON p.id = op.producto_id
        INNER JOIN usuarios u            ON u.id = hp.usuario_id
        $where
        ORDER BY hp.fecha_movimiento DESC, hp.id DESC
        LIMIT 500
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error"   => "Error al obtener reporte de movimientos de producción",
        "detalle" => $e->getMessage()
    ]);
}
