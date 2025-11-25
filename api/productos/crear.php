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
    echo json_encode([
        "error" => "El código y el nombre del producto son obligatorios."
    ]);
    exit;
}

// Si quieres, puedes obligar a que lleve color:
if ($color === '') {
    http_response_code(400);
    echo json_encode([
        "error" => "Debes indicar un color para el producto."
    ]);
    exit;
}

// Normalizar precio
if ($precio === '') {
    $precio = null;
} else {
    $precio = (float)$precio;
}

try {

    // 🔴 AQUÍ ESTABA EL BLOQUEO ANTES:
    // antes seguramente se hacía algo como:
    // SELECT id FROM productos WHERE codigo = :codigo
    // Eso impedía repetir código aunque fuera otro color.
    //
    // ✅ NUEVA LÓGICA:
    // solo bloqueamos si YA existe el mismo CODIGO + COLOR.

    $sqlDuplicado = "
        SELECT id 
        FROM productos 
        WHERE codigo = :codigo
          AND color  = :color
        LIMIT 1
    ";
    $stmt = $conn->prepare($sqlDuplicado);
    $stmt->execute([
        ':codigo' => $codigo,
        ':color'  => $color
    ]);
    $existe = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existe) {
        http_response_code(409);
        echo json_encode([
            "error" => "Ya existe un producto con ese código y color.",
            "detalle" => "Código: {$codigo}, Color: {$color}"
        ]);
        exit;
    }

    // Insertar nuevo producto
    $sqlInsert = "
        INSERT INTO productos (codigo, nombre, color, talla, precio_referencia, activo)
        VALUES (:codigo, :nombre, :color, :talla, :precio, 1)
    ";
    $stmt = $conn->prepare($sqlInsert);
    $stmt->execute([
        ':codigo' => $codigo,
        ':nombre' => $nombre,
        ':color'  => $color,
        ':talla'  => $talla,
        ':precio' => $precio
    ]);

    $nuevoId = $conn->lastInsertId();

    echo json_encode([
        "ok"      => true,
        "mensaje" => "Producto creado correctamente.",
        "id"      => $nuevoId
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error"   => "Error al crear el producto",
        "detalle" => $e->getMessage()
    ]);
}
