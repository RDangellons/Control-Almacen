<?php
// Ajusta estos require según los nombres de tus archivos reales
require_once __DIR__ . '/api/config/session.php';
require_once __DIR__ . '/api/config/db.php'; // si se llama database.php, cámbialo aquí

$usuario  = 'admin';
$password = 'admin123'; // puedes cambiarla
$nombre   = 'Administrador';

// Generar hash de la contraseña
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Ver si ya existe el usuario 'admin'
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE usuario = :usuario");
    $stmt->execute([':usuario' => $usuario]);
    $existe = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existe) {
        // Actualizar contraseña y nombre
        $stmt = $conn->prepare("
            UPDATE usuarios 
            SET password = :pass, nombre = :nom, rol = 'admin'
            WHERE id = :id
        ");
        $stmt->execute([
            ':pass' => $hash,
            ':nom'  => $nombre,
            ':id'   => $existe['id']
        ]);
        echo "Usuario admin actualizado.<br>Usuario: {$usuario}<br>Contraseña: {$password}";
    } else {
        // Crear nuevo usuario admin
        $stmt = $conn->prepare("
            INSERT INTO usuarios (nombre, usuario, password, rol)
            VALUES (:nom, :usuario, :pass, 'admin')
        ");
        $stmt->execute([
            ':nom'     => $nombre,
            ':usuario' => $usuario,
            ':pass'    => $hash
        ]);
        echo "Usuario admin creado.<br>Usuario: {$usuario}<br>Contraseña: {$password}";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
