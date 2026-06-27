<?php
include_once('config.php');
$title = "Recon & Hidden Endpoints";
include('header.php');

$local_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $base_url;

// Tentukan IP server dinamis
$localIp = 'localhost';
if (isset($_SERVER['SERVER_ADDR'])) {
    $localIp = $_SERVER['SERVER_ADDR'];
}
?>

<main class="main-content">

  <div class="page-header" style="background:linear-gradient(135deg,#0d0a1a,#1a1030);border:1px solid #5e35b1;">
    <div class="page-header-inner">
      <div class="lab-badge" style="background:rgba(94,53,177,0.2);color:#b39ddb;border:1px solid rgba(94,53,177,0.4);">🕵️ RECON LAB</div>
      <h1>🔍 LAB 4: Recon & Hidden Endpoints Discovery</h1>
      <p>Latihan menemukan endpoint tersembunyi menggunakan <strong>dirsearch</strong>, <strong>gobuster</strong>, dan menganalisis HTTP headers via <strong>Burp Suite</strong>.</p>
    </div>
  </div>

  <div class="lab-container">

    <!-- KONSEP -->
    <section class="lab-section">
      <h2>📖 Konsep Reconnaissance</h2>
      <div class="concept-box">
        <p><strong>Reconnaissance (Recon)</strong> adalah tahap pertama dalam penetration testing — mengumpulkan informasi sebanyak mungkin tentang target sebelum melakukan serangan.</p>
        <p>Pada web application, recon meliputi:</p>
        <ul>
          <li><strong>Directory/File Brute-Force</strong> – Menemukan path tersembunyi (dirsearch, gobuster, ffuf)</li>
          <li><strong>HTTP Header Analysis</strong> – Membaca informasi yang bocor di response headers (Burp Suite)</li>
          <li><strong>robots.txt Reading</strong> – Mencari daftar path yang sengaja disembunyikan</li>
          <li><strong>Cookie Analysis</strong> – Memeriksa flag keamanan cookie (HttpOnly, Secure, SameSite)</li>
        </ul>
      </div>
    </section>

    <!-- BAGIAN 1: DIRSEARCH -->
    <section class="lab-section">
      <h2>🔨 Bagian 1 – Directory Brute-Force dengan dirsearch</h2>
      <div class="vuln-box">
        <div class="vuln-label">🔍 Tools: dirsearch / gobuster / ffuf</div>

        <h4 style="margin-bottom:1rem;color:#c9d1d9;">📦 Install dirsearch</h4>
        <div class="code-block">
          <div class="code-label">Terminal – Install dirsearch</div>
          <pre><code># Via pip (Python)
pip install dirsearch

# Atau clone dari GitHub
git clone https://github.com/maurosoria/dirsearch.git
cd dirsearch
pip install -r requirements.txt</code></pre>
        </div>

        <h4 style="margin:1.5rem 0 1rem;color:#c9d1d9;">🚀 Perintah dirsearch untuk Lab Ini</h4>
        <div class="code-block">
          <div class="code-label">Perintah dirsearch – Basic Scan</div>
          <pre><code># Scan basic terhadap server lab
dirsearch -u <?= $local_url ?> -e php,html,js,json,txt,sql,zip,env,git,bak</code></pre>
        </div>

        <h4 style="margin:1.5rem 0 1rem;color:#c9d1d9;">🎯 Endpoint yang Seharusnya Ditemukan</h4>
        <div class="endpoint-discovery-grid">
          <?php
          $endpoints = [
              ['path' => '/robots.txt',        'status' => '200', 'type' => 'text/plain', 'severity' => 'medium', 'note' => 'Membocorkan daftar path sensitif'],
              ['path' => '/admin/index.php',   'status' => '200', 'type' => 'html',       'severity' => 'critical', 'note' => 'Admin panel tanpa autentikasi'],
              ['path' => '/admin/dashboard.php', 'status' => '200', 'type' => 'json',     'severity' => 'critical', 'note' => 'Dashboard data & credentials'],
              ['path' => '/admin/users.php',   'status' => '200', 'type' => 'json',       'severity' => 'critical', 'note' => 'Daftar user + password hash'],
              ['path' => '/.env',              'status' => '200', 'type' => 'text/plain', 'severity' => 'critical', 'note' => 'File .env dengan secret keys'],
              ['path' => '/config.json',       'status' => '200', 'type' => 'json',       'severity' => 'high', 'note' => 'Konfigurasi app + credentials'],
              ['path' => '/api/users.php',     'status' => '200', 'type' => 'json',       'severity' => 'high', 'note' => 'API user list tanpa auth'],
              ['path' => '/api/keys.php',      'status' => '200', 'type' => 'json',       'severity' => 'critical', 'note' => 'API keys & webhook secrets'],
              ['path' => '/api/debug.php',     'status' => '200', 'type' => 'json',       'severity' => 'high', 'note' => 'Debug info & env variables'],
              ['path' => '/.git/config',       'status' => '200', 'type' => 'text/plain', 'severity' => 'high', 'note' => 'Git config & repo URL'],
          ];

          foreach ($endpoints as $ep):
          ?>
          <div class="discovery-item">
            <div class="discovery-path">
              <a href="<?php echo $base_url . $ep['path']; ?>" target="_blank" class="discovery-link"><?php echo $ep['path']; ?></a>
            </div>
            <div class="discovery-meta">
              <span class="disc-status">HTTP <?php echo $ep['status']; ?></span>
              <span class="disc-type"><?php echo $ep['type']; ?></span>
              <span class="disc-severity sev-<?php echo $ep['severity']; ?>"><?php echo strtoupper($ep['severity']); ?></span>
            </div>
            <div class="discovery-note"><?php echo $ep['note']; ?></div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="info-callout callout-orange" style="margin-top:1rem;">
          <strong>💡 Cara Pakai:</strong> Klik link di atas untuk buka manual, ATAU jalankan dirsearch dan temukan sendiri!
        </div>
      </div>
    </section>

    <!-- BAGIAN 2: BURP SUITE -->
    <section class="lab-section">
      <h2>🔧 Bagian 2 – HTTP Header Analysis via Burp Suite</h2>
      <div class="vuln-box">
        <div class="vuln-label">🦊 Tools: Burp Suite Community / Professional</div>
        <p>Buka proxy listener Anda, tangkap request ke <code>http://localhost:3000</code>, dan lihat detail Response Headers yang dibocorkan oleh web server PHP.</p>
      </div>
    </section>

  </div>
</main>

<?php include('footer.php'); ?>
