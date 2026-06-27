const express = require('express');
const router = express.Router();

// =============================================
// ⚠️ ENDPOINT TERSEMBUNYI UNTUK LATIHAN RECON
// Endpoint ini dirancang untuk ditemukan oleh
// tools seperti dirsearch, gobuster, ffuf, dll.
// =============================================

// Semua endpoint ini mengembalikan data dummy
// tetapi mensimulasikan kebocoran nyata

// =============================================
// HIDDEN ENDPOINT 1: /admin
// =============================================
router.get('/admin', (req, res) => {
  // ⚠️ Admin panel tanpa autentikasi (accessible to anyone)
  res.status(200).send(`
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel – CyberLAB</title>
  <style>
    body { font-family: monospace; background: #1a1a1a; color: #00ff00; padding: 2rem; }
    .box { border: 1px solid #00ff00; padding: 1.5rem; max-width: 600px; margin: 0 auto; }
    h1 { color: #ff5555; }
    .found { color: #ffff00; background: #2a2a00; padding: 0.5rem; margin: 0.5rem 0; }
    .cred { color: #00ffff; font-weight: bold; }
    a { color: #00ff00; }
    .warning { color: #ff5555; border: 1px dashed #ff5555; padding: 0.5rem; margin-top: 1rem; }
  </style>
</head>
<body>
  <div class="box">
    <h1>⚠️ ADMIN PANEL – CYBER LAB (SIMULASI)</h1>
    <div class="found">
      ✅ Endpoint ini ditemukan via: dirsearch / gobuster / ffuf
    </div>

    <h3>🔓 Fake Admin Credentials (DUMMY)</h3>
    <p>Username: <span class="cred">admin</span></p>
    <p>Password: <span class="cred">Admin@1234_DUMMY</span></p>

    <h3>🔗 Sub-endpoints admin:</h3>
    <ul>
      <li><a href="/admin/dashboard">/admin/dashboard</a></li>
      <li><a href="/admin/users">/admin/users</a></li>
      <li><a href="/admin/config">/admin/config</a></li>
    </ul>

    <h3>💡 Pelajaran:</h3>
    <p>Endpoint /admin seharusnya:</p>
    <ul>
      <li>Dilindungi autentikasi kuat</li>
      <li>Dibatasi akses hanya dari IP tertentu</li>
      <li>Tidak ditemukan oleh search engine (robots.txt + noindex)</li>
    </ul>

    <div class="warning">
      ⚠️ SIMULASI LAB – Data di halaman ini adalah DUMMY<br>
      Buka <a href="/lab/recon">Lab Recon</a> untuk panduan lengkap
    </div>
  </div>
</body>
</html>
  `);
});

// =============================================
// HIDDEN ENDPOINT 2: /admin/dashboard
// =============================================
router.get('/admin/dashboard', (req, res) => {
  res.status(200).json({
    status: 'ok',
    page: 'admin_dashboard',
    note: '⚠️ Endpoint ini ditemukan via directory brute-force (SIMULASI)',
    stats: {
      totalUsers: 42,
      activeTokens: 17,
      lastBackup: '2026-06-27T00:00:00Z',
      dbSize: '12.4 MB',
      internalApiKey: 'sk_internal_DUMMY_KEY_abc123xyz'  // ⚠️ Credential leak
    },
    links: {
      users: '/admin/users',
      config: '/admin/config',
      logs: '/admin/logs'
    }
  });
});

// =============================================
// HIDDEN ENDPOINT 3: /admin/users
// =============================================
router.get('/admin/users', (req, res) => {
  res.status(200).json({
    note: '⚠️ User list terekspos tanpa autentikasi (SIMULASI)',
    users: [
      { id: 1, username: 'admin',   email: 'admin@cyberlab.local',   role: 'administrator', passwordHash: '$2b$10$DUMMY_HASH_admin' },
      { id: 2, username: 'student', email: 'student@cyberlab.local', role: 'user',          passwordHash: '$2b$10$DUMMY_HASH_student' },
      { id: 3, username: 'instructor', email: 'instructor@cyberlab.local', role: 'moderator', passwordHash: '$2b$10$DUMMY_HASH_instructor' }
    ]
  });
});

// =============================================
// HIDDEN ENDPOINT 4: /.env
// =============================================
router.get('/.env', (req, res) => {
  res.type('text/plain');
  // ⚠️ VULNERABLE: File .env yang terekspos ke publik
  res.send(`# .env – SIMULASI KEBOCORAN FILE KONFIGURASI
# File ini ditemukan karena server salah konfigurasi
# (file .env di-serve sebagai static file)

NODE_ENV=development
PORT=3000

# ⚠️ Secret keys (DUMMY – hanya untuk lab)
APP_SECRET=app_secret_DUMMY_abc123xyz
JWT_SECRET=jwt_super_secret_DUMMY_KEY_2026
ADMIN_TOKEN=admin_token_DUMMY_xyz789

# Database
DB_HOST=localhost
DB_PORT=5432
DB_NAME=cyberlab_db
DB_USER=cyberlab_admin
DB_PASS=DBpass_DUMMY_2026!

# Internal API
INTERNAL_API_URL=http://internal-api.local:8080
INTERNAL_API_KEY=sk_internal_DUMMY_abc123

# Third party (DUMMY)
SMTP_HOST=mail.cyberlab.local
SMTP_USER=noreply@cyberlab.local
SMTP_PASS=smtp_DUMMY_pass123
`);
});

// =============================================
// HIDDEN ENDPOINT 5: /backup
// =============================================
router.get('/backup', (req, res) => {
  res.status(200).json({
    status: 'ok',
    note: '⚠️ Backup directory listing terekspos (SIMULASI)',
    files: [
      { name: 'db_backup_2026-06-01.sql',  size: '8.2 MB',  modified: '2026-06-01' },
      { name: 'db_backup_2026-06-15.sql',  size: '8.9 MB',  modified: '2026-06-15' },
      { name: 'db_backup_2026-06-27.sql',  size: '9.1 MB',  modified: '2026-06-27' },
      { name: 'config_backup.zip',          size: '1.2 MB',  modified: '2026-06-20' },
      { name: 'source_code_backup.tar.gz',  size: '24.5 MB', modified: '2026-06-10' }
    ],
    warning: 'File backup berisi data sensitif dan kode sumber! Jangan pernah expose di production.'
  });
});

// =============================================
// HIDDEN ENDPOINT 6: /config.json
// =============================================
router.get('/config.json', (req, res) => {
  res.status(200).json({
    _note: '⚠️ File konfigurasi terekspos (SIMULASI)',
    app: {
      name: 'CyberLAB',
      version: '1.0.0',
      environment: 'development',
      debug: true,
      adminPath: '/admin',
      backupPath: '/backup',
      uploadPath: '/uploads'
    },
    database: {
      type: 'sqlite',
      path: './data/database.sqlite',
      backupEnabled: true
    },
    auth: {
      sessionSecret: 'session_secret_DUMMY_abc',
      jwtSecret: 'jwt_DUMMY_secret_key',
      adminUsername: 'admin',
      adminPassword: 'Admin@1234_DUMMY'
    },
    internalServices: {
      apiGateway: 'http://api-gateway.internal:8080',
      authService: 'http://auth.internal:8081',
      fileService: 'http://files.internal:8082'
    }
  });
});

// =============================================
// HIDDEN ENDPOINT 7: /api/users
// =============================================
router.get('/api/users', (req, res) => {
  res.status(200).json({
    status: 'ok',
    _note: '⚠️ API endpoint tanpa autentikasi (SIMULASI)',
    total: 3,
    data: [
      { id: 1, name: 'Admin',      email: 'admin@cyberlab.local',   role: 'admin',     token: 'tok_admin_DUMMY_abc123' },
      { id: 2, name: 'Student',    email: 'student@cyberlab.local', role: 'user',      token: 'tok_user_DUMMY_xyz789' },
      { id: 3, name: 'Instructor', email: 'instr@cyberlab.local',   role: 'moderator', token: 'tok_mod_DUMMY_def456' }
    ]
  });
});

// =============================================
// HIDDEN ENDPOINT 8: /api/keys
// =============================================
router.get('/api/keys', (req, res) => {
  res.status(200).json({
    status: 'ok',
    _note: '⚠️ API keys terekspos tanpa autentikasi (SIMULASI)',
    keys: [
      { name: 'Production API Key', key: 'sk_prod_DUMMY_abc123xyz789', scope: 'read:write', created: '2026-01-01' },
      { name: 'Internal Service',   key: 'sk_int_DUMMY_def456uvw012',  scope: 'admin',      created: '2026-01-15' },
      { name: 'Third Party',        key: 'sk_3rd_DUMMY_ghi789rst345',  scope: 'read',       created: '2026-02-01' }
    ],
    webhookSecret: 'whsec_DUMMY_jkl012mno678'
  });
});

// =============================================
// HIDDEN ENDPOINT 9: /api/v1/debug
// =============================================
router.get('/api/v1/debug', (req, res) => {
  res.status(200).json({
    debug: true,
    _note: '⚠️ Debug endpoint aktif di production (SIMULASI)',
    request: {
      ip: '127.0.0.1',
      method: 'GET',
      path: '/api/v1/debug',
      headers: { 'user-agent': 'Burp Suite', host: 'localhost:3000' }
    },
    server: {
      nodeVersion: process.version,
      platform: process.platform,
      arch: process.arch,
      pid: process.pid,
      uptime: Math.floor(process.uptime()),
      memory: process.memoryUsage()
    },
    env: {
      NODE_ENV: 'development',
      APP_VERSION: '1.0.0',
      SECRET: 'debug_secret_DUMMY'
    }
  });
});

// =============================================
// HIDDEN ENDPOINT 10: /.git/config
// =============================================
router.get('/.git/config', (req, res) => {
  res.type('text/plain');
  // ⚠️ VULNERABLE: Git config terekspos ke publik
  res.send(`[core]
\trepositoryformatversion = 0
\tfilemode = true
\tbare = false
\tlogallrefupdates = true

[remote "origin"]
\turl = https://github.com/cyberlab-internal/web-app-DUMMY.git
\tfetch = +refs/heads/*:refs/remotes/origin/*

[branch "main"]
\tremote = origin
\tmerge = refs/heads/main

# ⚠️ SIMULASI: .git folder terekspos berarti attacker bisa
# download seluruh source code via: git clone http://target/.git
# Tools: git-dumper, GitHack, gitjacker
`);
});

// =============================================
// HIDDEN ENDPOINT 11: /server-status
// =============================================
router.get('/server-status', (req, res) => {
  res.status(200).json({
    status: 'running',
    _note: '⚠️ Server status endpoint terekspos (SIMULASI)',
    server: 'Apache/2.4.52 (Ubuntu)',
    uptime: Math.floor(process.uptime()) + ' seconds',
    connections: { total: 1248, active: 7, idle: 3 },
    workers: { total: 4, busy: 1, idle: 3 },
    requests: { total: 9821, perSecond: 2.3 },
    internalIPs: ['10.0.0.1', '192.168.1.100', '172.16.0.5']
  });
});

// =============================================
// HIDDEN ENDPOINT 12: /console
// =============================================
router.get('/console', (req, res) => {
  res.status(200).send(`
<!DOCTYPE html>
<html>
<head>
  <title>Debug Console (SIMULASI)</title>
  <style>
    body { background:#0d0d0d; color:#00ff00; font-family:monospace; padding:2rem; }
    .box { border:1px solid #00ff00; padding:1.5rem; max-width:700px; margin:0 auto; }
    input { background:#0d0d0d; color:#00ff00; border:1px solid #00ff00; padding:0.5rem; width:100%; }
    .note { color:#ff5555; margin-top:1rem; border:1px dashed #ff5555; padding:0.5rem; }
  </style>
</head>
<body>
  <div class="box">
    <h2>⚠️ Debug Console – SIMULASI LAB</h2>
    <p>Endpoint ini mensimulasikan debug console yang terekspos.</p>
    <p>Di dunia nyata, endpoint seperti ini memungkinkan eksekusi kode atau command!</p>
    <p><strong>Contoh tools nyata:</strong> Laravel Telescope, PHP eval(), Node.js REPL exposure</p>
    <br>
    <div class="note">
      ⚠️ SIMULASI: Console ini tidak menjalankan command apapun.<br>
      Temukan endpoint ini via dirsearch atau baca robots.txt
    </div>
    <br>
    <a href="/lab/recon" style="color:#00ffff">→ Kembali ke Recon Lab</a>
  </div>
</body>
</html>
  `);
});

// =============================================
// HALAMAN RECON LAB
// =============================================
router.get('/lab/recon', (req, res) => {
  res.render('recon', {
    title: 'LAB 4: Recon & Hidden Endpoints Discovery',
    page: 'recon'
  });
});

module.exports = router;
