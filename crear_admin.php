<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/db.php';

$usuario  = 'admin';
$password = 'admin123'; // cámbiala si quieres
$nombre   = 'Administrador';

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
        echo "Usuario admin actualizado. Usuario: $usuario, Contraseña: $password";
    } else {
        // Crear nuevo admin
        $stmt = $conn->prepare("
            INSERT INTO usuarios (nombre, usuario, password, rol)
            VALUES (:nom, :usuario, :pass, 'admin')
        ");
        $stmt->execute([
            ':nom'     => $nombre,
            ':usuario' => $usuario,
            ':pass'    => $hash
        ]);
        echo "Usuario admin creado. Usuario: $usuario, Contraseña: $password";
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
