<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'page' => 'admin_dashboard',
    'note' => '⚠️ Endpoint ini ditemukan via directory brute-force (SIMULASI PHP)',
    'stats' => [
        'totalUsers' => 42,
        'activeTokens' => 17,
        'lastBackup' => '2026-06-27T00:00:00Z',
        'dbSize' => '1.4 KB',
        'internalApiKey' => 'sk_internal_DUMMY_KEY_abc123xyz'
    ],
    'links' => [
        'users' => '/admin/users.php',
        'config' => '/config.json'
    ]
], JSON_PRETTY_PRINT);
?>
