<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "No autenticado"]);
    exit;
}

// Datos recibidos
$producto_id = isset($_POST['producto_id']) ? (int)$_POST['producto_id'] : 0;
$cantidad    = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 0;
$referencia  = isset($_POST['referencia']) ? trim($_POST['referencia']) : '';

if ($producto_id <= 0 || $cantidad <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Producto y cantidad son obligatorios"]);
    exit;
}

$usuario_id = (int)$_SESSION['user_id'];

try {

    // Iniciar transacción
    $conn->beginTransaction();

    // Verificar producto
    $stmt = $conn->prepare("SELECT id FROM productos WHERE id = :id AND activo = 1");
    $stmt->execute([':id' => $producto_id]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$prod) {
        $conn->rollBack();
        http_response_code(400);
        echo json_encode(["error" => "El producto no existe o está inactivo"]);
        exit;
    }

    // Crear orden de producción
    $stmt = $conn->prepare("
        INSERT INTO ordenes_produccion (producto_id, cantidad, estado, referencia, usuario_id)
        VALUES (:pid, :cant, 'tejido', :ref, :uid)
    ");
    $stmt->execute([
        ':pid'  => $producto_id,
        ':cant' => $cantidad,
        ':ref'  => $referencia,
        ':uid'  => $usuario_id
    ]);

    $orden_id = $conn->lastInsertId();

    // Registrar historial
    $stmt = $conn->prepare("
        INSERT INTO historial_produccion (orden_id, usuario_id, estado_anterior, estado_nuevo)
        VALUES (:oid, :uid, NULL, 'tejido')
    ");
    $stmt->execute([
        ':oid' => $orden_id,
        ':uid' => $usuario_id
    ]);

    // Confirmar
    $conn->commit();

    echo json_encode([
        "ok" => true,
        "mensaje" => "Orden de producción creada correctamente"
    ]);

} catch (Exception $e) {

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        "error" => "Error en el servidor",
        "detalle" => $e->getMessage()
    ]);
}
