<?php
/**
 * Generar hash de contraseña
 * Uso: php generate_password_hash.php
 */

$password = 'cumples*';

echo "=== Generador de Hash de Contraseña ===\n\n";
echo "Contraseña: $password\n";
echo "Hash: " . password_hash($password, PASSWORD_DEFAULT) . "\n\n";

// Generar varios hashes (cada uno es diferente por el salt aleatorio)
echo "Otros hashes válidos (cualquiera funciona):\n";
for ($i = 1; $i <= 3; $i++) {
    echo "$i. " . password_hash($password, PASSWORD_DEFAULT) . "\n";
}

echo "\n=== Query SQL para actualizar usuario admin ===\n\n";
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "UPDATE usuarios SET password = '$hash' WHERE email = 'admin@birthdays.com';\n";
