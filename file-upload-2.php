<?php
include_once('config.php');
$title = "Lab 2: Blacklist Filter";

$upload_dir = __DIR__ . '/public/uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$message = '';
$error = '';

// Proses upload jika ada file dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $filename = basename($file['name']);
        $target_path = $upload_dir . $filename;
        
        // ⚠️ VULNERABLE: Filter Blacklist Lemah (Hanya mengecek ekstensi ".php" literal lowercase)
        if (str_ends_with($filename, '.php')) {
            $error = "Unggah ditolak: Ekstensi .php diblokir oleh filter blacklist dasar!";
        } else {
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                chmod($target_path, 0644); // Pastikan berkas dapat dibaca oleh web server
                $message = "✅ File lolos filter blacklist: " . htmlspecialchars($filename);
            } else {
                $error = "Gagal memindahkan file ke direktori tujuan.";
            }
        }
    } else {
        $error = "Terjadi kesalahan upload: Kode " . $file['error'];
    }
}

// Ambil daftar file
$uploaded_files = [];
if (file_exists($upload_dir) && $handle = opendir($upload_dir)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != ".." && $entry != "lab-php.php" && !is_dir($upload_dir . $entry)) {
            $uploaded_files[] = [
                'name' => $entry,
                'url' => $base_url . '/public/uploads/' . $entry,
                'size' => filesize($upload_dir . $entry)
            ];
        }
    }
    closedir($handle);
}

include('header.php');
?>

<main class="main-content">

  <!-- PAGE HEADER -->
  <div class="page-header vuln-page-header">
    <div class="page-header-inner">
      <div class="lab-badge vuln-badge">⚠️ VULNERABLE LEVEL 2</div>
      <h1>📁 LAB 2: Filter Blacklist Ekstensi</h1>
      <p>Simulasi filter ekstensi berbasis blacklist. Server memblokir file berekstensi <code>.php</code>.</p>
    </div>
  </div>

  <!-- NAVIGATION SUB-LABS -->
  <div style="display:flex; gap:0.5rem; margin-bottom:2rem; flex-wrap:wrap;">
    <a href="<?= $base_url ?>/file-upload-1.php" class="btn btn-outline">Level 1: No Validation</a>
    <a href="<?= $base_url ?>/file-upload-2.php" class="btn btn-vuln btn-vuln-active">Level 2: Blacklist Filter</a>
    <a href="<?= $base_url ?>/file-upload-3.php" class="btn btn-outline">Level 3: Extension Whitelist</a>
    <a href="<?= $base_url ?>/file-upload-fixed.php" class="btn btn-fixed">Level 4: Secure Version</a>
  </div>

  <div class="lab-container">

    <!-- KONSEP -->
    <section class="lab-section">
      <h2>📖 Konsep Blacklist Bypass</h2>
      <div class="concept-box">
        <p>Pendekatan <strong>Blacklist</strong> bekerja dengan memblokir daftar kata kunci atau ekstensi yang sudah diketahui berbahaya (misal: <code>.php</code>, <code>.exe</code>).</p>
        <p>Kelemahan fatal metode ini adalah melimpahnya variasi ekstensi interpreter yang mungkin dilewatkan oleh programmer. Selain itu, jika pemeriksaan tidak bersifat *case-insensitive*, manipulasi huruf besar-kecil akan dengan mudah menembus filter ini.</p>
      </div>
    </section>

    <!-- FORM UPLOAD -->
    <section class="lab-section">
      <h2>🧪 Simulasi Upload &amp; Bypass</h2>
      <div class="vuln-box">
        <div class="vuln-label">⚠️ Endpoint: <code>POST /file-upload-2.php</code></div>

        <?php if ($message): ?>
          <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="upload-form">
          <div class="form-group">
            <label for="file-input">Pilih file untuk diupload:</label>
            <div class="file-input-wrapper">
              <input type="file" name="file" id="file-input" class="file-input" />
              <div class="file-input-display" id="file-display">Belum ada file dipilih...</div>
            </div>
            <small class="form-hint text-red">⚠️ Blacklist aktif: File dengan ekstensi <code>.php</code> (lowercase) akan langsung ditolak.</small>
          </div>
          <button type="submit" class="btn btn-vuln-submit" id="btn-upload-vuln">📤 Upload File (Blacklist Filter)</button>
        </form>

        <!-- TIPS CHALLENGE -->
        <div class="webshell-demo" style="border-color:var(--accent-orange); background:rgba(255,123,53,0.03);">
          <h4 style="color:var(--accent-orange)">💡 Petunjuk Latihan Bypass:</h4>
          <ul>
            <li><strong>Teknik 1: Case Manipulation</strong> – Coba ganti ekstensi file webshell Anda dari `.php` menjadi huruf campuran seperti <code>.pHp</code>, <code>.PhP</code>, atau <code>.PHP</code>.</li>
            <li><strong>Teknik 2: Alternatif Ekstensi</strong> – Coba gunakan ekstensi interpreter alternatif seperti <code>.phtml</code>, <code>.php5</code>, atau <code>.phar</code>.</li>
          </ul>
        </div>
      </div>
    </section>

    <!-- LIST FILE -->
    <?php if (!empty($uploaded_files)): ?>
    <section class="lab-section">
      <h2>📂 Daftar File yang Diunggah</h2>
      <div class="vuln-box">
        <table class="file-table">
          <thead>
            <tr>
              <th>Nama File</th>
              <th>Ukuran</th>
              <th>Aksi / URL</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($uploaded_files as $f): ?>
            <tr>
              <td><code><?php echo htmlspecialchars($f['name']); ?></code></td>
              <td><?php echo round($f['size'] / 1024, 2); ?> KB</td>
              <td>
                <a href="<?php echo htmlspecialchars($f['url']); ?>" target="_blank" class="file-link">🔗 Buka / Jalankan File</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
    <?php endif; ?>

  </div>
</main>

<?php include('footer.php'); ?>

<script>
  document.getElementById('file-input').addEventListener('change', function() {
    const display = document.getElementById('file-display');
    display.textContent = this.files[0] ? this.files[0].name + ' (' + (this.files[0].size / 1024).toFixed(2) + ' KB)' : 'Belum ada file dipilih...';
  });
</script>
