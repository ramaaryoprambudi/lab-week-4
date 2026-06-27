<?php
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
        
        <div class="disclosure-buttons">
          <button onclick="getSanitizedProfile()" class="btn btn-fixed" style="background:var(--accent-green);color:#000;">👤 Sanitized Profile (API)</button>
          <button onclick="getClosedDebug()" class="btn btn-fixed" style="background:var(--accent-green);color:#000;">⚙️ Debug Endpoint (Closed)</button>
          <button onclick="getGenericError()" class="btn btn-fixed" style="background:var(--accent-green);color:#000;">💥 Generic Error Message</button>
        </div>

        <!-- OUTPUT BOX -->
        <div class="json-output-container" id="output-container" style="display:none; margin-top: 1.5rem;">
          <div class="json-output-header">
            <span id="output-url">GET /api/endpoint</span>
            <span class="json-badge font-mono" id="output-status">HTTP 200 OK</span>
          </div>
          <pre id="json-output" class="json-output"></pre>
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
    outputStatus.className = 'json-badge font-mono text-red';
    outputStatus.style.color = '#ff5555';
  } else {
    outputStatus.className = 'json-badge font-mono';
    outputStatus.style.color = '#00ff00';
  }

  jsonOutput.innerHTML = data;
  container.style.display = 'block';
}

function getSanitizedProfile() {
  displayOutput('GET /api/profile.php', 'HTTP 200 OK', JSON.stringify(safeProfile, null, 2));
}

function getClosedDebug() {
  const errorJson = JSON.stringify({
    status: 403,
    error: "Forbidden",
    message: "Akses ditolak. Debug mode tidak aktif."
  }, null, 2);
  displayOutput('GET /api/debug.php', 'HTTP 403 Forbidden', errorJson, true);
}

function getGenericError() {
  const genericError = JSON.stringify({
    status: 500,
    error: "Internal Server Error",
    message: "Terjadi kesalahan internal. Mohon coba lagi beberapa saat lagi."
  }, null, 2);
  displayOutput('GET /api/profile.php?id=999999999999999999999', 'HTTP 500 Internal Server Error', genericError, true);
}
</script>
