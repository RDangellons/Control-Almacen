<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "No autenticado"]);
    exit;
}

$producto_id = isset($_POST['producto_id']) ? (int)$_POST['producto_id'] : 0;
$cantidad    = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 0;
$referencia  = isset($_POST['referencia']) ? trim($_POST['referencia']) : '';

$usuario_id  = (int)$_SESSION['user_id'];

if ($producto_id <= 0 || $cantidad <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Producto y cantidad son obligatorios."]);
    exit;
}

try {
    // 👇 AQUÍ FORZAMOS ESTADO = 'tejido'
    $stmt = $conn->prepare("
        INSERT INTO ordenes_produccion
            (producto_id, cantidad, estado, referencia, usuario_id, fecha_creacion)
        VALUES
            (:pid, :cant, 'tejido', :ref, :uid, NOW())
    ");
    $stmt->execute([
        ':pid'  => $producto_id,
        ':cant' => $cantidad,
        ':ref'  => $referencia,
        ':uid'  => $usuario_id
    ]);

    $ordenId = $conn->lastInsertId();

    // Registrar en historial_produccion
    $stmt = $conn->prepare("
        INSERT INTO historial_produccion
            (orden_id, usuario_id, estado_anterior, estado_nuevo)
        VALUES
            (:orden_id, :usuario_id, NULL, 'tejido')
    ");
    $stmt->execute([
        ':orden_id'   => $ordenId,
        ':usuario_id' => $usuario_id
    ]);

    echo json_encode([
        "ok"      => true,
        "mensaje" => "Orden creada correctamente."
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error"   => "Error al crear orden de producción",
        "detalle" => $e->getMessage()
    ]);
}
