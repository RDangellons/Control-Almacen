<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

// Verificar que haya usuario en sesión
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "No autenticado"]);
    exit;
}

$producto_id = isset($_POST['producto_id']) ? (int)$_POST['producto_id'] : 0;
$tipo        = isset($_POST['tipo']) ? $_POST['tipo'] : '';
$cantidad    = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 0;
$motivo      = isset($_POST['motivo']) ? trim($_POST['motivo']) : '';
$referencia  = isset($_POST['referencia']) ? trim($_POST['referencia']) : '';

$usuario_id  = (int)$_SESSION['user_id'];   // 👈 AQUÍ el usuario real

// Validaciones básicas
if ($producto_id <= 0 || $cantidad <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Producto y cantidad son obligatorios y deben ser válidos."]);
    exit;
}

if (!in_array($tipo, ['entrada', 'salida', 'ajuste'])) {
    http_response_code(400);
    echo json_encode(["error" => "Tipo de movimiento no válido."]);
    exit;
}

try {
    $conn->beginTransaction();

    // Verificar que el producto exista y esté activo
    $stmt = $conn->prepare("SELECT id FROM productos WHERE id = :id AND activo = 1");
    $stmt->execute([':id' => $producto_id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        $conn->rollBack();
        http_response_code(400);
        echo json_encode(["error" => "El producto no existe o está inactivo."]);
        exit;
    }

    // Obtener existencias actuales
    $stmt = $conn->prepare("SELECT id, cantidad FROM existencias WHERE producto_id = :pid");
    $stmt->execute([':pid' => $producto_id]);
    $exist = $stmt->fetch(PDO::FETCH_ASSOC);

    $existencia_actual = $exist ? (int)$exist['cantidad'] : 0;
    $nueva_existencia  = $existencia_actual;

    if ($tipo === 'entrada') {
        $nueva_existencia = $existencia_actual + $cantidad;
    } elseif ($tipo === 'salida') {
        if ($cantidad > $existencia_actual) {
            $conn->rollBack();
            http_response_code(400);
            echo json_encode([
                "error" => "No hay existencias suficientes. Actual: $existencia_actual, intentas sacar: $cantidad."
            ]);
            exit;
        }
        $nueva_existencia = $existencia_actual - $cantidad;
    } elseif ($tipo === 'ajuste') {
        // Aquí puedes ajustar la lógica según cómo uses "ajuste"
        $nueva_existencia = $existencia_actual + $cantidad;
        if ($nueva_existencia < 0) {
            $conn->rollBack();
            http_response_code(400);
            echo json_encode(["error" => "El ajuste no puede dejar existencias negativas."]);
            exit;
        }
    }

    // Insertar / actualizar existencias
    if ($exist) {
        $stmt = $conn->prepare("UPDATE existencias SET cantidad = :cant WHERE id = :id");
        $stmt->execute([
            ':cant' => $nueva_existencia,
            ':id'   => $exist['id']
        ]);
    } else {
        $stmt = $conn->prepare("INSERT INTO existencias (producto_id, cantidad) VALUES (:pid, :cant)");
        $stmt->execute([
            ':pid'  => $producto_id,
            ':cant' => $nueva_existencia
        ]);
    }

    // Registrar el movimiento con usuario_id 👇
    // Fecha real de México
$fecha = date('Y-m-d H:i:s');

$stmt = $conn->prepare("
    INSERT INTO movimientos (producto_id, tipo, cantidad, motivo, usuario_id, referencia, fecha)
    VALUES (:pid, :tipo, :cant, :motivo, :uid, :ref, :fecha)
");
$stmt->execute([
    ':pid'   => $producto_id,
    ':tipo'  => $tipo,
    ':cant'  => $cantidad,
    ':motivo'=> $motivo,
    ':uid'   => $usuario_id,
    ':ref'   => $referencia,
    ':fecha' => $fecha
]);

    $conn->commit();

    echo json_encode([
        "ok" => true,
        "mensaje" => "Movimiento registrado correctamente.",
        "existencia_actualizada" => $nueva_existencia
    ]);

} catch (PDOException $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode([
        "error" => "Error al registrar el movimiento",
        "detalle" => $e->getMessage()
    ]);
}
