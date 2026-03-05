<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metodo no permitido']);
    exit;
}

$nombre = isset($_POST['nombre']) ? substr($_POST['nombre'], 0, 255) : '';
$email = isset($_POST['email']) ? substr($_POST['email'], 0, 255) : '';
$servicio = isset($_POST['servicio']) ? substr($_POST['servicio'], 0, 100) : '';
$mensaje = isset($_POST['mensaje']) ? substr($_POST['mensaje'], 0, 5000) : '';
$ip = $_SERVER['REMOTE_ADDR'] ?? '';

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email invalido']);
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO contacto (nombre, email, servicio, mensaje, ip, fecha) VALUES (?, ?, ?, ?, ?, NOW())');
    $stmt->execute([$nombre, $email, $servicio, $mensaje, $ip]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al registrar mensaje']);
}
