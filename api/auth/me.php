<?php
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "No autenticado"]);
    exit;
}

echo json_encode([
    "ok" => true,
    "usuario" => [
        "id" => $_SESSION['user_id'],
        "nombre" => $_SESSION['user_name'] ?? '',
        "rol" => $_SESSION['user_rol'] ?? ''
    ]
]);
