<?php
require_once __DIR__ . '/../../config/db.php';

try {
    $stmt = $conn->prepare("
        SELECT 
            p.id,
            p.codigo,
            p.nombre,
            p.color,
            p.talla,
            p.precio_referencia,
            IFNULL(e.cantidad, 0) AS existencias
        FROM productos p
        LEFT JOIN existencias e ON e.producto_id = p.id
        WHERE p.activo = 1
        ORDER BY
  CASE 
    WHEN p.codigo REGEXP '^[0-9]+$' THEN CAST(p.codigo AS UNSIGNED)
    ELSE 9999999
  END,
  p.codigo ASC;
    ");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($productos);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error al obtener productos",
        "detalle" => $e->getMessage()
    ]);
}
