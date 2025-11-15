<?php
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

$codigo  = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
$nombre  = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$color   = isset($_POST['color']) ? trim($_POST['color']) : '';
$talla   = isset($_POST['talla']) ? trim($_POST['talla']) : '';
$precio  = isset($_POST['precio_referencia']) ? trim($_POST['precio_referencia']) : '';

if ($codigo === '' || $nombre === '') {
    http_response_code(400);
    echo json_encode(["error" => "Código y nombre son obligatorios."]);
    exit;
}

try {
    // Verificar que no exista un producto con el mismo código
    $stmt = $conn->prepare("SELECT id FROM productos WHERE codigo = :codigo AND activo = 1");
    $stmt->execute([':codigo' => $codigo]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        http_response_code(400);
        echo json_encode(["error" => "Ya existe un producto activo con ese código."]);
        exit;
    }

    // Insertar producto
    $stmt = $conn->prepare("
        INSERT INTO productos (codigo, nombre, color, talla, precio_referencia, activo)
        VALUES (:codigo, :nombre, :color, :talla, :precio, 1)
    ");

    $stmt->execute([
        ':codigo' => $codigo,
        ':nombre' => $nombre,
        ':color'  => $color !== '' ? $color : null,
        ':talla'  => $talla !== '' ? $talla : null,
        ':precio' => $precio !== '' ? $precio : null,
    ]);

    $nuevoId = $conn->lastInsertId();

    // Crear registro de existencias en 0
    $stmt = $conn->prepare("INSERT INTO existencias (producto_id, cantidad) VALUES (:pid, 0)");
    $stmt->execute([':pid' => $nuevoId]);

    echo json_encode([
        "ok" => true,
        "mensaje" => "Producto creado correctamente.",
        "id" => $nuevoId
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error al crear el producto",
        "detalle" => $e->getMessage()
    ]);
}
