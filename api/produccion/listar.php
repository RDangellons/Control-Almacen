<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

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
        op.estado,
        DATE_FORMAT(
            DATE_SUB(op.fecha_creacion, INTERVAL 6 HOUR),
            '%Y-%m-%d %H:%i:%s'
        ) AS fecha_creacion,
        p.codigo AS codigo,
        p.codigo AS modelo,  -- Si tu frontend usa 'modelo', lo mantenemos también
        p.nombre AS nombre,
        p.color AS color
    FROM ordenes_produccion op
    INNER JOIN productos p ON p.id = op.producto_id
    WHERE op.estado != 'terminada'
    ORDER BY op.fecha_creacion DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error"   => "Error al listar órdenes de producción",
        "detalle" => $e->getMessage()
    ]);
}
