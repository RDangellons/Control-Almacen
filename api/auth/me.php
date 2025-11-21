<?php
// SUBIMOS DOS NIVELES: de /api/auth a / (raíz del proyecto) y luego entramos a /config
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json; charset=utf-8');

// Si no hay sesión → retornar JSON válido
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "No autenticado"]);
    exit;
}

// Responder JSON limpio
echo json_encode([
    "ok" => true,
    "usuario" => [
        "id"     => $_SESSION['user_id'],
        "nombre" => $_SESSION['user_name'] ?? '',
        "rol"    => $_SESSION['user_rol'] ?? ''
    ]
]);
