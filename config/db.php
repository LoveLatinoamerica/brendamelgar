<?php
// Load .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? '');
define('DB_USER', $_ENV['DB_USER'] ?? '');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

define('ADMIN_USER', $_ENV['ADMIN_USER'] ?? '');
define('ADMIN_PASS', $_ENV['ADMIN_PASS'] ?? '');

// SMTP
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'mail.inteex.com');
define('SMTP_PORT', intval($_ENV['SMTP_PORT'] ?? 25));
define('SMTP_USER', $_ENV['SMTP_USER'] ?? '');
define('SMTP_PASS', $_ENV['SMTP_PASS'] ?? '');
define('SMTP_FROM', $_ENV['SMTP_FROM'] ?? 'notificaciones@inteex.com');
define('NOTIFY_TO', $_ENV['NOTIFY_TO'] ?? 'robertomelgar@gmail.com');

function sendMail($to, $subject, $body, $replyTo = '') {
    $smtp = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 10);
    if (!$smtp) return false;

    $read = function() use ($smtp) {
        $resp = '';
        while ($line = fgets($smtp, 512)) {
            $resp .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $resp;
    };

    $write = function($cmd) use ($smtp, $read) {
        fwrite($smtp, $cmd . "\r\n");
        return $read();
    };

    $read(); // banner
    $write('EHLO ' . gethostname());

    // STARTTLS
    $write('STARTTLS');
    stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT);
    $write('EHLO ' . gethostname());

    // AUTH LOGIN
    if (SMTP_USER && SMTP_PASS) {
        $write('AUTH LOGIN');
        $write(base64_encode(SMTP_USER));
        $resp = $write(base64_encode(SMTP_PASS));
        if (strpos($resp, '235') === false) {
            fclose($smtp);
            return false;
        }
    }

    $write('MAIL FROM:<' . SMTP_FROM . '>');
    $write('RCPT TO:<' . $to . '>');
    $write('DATA');

    $headers  = "From: " . SMTP_FROM . "\r\n";
    $headers .= "To: $to\r\n";
    $headers .= "Subject: $subject\r\n";
    if ($replyTo) $headers .= "Reply-To: $replyTo\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Date: " . date('r') . "\r\n";

    fwrite($smtp, $headers . "\r\n" . $body . "\r\n.\r\n");
    $resp = $read();
    $write('QUIT');
    fclose($smtp);

    return strpos($resp, '250') === 0;
}

function getClientIP() {
    $headers = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP'];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = trim(explode(',', $_SERVER[$header])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    return $pdo;
}
