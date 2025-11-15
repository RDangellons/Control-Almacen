<?php
require_once __DIR__ . '/../../config/db.php';

// Parámetro opcional para buscar por texto (código/nombre)
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

$where = '';
$params = [];

if ($busqueda !== '') {
    $where = "WHERE (p.codigo LIKE :busqueda OR p.nombre LIKE :busqueda)";
    $params[':busqueda'] = '%' . $busqueda . '%';
}

try {
    $sql = "
        SELECT 
            p.id,
            p.codigo,
            p.nombre,
            p.color,
            p.talla,
            IFNULL(e.cantidad, 0) AS existencias
        FROM productos p
        LEFT JOIN existencias e ON e.producto_id = p.id
        $where
        ORDER BY p.nombre ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($productos);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error al obtener reporte de existencias",
        "detalle" => $e->getMessage()
    ]);
}
