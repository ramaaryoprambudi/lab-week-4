<?php
header('Content-Type: application/json');
echo json_encode([
    'note' => '⚠️ User list terekspos tanpa autentikasi (SIMULASI PHP)',
    'users' => [
        ['id' => 1, 'username' => 'admin', 'email' => 'admin@cyberlab.local', 'role' => 'administrator', 'passwordHash' => '$2b$10$DUMMY_HASH_admin'],
        ['id' => 2, 'username' => 'student', 'email' => 'student@cyberlab.local', 'role' => 'user', 'passwordHash' => '$2b$10$DUMMY_HASH_student']
    ]
], JSON_PRETTY_PRINT);
?>
