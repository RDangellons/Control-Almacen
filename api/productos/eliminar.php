<?php
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "ID de producto no válido."]);
    exit;
}

try {
    // Verificar que exista
    $stmt = $conn->prepare("SELECT id FROM productos WHERE id = :id AND activo = 1");
    $stmt->execute([':id' => $id]);
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        http_response_code(400);
        echo json_encode(["error" => "El producto no existe o ya está inactivo."]);
        exit;
    }

    // Marcar como inactivo
    $stmt = $conn->prepare("UPDATE productos SET activo = 0 WHERE id = :id");
    $stmt->execute([':id' => $id]);

    echo json_encode([
        "ok" => true,
        "mensaje" => "Producto eliminado (baja lógica) correctamente."
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error al eliminar el producto",
        "detalle" => $e->getMessage()
    ]);
}
