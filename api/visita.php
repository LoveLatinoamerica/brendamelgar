<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metodo no permitido']);
    exit;
}

$pagina = isset($_POST['pagina']) ? substr($_POST['pagina'], 0, 255) : '/';
$ip = getClientIP();
$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : '';
$referrer = isset($_SERVER['HTTP_REFERER']) ? substr($_SERVER['HTTP_REFERER'], 0, 500) : '';

try {
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO visitas (pagina, ip, user_agent, referrer, fecha) VALUES (?, ?, ?, ?, NOW())');
    $stmt->execute([$pagina, $ip, $user_agent, $referrer]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al registrar visita']);
}
