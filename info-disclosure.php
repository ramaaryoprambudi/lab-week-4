<?php
include_once('config.php');
$title = "Information Disclosure";

// Simulasikan endpoint API bocor
$user_profile = [
    'id' => 42,
    'username' => 'student',
    'email' => 'student@cyberlab.local',
    'role' => 'user',
    // ⚠️ VULNERABLE: Membocorkan data internal sensitif
    'internalUserId' => 'USR-9982-SENSITIVE',
    'debugNote' => 'Password default sebelum diganti adalah: StudentPass123_DUMMY',
    'lastLoginIp' => '10.0.2.15',
    'apiToken' => 'tok_bocor_abc123xyz789_DUMMY'
];

$server_debug = [
    'status' => 'debug',
    'environment' => 'development',
    'appVersion' => '1.0.0-dev',
    // ⚠️ VULNERABLE: Membocorkan kredensial konfigurasi sensitif
    'db' => [
        'type' => 'sqlite',
        'host' => 'localhost',
        'database' => './data/database.json',
        'username' => 'admin_dummy',
        'password' => 'DB_Secret_DUMMY_Password!'
    ],
    'server' => [
        'php_version' => PHP_VERSION,
        'os' => PHP_OS,
        'api_key' => 'api_key_DUMMY_xyz789abc'
    ]
];

include('header.php');
?>

<main class="main-content">

  <!-- PAGE HEADER -->
  <div class="page-header vuln-page-header">
    <div class="page-header-inner">
      <div class="lab-badge vuln-badge">⚠️ VULNERABLE VERSION</div>
      <h1>🔍 LAB 2: Information Disclosure</h1>
      <p>Simulasi kebocoran data sensitif melalui API internal, debug endpoint, dan error stack trace.</p>
    </div>
  </div>

  <div class="lab-container">

    <!-- KONSEP -->
    <section class="lab-section">
      <h2>📖 Konsep</h2>
      <div class="concept-box">
        <p><strong>Information Disclosure</strong> (Kebocoran Informasi) terjadi ketika aplikasi secara tidak sengaja mengungkapkan informasi sensitif kepada pengguna yang tidak berhak. Informasi ini bisa berupa detail teknis, kredensial konfigurasi, data pribadi pengguna, atau pesan error internal.</p>
        <p>Attacker sering mencari kebocoran data seperti ini untuk melakukan pengintaian (*reconnaissance*) sebelum meluncurkan serangan yang lebih bertarget.</p>
      </div>
    </section>

    <!-- SIMULASI VULNERABLE -->
    <section class="lab-section">
      <h2>🧪 Simulasi Kebocoran Informasi</h2>
      <div class="vuln-box">
        <div class="vuln-label">⚠️ Silakan klik tombol di bawah untuk melihat kebocoran data:</div>
        
        <div class="disclosure-buttons" style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:1rem;">
          <button onclick="getProfileLeak()" class="btn btn-vuln" id="btn-profile-leak">👤 Profile Leak (API)</button>
          <button onclick="getDebugLeak()" class="btn btn-vuln" id="btn-debug-leak">⚙️ Debug Endpoint Leak</button>
          <button onclick="getErrorLeak()" class="btn btn-vuln" id="btn-error-leak">💥 Error Stack Trace Leak</button>
        </div>

        <!-- OUTPUT BOX -->
        <div class="response-output" id="output-container" style="display:none; margin-top: 1.5rem;">
          <div class="response-header">
            <div>
              <span id="output-url" style="margin-right: 1rem; font-weight: 600;">GET /api/endpoint</span>
              <span class="disc-status" id="output-status">HTTP 200 OK</span>
            </div>
            <button onclick="document.getElementById('output-container').style.display='none'" class="btn-close-response">✕</button>
          </div>
          <pre id="json-output" class="response-pre"></pre>
        </div>
      </div>
    </section>

    <!-- CARA MENGUJI -->
    <section class="lab-section">
      <h2>🔨 Cara Menguji dengan Burp Suite / dirsearch</h2>
      <div class="vuln-box">
        <ul>
          <li><strong>Burp Suite (Header Leak)</strong>: Buka tab Proxy → Intercept request ke aplikasi utama → Lihat bagian Response Headers. Anda akan melihat header internal server seperti <code>X-Debug-Token</code> atau <code>Server</code> yang terekspos.</li>
          <li><strong>dirsearch (Sensitive Files)</strong>: Jalankan dirsearch untuk menemukan file konfigurasi yang terekspos seperti <code>/config.json</code> or <code>/.env</code> di root direktori.</li>
        </ul>
      </div>
    </section>

    <!-- APA YANG SALAH -->
    <div class="two-col-grid">
      <section class="lab-section">
        <h2>❌ Apa yang Salah?</h2>
        <div class="wrong-box">
          <ul class="check-list check-red">
            <li>Mengirimkan seluruh objek database/user langsung ke client.</li>
            <li>Endpoint debug sensitif dibiarkan terbuka untuk publik tanpa autentikasi.</li>
            <li>Sistem menampilkan pesan error mentah (*stack trace*) langsung ke pengguna.</li>
          </ul>
        </div>
      </section>

      <section class="lab-section">
        <h2>💥 Dampak</h2>
        <div class="impact-box">
          <ul class="check-list check-orange">
            <li>Kredensial database bocor → Potensi pengambilalihan database.</li>
            <li>Token API/Password default bocor → Akun admin bisa diretas.</li>
            <li>Detail sistem operasi &amp; versi bahasa pemrograman bocor → Memudahkan pencarian CVE eksploit.</li>
          </ul>
        </div>
      </section>
    </div>

    <!-- NAVIGASI -->
    <div class="nav-to-fixed">
      <a href="<?= $base_url ?>/info-disclosure-fixed.php" class="btn btn-fixed-large" id="btn-goto-fixed-info">
        ✅ Lihat Versi Aman (Fixed) →
      </a>
    </div>

  </div>
</main>

<?php include('footer.php'); ?>

<script>
const profileData = <?php echo json_encode($user_profile, JSON_PRETTY_PRINT); ?>;
const debugData = <?php echo json_encode($server_debug, JSON_PRETTY_PRINT); ?>;

function displayOutput(url, status, data, isError = false) {
  const container = document.getElementById('output-container');
  const outputUrl = document.getElementById('output-url');
  const outputStatus = document.getElementById('output-status');
  const jsonOutput = document.getElementById('json-output');

  outputUrl.textContent = url;
  outputStatus.textContent = status;
  
  if (isError) {
    outputStatus.style.color = 'var(--accent-red)';
    outputStatus.style.background = 'rgba(255, 0, 85, 0.15)';
    container.style.borderColor = 'rgba(255, 0, 85, 0.4)';
  } else {
    outputStatus.style.color = 'var(--accent-green)';
    outputStatus.style.background = 'rgba(0, 255, 102, 0.1)';
    container.style.borderColor = 'rgba(0, 255, 102, 0.4)';
  }

  jsonOutput.innerHTML = data;
  container.style.display = 'block';
  container.scrollIntoView({ behavior: 'smooth' });
}

function getProfileLeak() {
  displayOutput('GET <?= $base_url ?>/api/profile.php', 'HTTP 200 OK', JSON.stringify(profileData, null, 2));
}

function getDebugLeak() {
  displayOutput('GET <?= $base_url ?>/api/debug.php', 'HTTP 200 OK', JSON.stringify(debugData, null, 2));
}

function getErrorLeak() {
  const errorHtml = `Fatal error: Uncaught Error: Call to a member function query() on null in /var/www/html/lib/database.php:24
Stack trace:
#0 /var/www/html/api/profile.php(10): Database->getUserProfile('42')
#1 /var/www/html/index.php(15): include('/var/www/html/a...')
#2 {main}
  thrown in /var/www/html/lib/database.php on line 24`;
  displayOutput('GET <?= $base_url ?>/api/profile.php?id=999999999999999999999', 'HTTP 500 Internal Server Error', errorHtml, true);
}
</script>
