<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    '_note' => '⚠️ API endpoint tanpa autentikasi (SIMULASI PHP)',
    'total' => 2,
    'data' => [
        ['id' => 1, 'name' => 'Admin', 'email' => 'admin@cyberlab.local', 'role' => 'admin', 'token' => 'tok_admin_DUMMY_abc123'],
        ['id' => 2, 'name' => 'Student', 'email' => 'student@cyberlab.local', 'role' => 'user', 'token' => 'tok_user_DUMMY_xyz789']
    ]
], JSON_PRETTY_PRINT);
?>
