<?php
require_once __DIR__ . '/../../config/db.php';

// Parámetros opcionales
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$fecha_fin    = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
$producto_id  = isset($_GET['producto_id']) ? (int)$_GET['producto_id'] : null;
$tipo         = isset($_GET['tipo']) ? $_GET['tipo'] : null; // entrada/salida/ajuste

// Armamos el WHERE dinámico
$where   = [];
$params  = [];

if ($fecha_inicio) {
    $where[] = "m.fecha >= :fecha_inicio";
    $params[':fecha_inicio'] = $fecha_inicio . " 00:00:00";
}

if ($fecha_fin) {
    $where[] = "m.fecha <= :fecha_fin";
    $params[':fecha_fin'] = $fecha_fin . " 23:59:59";
}

if ($producto_id) {
    $where[] = "m.producto_id = :producto_id";
    $params[':producto_id'] = $producto_id;
}

if ($tipo && in_array($tipo, ['entrada','salida','ajuste'])) {
    $where[] = "m.tipo = :tipo";
    $params[':tipo'] = $tipo;
}

$where_sql = '';
if (count($where) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where);
}

try {
    $sql = "
        SELECT 
            m.id,
            m.fecha,
            m.tipo,
            m.cantidad,
            m.motivo,
            m.referencia,
            p.codigo AS producto_codigo,
            p.nombre AS producto_nombre,
            p.color,
            p.talla,
            u.nombre AS usuario_nombre
        FROM movimientos m
        INNER JOIN productos p ON p.id = m.producto_id
        LEFT JOIN usuarios u ON u.id = m.usuario_id
        $where_sql
        ORDER BY m.fecha DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($movimientos);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error al obtener reporte de movimientos",
        "detalle" => $e->getMessage()
    ]);
}
