<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "No autenticado"]);
    exit;
}

try {
    $sql = "
        SELECT
            op.id,
            op.cantidad,
            op.referencia,
            op.estado,  -- 👈 MUY IMPORTANTE
            DATE_FORMAT(
                DATE_SUB(op.fecha_creacion, INTERVAL 6 HOUR),
                '%Y-%m-%d %H:%i:%s'
            ) AS fecha_creacion,
            p.codigo AS codigo,
            p.codigo AS modelo,
            p.nombre AS nombre,
            p.color  AS color
        FROM ordenes_produccion op
        INNER JOIN productos p ON p.id = op.producto_id
        WHERE op.estado NOT IN ('terminada','cancelada')
        ORDER BY op.fecha_creacion DESC
    ";

    $stmt = $conn->query($sql);
    $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($ordenes);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error"   => "Error al obtener órdenes de producción",
        "detalle" => $e->getMessage()
    ]);
}
