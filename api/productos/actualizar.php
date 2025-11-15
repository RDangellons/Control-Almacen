<?php
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

$id      = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$codigo  = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
$nombre  = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$color   = isset($_POST['color']) ? trim($_POST['color']) : '';
$talla   = isset($_POST['talla']) ? trim($_POST['talla']) : '';
$precio  = isset($_POST['precio_referencia']) ? trim($_POST['precio_referencia']) : '';

if ($id <= 0 || $codigo === '' || $nombre === '') {
    http_response_code(400);
    echo json_encode(["error" => "ID, código y nombre son obligatorios."]);
    exit;
}

try {
    // Verificar que el producto exista
    $stmt = $conn->prepare("SELECT id FROM productos WHERE id = :id AND activo = 1");
    $stmt->execute([':id' => $id]);
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        http_response_code(400);
        echo json_encode(["error" => "El producto no existe o está inactivo."]);
        exit;
    }

    // Verificar que no haya otro producto con el mismo código
    $stmt = $conn->prepare("SELECT id FROM productos WHERE codigo = :codigo AND id <> :id AND activo = 1");
    $stmt->execute([
        ':codigo' => $codigo,
        ':id'     => $id
    ]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        http_response_code(400);
        echo json_encode(["error" => "Ya existe otro producto con ese código."]);
        exit;
    }

    // Actualizar
    $stmt = $conn->prepare("
        UPDATE productos
        SET codigo = :codigo,
            nombre = :nombre,
            color = :color,
            talla = :talla,
            precio_referencia = :precio
        WHERE id = :id
    ");

    $stmt->execute([
        ':codigo' => $codigo,
        ':nombre' => $nombre,
        ':color'  => $color !== '' ? $color : null,
        ':talla'  => $talla !== '' ? $talla : null,
        ':precio' => $precio !== '' ? $precio : null,
        ':id'     => $id
    ]);

    echo json_encode([
        "ok" => true,
        "mensaje" => "Producto actualizado correctamente."
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error al actualizar el producto",
        "detalle" => $e->getMessage()
    ]);
}
