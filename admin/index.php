<?php
include_once(dirname(__DIR__) . '/config.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel – CyberLAB</title>
  <link rel="stylesheet" href="<?= $base_url ?>/public/css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body {
      background-color: var(--bg-dark);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 1.5rem;
    }
    .admin-container {
      max-width: 600px;
      width: 100%;
      background: var(--bg-card);
      border: 1px solid var(--vuln-border);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-lg), 0 0 30px rgba(255, 0, 85, 0.05);
      padding: 2.5rem;
      animation: fadeSlide 0.3s ease;
    }
    .admin-title {
      font-size: 1.6rem;
      font-weight: 800;
      color: var(--accent-red);
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .admin-badge {
      display: inline-block;
      background: rgba(255, 0, 85, 0.1);
      border: 1px solid rgba(255, 0, 85, 0.3);
      color: var(--accent-red);
      font-family: 'JetBrains Mono', monospace;
      font-size: 0.78rem;
      padding: 0.35rem 0.75rem;
      border-radius: var(--radius-sm);
      margin-bottom: 1.5rem;
      font-weight: 600;
    }
    .cred-box {
      background: rgba(0, 0, 0, 0.25);
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      padding: 1.25rem;
      margin-bottom: 1.5rem;
      font-family: 'JetBrains Mono', monospace;
    }
    .cred-item {
      display: flex;
      justify-content: space-between;
      padding: 0.4rem 0;
      font-size: 0.85rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
    .cred-item:last-child {
      border-bottom: none;
    }
    .cred-label {
      color: var(--text-muted);
    }
    .cred-val {
      color: var(--accent-blue);
      font-weight: 600;
    }
    .endpoint-list {
      margin-bottom: 2rem;
    }
    .endpoint-list h3 {
      font-size: 0.95rem;
      font-weight: 700;
      margin-bottom: 0.75rem;
      color: var(--text-primary);
    }
    .endpoint-list ul {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }
    .endpoint-list a {
      font-family: 'JetBrains Mono', monospace;
      font-size: 0.82rem;
      color: var(--accent-green);
      text-decoration: none;
      transition: color var(--transition);
    }
    .endpoint-list a:hover {
      color: var(--text-primary);
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="admin-container">
    <h1 class="admin-title">🕵️ Admin Portal</h1>
    <div class="admin-badge">⚠️ SIMULASI RECONNAISSANCE</div>
    
    <p style="font-size:0.9rem; color:var(--text-secondary); margin-bottom:1.5rem; line-height:1.5;">
      Selamat! Anda berhasil menemukan panel admin rahasia ini melalui directory brute-force.
    </p>

    <div class="cred-box">
      <div style="font-weight:700; color:var(--accent-red); margin-bottom:0.75rem; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.05em;">🔓 Credentials Found (DUMMY)</div>
      <div class="cred-item">
        <span class="cred-label">Username:</span>
        <span class="cred-val">admin</span>
      </div>
      <div class="cred-item">
        <span class="cred-label">Password:</span>
        <span class="cred-val">Admin@1234_DUMMY</span>
      </div>
    </div>

    <div class="endpoint-list">
      <h3>🔗 Sub-endpoints admin terdeteksi:</h3>
      <ul>
        <li>
          <a href="<?= $base_url ?>/admin/dashboard.php" target="_blank">▸ /admin/dashboard.php</a>
        </li>
        <li>
          <a href="<?= $base_url ?>/admin/users.php" target="_blank">▸ /admin/users.php</a>
        </li>
      </ul>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; border-top: 1px solid var(--border); padding-top:1.5rem;">
      <a href="<?= $base_url ?>/index.php" class="btn btn-outline">🏠 Kembali ke Beranda</a>
      <span style="font-size:0.75rem; color:var(--text-muted); font-family:'JetBrains Mono', monospace;">ADMIN AREA</span>
    </div>
  </div>
</body>
</html>
