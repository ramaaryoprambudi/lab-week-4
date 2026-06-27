<?php
header('Content-Type: application/json');
echo json_encode([
    'debug' => true,
    '_note' => '⚠️ Debug endpoint aktif di production (SIMULASI PHP)',
    'server' => [
        'php_version' => PHP_VERSION,
        'os' => PHP_OS,
        'sapi' => PHP_SAPI,
        'loaded_extensions' => get_loaded_extensions()
    ],
    'env' => [
        'NODE_ENV' => 'production',
        'APP_VERSION' => '1.0.0-dev',
        'DB_FILE' => './data/database.json',
        'SECRET' => 'debug_secret_DUMMY_xyz123'
    ]
], JSON_PRETTY_PRINT);
?>
