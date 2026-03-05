<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metodo no permitido']);
    exit;
}

$libro = isset($_POST['libro']) ? substr($_POST['libro'], 0, 100) : '';
$email = isset($_POST['email']) ? substr($_POST['email'], 0, 255) : '';
$nombre = isset($_POST['nombre']) ? substr($_POST['nombre'], 0, 255) : '';
$ip = $_SERVER['REMOTE_ADDR'] ?? '';

try {
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO descargas (libro, email, nombre, ip, fecha) VALUES (?, ?, ?, ?, NOW())');
    $stmt->execute([$libro, $email, $nombre, $ip]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al registrar descarga']);
}
