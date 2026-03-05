<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'brendamelgar');
define('DB_USER', 'brendamelgar');
define('DB_PASS', 'ichbinausGT01+');

define('ADMIN_USER', 'brenda');
define('ADMIN_PASS', '$2y$10$GQ.Q8M0Q68txyNZPd6Z7KO9kvADRjAJf5MZwuiBvlQ17N5qnOPK9.');

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
