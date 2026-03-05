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
$telefono = isset($_POST['telefono']) ? substr($_POST['telefono'], 0, 50) : '';
$pais = isset($_POST['pais']) ? substr($_POST['pais'], 0, 100) : '';
$fecha_evento = isset($_POST['fecha']) ? substr($_POST['fecha'], 0, 10) : '';
$asistentes = isset($_POST['asistentes']) ? intval($_POST['asistentes']) : 0;
$ip = $_SERVER['REMOTE_ADDR'] ?? '';

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email invalido']);
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO speaking (nombre, email, telefono, pais, fecha_evento, asistentes, ip, fecha) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
    $stmt->execute([$nombre, $email, $telefono, $pais, $fecha_evento, $asistentes, $ip]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al registrar solicitud']);
}
