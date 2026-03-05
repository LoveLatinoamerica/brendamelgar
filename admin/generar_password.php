<?php
// Ejecutar una vez para generar el hash de tu contraseña
// Luego copiar el resultado en config/db.php y BORRAR este archivo

$password = 'brenda2026'; // Cambia esto por tu contraseña deseada
echo password_hash($password, PASSWORD_DEFAULT);
