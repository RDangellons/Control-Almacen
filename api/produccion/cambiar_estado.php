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

$id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$estado = isset($_POST['estado']) ? trim($_POST['estado']) : '';

$validos = ['tejido','confeccion','revisado','bodega','terminada','cancelada'];

if ($id <= 0 || !in_array($estado, $validos, true)) {
    http_response_code(400);
    echo json_encode(["error" => "Datos inválidos."]);
    exit;
}

$usuario_id = (int)$_SESSION['user_id'];

try {
    // Obtener estado anterior de la orden
    $stmt = $conn->prepare("SELECT estado FROM ordenes_produccion WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $orden = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$orden) {
        http_response_code(404);
        echo json_encode(["error" => "La orden de producción no existe."]);
        exit;
    }

    $estado_anterior = $orden['estado'];

    // Actualizar estado de la orden
    $stmt = $conn->prepare("
        UPDATE ordenes_produccion
        SET estado = :estado,
            fecha_actualizacion = NOW()
        WHERE id = :id
    ");
    $stmt->execute([
        ':estado' => $estado,
        ':id'     => $id
    ]);

    // Registrar movimiento en historial_produccion
  $stmt = $conn->prepare("
    INSERT INTO historial_produccion
        (orden_id, usuario_id, estado_anterior, estado_nuevo)
    VALUES
        (:orden_id, :usuario_id, :estado_anterior, :estado_nuevo)
");
$stmt->execute([
    ':orden_id'        => $id,
    ':usuario_id'      => $usuario_id,
    ':estado_anterior' => $estado_anterior,
    ':estado_nuevo'    => $estado
]);



    echo json_encode(["ok" => true, "mensaje" => "Estado actualizado y movimiento registrado."]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error"   => "Error al actualizar estado",
        "detalle" => $e->getMessage()
    ]);
}
