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
            op.producto_id,
            op.cantidad,
            op.estado,
            op.referencia,
            op.fecha_creacion,
            p.codigo,
            p.nombre,
            p.color,
            p.talla
        FROM ordenes_produccion op
        INNER JOIN productos p ON p.id = op.producto_id
        WHERE op.estado NOT IN ('terminada','cancelada')
        ORDER BY op.fecha_creacion DESC, op.id DESC
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
