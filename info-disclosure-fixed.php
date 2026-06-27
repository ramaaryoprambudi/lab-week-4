<?php
include_once('config.php');
$title = "Information Disclosure (Fixed)";

$sanitized_profile = [
    'id' => 42,
    'username' => 'student',
    'email' => 'student@cyberlab.local',
    'role' => 'user'
    // ✅ FIXED: Data internal sensitif, token API, dan debug note di-filter
];

include('header.php');
?>

<main class="main-content">

  <!-- PAGE HEADER -->
  <div class="page-header fixed-page-header">
    <div class="page-header-inner">
      <div class="lab-badge fixed-badge">✅ SECURE VERSION</div>
      <h1>🔍 LAB 2 (Fixed): Information Disclosure – Versi Aman</h1>
      <p>Penyaringan ketat respon API, penutupan debug endpoint, dan penanganan error yang aman.</p>
    </div>
  </div>

  <div class="lab-container">

    <!-- KONSEP -->
    <section class="lab-section">
      <h2>📖 Cara Memperbaiki Kerentanan Information Disclosure</h2>
      <div class="concept-box">
        <p>Pengamanan terhadap kebocoran informasi dilakukan dengan tiga prinsip utama:</p>
        <ul>
          <li><strong>Penyaringan Objek (API Sanitization)</strong>: Hanya kirim data yang benar-benar dibutuhkan oleh klien. Hindari mengirimkan seluruh baris database mentah.</li>
          <li><strong>Menonaktifkan Mode Debug di Production</strong>: Pastikan konfigurasi sensitif, kunci rahasia (*secret keys*), dan status lingkungan disembunyikan dari endpoint publik.</li>
          <li><strong>Gunakan Error Handler Global yang Aman</strong>: Jangan tampilkan rincian baris kode (*stack trace*) kepada user. Simpan rincian error di log internal server, dan tampilkan pesan umum (misal: "Terjadi kesalahan internal") ke browser pengguna.</li>
        </ul>
      </div>
    </section>

    <!-- SIMULASI FIXED -->
    <section class="lab-section">
      <h2>🧪 Simulasi Pengujian Versi Aman</h2>
      <div class="vuln-box">
        <div class="fixed-label-inline" style="margin-bottom:1rem;">✅ Endpoint Aman Terkonfigurasi:</div>
        
        <div class="disclosure-buttons" style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:1rem;">
          <button onclick="getSanitizedProfile()" class="btn btn-fixed">👤 Sanitized Profile (API)</button>
          <button onclick="getClosedDebug()" class="btn btn-fixed">⚙️ Debug Endpoint (Closed)</button>
          <button onclick="getGenericError()" class="btn btn-fixed">💥 Generic Error Message</button>
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

    <!-- CARA MEMPERBAIKI KODE (PHP) -->
    <section class="lab-section">
      <h2>🛠️ Contoh Kode Pengamanan (PHP)</h2>
      <div class="code-block">
        <div class="code-label">Penanganan Error &amp; Output API Aman</div>
        <pre><code>// 1. Matikan error display ke user di production
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// 2. Filter data API sebelum dikirim ke client
$user_data = getUserFromDatabase();
$safe_response = [
    'id' => $user_data['id'],
    'username' => $user_data['username'],
    'email' => $user_data['email']
    // Jangan sertakan 'password_hash' atau 'api_token'!
];
header('Content-Type: application/json');
echo json_encode($safe_response);

// 3. Batasi debug endpoint
if ($env !== 'development') {
    http_response_code(403);
    die(json_encode(['error' => 'Akses ditolak']));
}</code></pre>
      </div>
    </section>

  </div>
</main>

<?php include('footer.php'); ?>

<script>
const safeProfile = <?php echo json_encode($sanitized_profile, JSON_PRETTY_PRINT); ?>;

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

function getSanitizedProfile() {
  displayOutput('GET <?= $base_url ?>/api/profile.php', 'HTTP 200 OK', JSON.stringify(safeProfile, null, 2));
}

function getClosedDebug() {
  const errorJson = JSON.stringify({
    status: 403,
    error: "Forbidden",
    message: "Akses ditolak. Debug mode tidak aktif."
  }, null, 2);
  displayOutput('GET <?= $base_url ?>/api/debug.php', 'HTTP 403 Forbidden', errorJson, true);
}

function getGenericError() {
  const genericError = JSON.stringify({
    status: 500,
    error: "Internal Server Error",
    message: "Terjadi kesalahan internal. Mohon coba lagi beberapa saat lagi."
  }, null, 2);
  displayOutput('GET <?= $base_url ?>/api/profile.php?id=999999999999999999999', 'HTTP 500 Internal Server Error', genericError, true);
}
</script>
