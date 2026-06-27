<?php
include_once('config.php');
$title = "Lab 1: Tanpa Validasi";

$upload_dir = __DIR__ . '/public/uploads/';
if (!fs_exists_fallback($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

function fs_exists_fallback($dir) {
    return file_exists($dir);
}

$message = '';
$error = '';

// Proses upload jika ada file dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $filename = basename($file['name']);
        $target_path = $upload_dir . $filename;
        
        // ⚠️ VULNERABLE: Tanpa validasi apapun
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            chmod($target_path, 0644); // Pastikan berkas dapat dibaca oleh web server
            $message = "✅ File berhasil diunggah tanpa restriksi: " . htmlspecialchars($filename);
        } else {
            $error = "Gagal memindahkan file ke direktori tujuan.";
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
      <div class="lab-badge vuln-badge">⚠️ VULNERABLE LEVEL 1</div>
      <h1>📁 LAB 1: File Upload Tanpa Validasi</h1>
      <p>Simulasi kerentanan unggah file tanpa restriksi sama sekali. Jenis file apa pun diizinkan masuk.</p>
    </div>
  </div>

  <!-- NAVIGATION SUB-LABS -->
  <div style="display:flex; gap:0.5rem; margin-bottom:2rem; flex-wrap:wrap;">
    <a href="<?= $base_url ?>/file-upload-1.php" class="btn btn-vuln btn-vuln-active">Level 1: No Validation</a>
    <a href="<?= $base_url ?>/file-upload-2.php" class="btn btn-outline">Level 2: Blacklist Filter</a>
    <a href="<?= $base_url ?>/file-upload-3.php" class="btn btn-outline">Level 3: Extension Whitelist</a>
    <a href="<?= $base_url ?>/file-upload-fixed.php" class="btn btn-fixed">Level 4: Secure Version</a>
  </div>

  <div class="lab-container">

    <!-- KONSEP -->
    <section class="lab-section">
      <h2>📖 Konsep</h2>
      <div class="concept-box">
        <p><strong>Unrestricted File Upload</strong> terjadi jika aplikasi mempercayai file dari pengguna tanpa memeriksa isinya, tipenya, atau namanya. Server langsung menyimpan file ke direktori yang bisa diakses publik (seperti `/public/uploads/`).</p>
        <p>Jika server mengizinkan file dinamis (seperti `.html` atau `.php` pada server PHP), penyerang dapat menjalankan kode jahat (*webshell*) untuk mengendalikan server.</p>
      </div>
    </section>

    <!-- FORM UPLOAD -->
    <section class="lab-section">
      <h2>🧪 Uji Coba Unggah</h2>
      <div class="vuln-box">
        <div class="vuln-label">⚠️ Endpoint: <code>POST /file-upload-1.php</code></div>

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
            <small class="form-hint text-red">⚠️ Bebas: Anda dapat mengunggah ekstensi apa pun termasuk .php, .html, .txt</small>
          </div>
          <button type="submit" class="btn btn-vuln-submit" id="btn-upload-vuln">📤 Upload File (Tanpa Validasi)</button>
        </form>

        <div class="webshell-demo">
          <h4>🎭 File Simulasi webshell:</h4>
          <p>Unduh file HTML simulasi ini untuk diuji:</p>
          <a href="<?= $base_url ?>/public/uploads/fake-webshell.html" class="btn btn-sm btn-outline" target="_blank">🔗 Lihat Fake Webshell Demo</a>
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

    <!-- DAMPAK / CARA FIX -->
    <div class="two-col-grid">
      <section class="lab-section">
        <h2>❌ Masalah Utama</h2>
        <div class="wrong-box">
          <ul class="check-list check-red">
            <li>Tidak ada penyaringan tipe konten ataupun ekstensi nama file.</li>
            <li>Nama file tidak diganti (potensi overwrite/timpa file sistem).</li>
            <li>File disimpan langsung di folder publik yang dapat diakses dari browser.</li>
          </ul>
        </div>
      </section>

      <section class="lab-section">
        <h2>💥 Dampak Serangan</h2>
        <div class="impact-box">
          <ul class="check-list check-orange">
            <li>Eksekusi kode jarak jauh (RCE) via webshell PHP/JSP.</li>
            <li>Stored Cross-Site Scripting (XSS) via file HTML/SVG.</li>
            <li>Serangan Denial of Service (DoS) dengan memenuhi penyimpanan server.</li>
          </ul>
        </div>
      </section>
    </div>

  </div>
</main>

<?php include('footer.php'); ?>

<script>
  document.getElementById('file-input').addEventListener('change', function() {
    const display = document.getElementById('file-display');
    display.textContent = this.files[0] ? this.files[0].name + ' (' + (this.files[0].size / 1024).toFixed(2) + ' KB)' : 'Belum ada file dipilih...';
  });
</script>
