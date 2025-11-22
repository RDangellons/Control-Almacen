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
            op.producto_id,
            op.cantidad_total,
            op.cantidad_terminada,
            (op.cantidad_total - op.cantidad_terminada) AS cantidad_pendiente,
            op.estado,
            op.fecha_inicio,
            op.fecha_entrega_estimada,
            op.responsable,
            op.observaciones,
            p.codigo AS producto_codigo,
            p.nombre AS producto_nombre,
            p.color,
            p.talla
        FROM ordenes_produccion op
        INNER JOIN productos p ON p.id = op.producto_id
        WHERE 
            op.estado = 'en_transito'
            OR op.cantidad_terminada < op.cantidad_total
        ORDER BY op.fecha_inicio DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($ordenes);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error al obtener órdenes de producción en tránsito",
        "detalle" => $e->getMessage()
    ]);
}
