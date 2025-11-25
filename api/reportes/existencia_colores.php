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

    $sql = "
        SELECT
            p.id            AS producto_id,
            p.codigo        AS modelo,
            p.nombre        AS producto_nombre,
            p.color         AS color,
            COALESCE(e.cantidad, 0) AS existencias
        FROM productos p
        LEFT JOIN existencias e ON e.producto_id = p.id
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
