<?php
// MIRA BIEN ESTAS RUTAS 👇 (son la clave)
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';

// Opcional mientras depuras:
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Solo aceptamos POST
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

$usuario  = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($usuario === '' || $password === '') {
    http_response_code(400);
    echo json_encode(["error" => "Usuario y contraseña son obligatorios."]);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT id, nombre, usuario, password, rol 
        FROM usuarios 
        WHERE usuario = :usuario
    ");
    $stmt->execute([':usuario' => $usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(401);
        echo json_encode(["error" => "Usuario  incorrecto."]);
        exit;
    }

   if ($password !== $user['password']) {
    http_response_code(401);
    echo json_encode(["error" => "Contraseña incorrecta"]);
    exit;
}
    

    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['nombre'];
    $_SESSION['user_rol']  = $user['rol'];

    echo json_encode([
        "ok"      => true,
        "mensaje" => "Login correcto.",
        "usuario" => [
            "id"     => $user['id'],
            "nombre" => $user['nombre'],
            "rol"    => $user['rol']
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error"   => "Error al iniciar sesión",
        "detalle" => $e->getMessage()
    ]);
}
