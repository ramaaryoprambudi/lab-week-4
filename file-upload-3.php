<?php
include_once('config.php');
$title = "Lab 3: Bypass Ekstensi Gambar";

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
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png'];
        
        // ⚠️ VULNERABLE: Server memvalidasi ekstensi harus (.jpg, .jpeg, .png)
        // Tetapi mempercayai isi Content-Type header dari request tanpa validasi ekstensi asli
        // ATAU dalam kasus ini, kita cek jika ekstensinya valid di server.
        // Untuk melatih "MIME/Content-Type Bypass", filter di bawah mengecek $file['type'] (MIME type).
        // Developer salah mengira memvalidasi MIME type dari browser (misal $file['type'] === 'image/jpeg') sudah cukup aman,
        // padahal value tersebut bisa dimanipulasi dengan Burp Suite saat mengunggah berkas PHP!
        
        $mime_type = $file['type'];
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/jpg'];
        
        if (!in_array($mime_type, $allowed_mimes)) {
            $error = "Unggah ditolak: Hanya menerima berkas tipe gambar (image/jpeg, image/png)! MIME terdeteksi: " . htmlspecialchars($mime_type);
        } else {
            // Lolos pemeriksaan MIME yang dikirim browser, simpan berkas
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                chmod($target_path, 0644); // Pastikan berkas dapat dibaca oleh web server
                $message = "✅ File berhasil diunggah (MIME type terverifikasi): " . htmlspecialchars($filename);
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
      <div class="lab-badge vuln-badge">⚠️ VULNERABLE LEVEL 3</div>
      <h1>📁 LAB 3: Bypass Validasi MIME / Content-Type</h1>
      <p>Simulasi validasi tipe file yang hanya memeriksa tipe MIME dari header request client.</p>
    </div>
  </div>

  <!-- NAVIGATION SUB-LABS -->
  <div style="display:flex; gap:0.5rem; margin-bottom:2rem; flex-wrap:wrap;">
    <a href="<?= $base_url ?>/file-upload-1.php" class="btn btn-outline">Level 1: No Validation</a>
    <a href="<?= $base_url ?>/file-upload-2.php" class="btn btn-outline">Level 2: Blacklist Filter</a>
    <a href="<?= $base_url ?>/file-upload-3.php" class="btn btn-vuln btn-vuln-active">Level 3: Extension Whitelist</a>
    <a href="<?= $base_url ?>/file-upload-fixed.php" class="btn btn-fixed">Level 4: Secure Version</a>
  </div>

  <div class="lab-container">

    <!-- KONSEP -->
    <section class="lab-section">
      <h2>📖 Konsep MIME Type / Content-Type Bypass</h2>
      <div class="concept-box">
        <p>Banyak developer membatasi tipe berkas hanya dengan memeriksa parameter <code>Content-Type</code> (atau variabel <code>$_FILES['file']['type']</code> di PHP) yang dikirimkan oleh browser client.</p>
        <p>Karena header request dikirim dari pihak client, data tersebut **dapat dimanipulasi sepenuhnya** menggunakan proxy seperti **Burp Suite**. Penyerang dapat mengunggah berkas <code>shell.php</code>, tetapi mengubah header request-nya menjadi <code>Content-Type: image/jpeg</code> agar lolos filter.</p>
      </div>
    </section>

    <!-- FORM UPLOAD -->
    <section class="lab-section">
      <h2>🧪 Latihan Bypass dengan Burp Suite</h2>
      <div class="vuln-box">
        <div class="vuln-label">⚠️ Endpoint: <code>POST /file-upload-3.php</code></div>

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
              <!-- Accept gambar saja di browser client -->
              <input type="file" name="file" id="file-input" class="file-input" />
              <div class="file-input-display" id="file-display">Belum ada file dipilih...</div>
            </div>
            <small class="form-hint text-red">⚠️ Whitelist aktif: Server hanya memverifikasi jika berkas memiliki tipe MIME gambar (<code>image/jpeg</code> atau <code>image/png</code>).</small>
          </div>
          <button type="submit" class="btn btn-vuln-submit" id="btn-upload-vuln">📤 Upload File (Level 3)</button>
        </form>

        <!-- TIPS CHALLENGE -->
        <div class="webshell-demo" style="border-color:var(--accent-orange); background:rgba(255,123,53,0.03);">
          <h4 style="color:var(--accent-orange)">🦊 Panduan Bypass MIME / Content-Type dengan Burp Suite:</h4>
          <div class="steps-list" style="margin-top:0.5rem;">
            <div class="step-item">
              <span class="step-num">1</span>
              <div>
                <strong>Persiapan Berkas:</strong>
                <p>Siapkan berkas webshell PHP Anda asli dengan nama <code>backdoor.php</code>.</p>
              </div>
            </div>
            <div class="step-item">
              <span class="step-num">2</span>
              <div>
                <strong>Intercept Upload Request:</strong>
                <p>Aktifkan Burp Suite Intercept ON, lalu klik unggah file <code>backdoor.php</code>.</p>
              </div>
            </div>
            <div class="step-item">
              <span class="step-num">3</span>
              <div>
                <strong>Ubah Content-Type di dalam Burp:</strong>
                <p>Di jendela intercept Burp Suite, temukan header bagian file:</p>
                <pre><code>Content-Disposition: form-data; name="file"; filename="backdoor.php"
Content-Type: application/octet-stream   <-- UBAH INI</code></pre>
                <p>Ubah nilai tersebut menjadi: <code>Content-Type: image/jpeg</code>. Lalu klik Forward!</p>
              </div>
            </div>
          </div>
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
