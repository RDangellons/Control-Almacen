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

$modelo = isset($_GET['modelo']) ? trim($_GET['modelo']) : '';

try {
    $where  = '';
    $params = [];

    if ($modelo !== '') {
        $where = "WHERE p.codigo = :modelo";
        $params[':modelo'] = $modelo;
    }

    // Tomamos existencias y EN TRÁNSITO desde ordenes_produccion
    $sql = "
        SELECT
            p.id            AS producto_id,
            p.codigo        AS modelo,
            p.nombre        AS producto_nombre,
            p.color         AS color,
            COALESCE(e.cantidad, 0)        AS existencias,
            COALESCE(op.en_transito, 0)    AS en_transito
        FROM productos p
        LEFT JOIN existencias e 
            ON e.producto_id = p.id
        LEFT JOIN (
            SELECT 
                producto_id,
                SUM(cantidad) AS en_transito
            FROM ordenes_produccion
            WHERE estado IN ('tejido','confeccion','revisado','bodega')
            GROUP BY producto_id
        ) op ON op.producto_id = p.id
        $where
        ORDER BY p.codigo, p.color
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error"   => "Error al obtener existencias por color",
        "detalle" => $e->getMessage()
    ]);
}
