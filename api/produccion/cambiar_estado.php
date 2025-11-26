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

try {
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

    echo json_encode(["ok" => true, "mensaje" => "Estado actualizado."]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error"   => "Error al actualizar estado",
        "detalle" => $e->getMessage()
    ]);
}
