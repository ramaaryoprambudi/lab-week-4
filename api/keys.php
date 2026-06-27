<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    '_note' => '⚠️ API keys terekspos tanpa autentikasi (SIMULASI PHP)',
    'keys' => [
        ['name' => 'Production API Key', 'key' => 'sk_prod_DUMMY_abc123xyz789', 'scope' => 'read:write', 'created' => '2026-01-01'],
        ['name' => 'Internal Service', 'key' => 'sk_int_DUMMY_def456uvw012', 'scope' => 'admin', 'created' => '2026-01-15']
    ]
], JSON_PRETTY_PRINT);
?>
