<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

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

$id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$estadoNuevo = isset($_POST['estado']) ? trim($_POST['estado']) : '';

if ($id <= 0 || $estadoNuevo === '') {
    http_response_code(400);
    echo json_encode(["error" => "ID y estado son obligatorios."]);
    exit;
}

// 👇 Estados válidos, incluyendo enviado_confeccion
$validos = [
    'tejido',
    'enviado_confeccion',
    'confeccion',
    'revisado',
    'embolsado',
    'bodega',
    'terminada',
    'cancelada'
];

if (!in_array($estadoNuevo, $validos, true)) {
    http_response_code(400);
    echo json_encode(["error" => "Estado no válido."]);
    exit;
}

$usuario_id = (int)$_SESSION['user_id'];

try {
    $conn->beginTransaction();

    // Obtener estado actual de la orden
    $stmt = $conn->prepare("
        SELECT estado
        FROM ordenes_produccion
        WHERE id = :id
        FOR UPDATE
    ");
    $stmt->execute([':id' => $id]);
    $orden = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$orden) {
        $conn->rollBack();
        http_response_code(404);
        echo json_encode(["error" => "Orden no encontrada."]);
        exit;
    }

    $estadoAnterior = $orden['estado'];

    // Actualizar estado de la orden
    $stmt = $conn->prepare("
        UPDATE ordenes_produccion
        SET estado = :estado, fecha_actualizacion = NOW()
        WHERE id = :id
    ");
    $stmt->execute([
        ':estado' => $estadoNuevo,
        ':id'     => $id
    ]);

    // Registrar en historial_produccion
    $stmt = $conn->prepare("
        INSERT INTO historial_produccion
            (orden_id, usuario_id, estado_anterior, estado_nuevo)
        VALUES
            (:orden_id, :usuario_id, :estado_anterior, :estado_nuevo)
    ");
    $stmt->execute([
        ':orden_id'        => $id,
        ':usuario_id'      => $usuario_id,
        ':estado_anterior' => $estadoAnterior,
        ':estado_nuevo'    => $estadoNuevo
    ]);

    $conn->commit();

    echo json_encode([
        "ok"      => true,
        "mensaje" => "Estado actualizado correctamente."
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        "error"   => "Error al cambiar el estado",
        "detalle" => $e->getMessage()
    ]);
}
