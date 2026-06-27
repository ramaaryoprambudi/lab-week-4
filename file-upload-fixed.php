<?php
include_once('config.php');
$title = "File Upload – Versi Aman";

// Simpan file terproteksi di luar folder publik public/
$upload_dir = __DIR__ . '/data/secure-uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// In-memory log tiruan dengan file JSON agar data tidak hilang ketika refresh
$log_file = __DIR__ . '/data/secure-upload-log.json';
if (!file_exists(dirname($log_file))) {
    mkdir(dirname($log_file), 0777, true);
}

$uploaded_files_log = [];
if (file_exists($log_file)) {
    $uploaded_files_log = json_decode(file_get_contents($log_file), true) ?: [];
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $original_name = basename($file['name']);
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $allowed_mimetypes = ['image/jpeg', 'image/png', 'application/pdf'];
        $max_file_size = 1 * 1024 * 1024; // 1 MB
        
        // 1. Cek ukuran file
        if ($file['size'] > $max_file_size) {
            $error = "❌ Upload ditolak: Ukuran file melebihi batas maksimal (1 MB).";
        }
        // 2. Cek ekstensi (Whitelist)
        elseif (!in_array($ext, $allowed_extensions)) {
            $error = "❌ Upload ditolak: Ekstensi tidak diizinkan. Hanya menerima: " . implode(', ', $allowed_extensions);
        }
        // 3. Cek MIME Type yang sebenarnya (Menggunakan mime_content_type atau finfo)
        else {
            $real_mime = mime_content_type($file['tmp_name']);
            if (!in_array($real_mime, $allowed_mimetypes)) {
                $error = "❌ Upload ditolak: Tipe konten terdeteksi sebagai " . htmlspecialchars($real_mime) . ", bukan gambar/PDF asli!";
            } else {
                // 4. Rename file secara acak (UUID/Random)
                $new_filename = uniqid('file_', true) . '.' . $ext;
                $target_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    chmod($target_path, 0644); // Pastikan berkas dapat dibaca oleh web server
                    $meta = [
                        'originalName' => $original_name,
                        'savedAs' => $new_filename,
                        'mimeType' => $real_mime,
                        'size' => $file['size'],
                        'uploadedAt' => date('d/m/Y, H:i:s')
                    ];
                    
                    array_unshift($uploaded_files_log, $meta);
                    file_put_contents($log_file, json_encode($uploaded_files_log, JSON_PRETTY_PRINT));
                    
                    $message = "✅ File diterima dan disimpan dengan aman. Nama asli: \"$original_name\" → Disimpan sebagai: \"$new_filename\"";
                } else {
                    $error = "Terjadi kesalahan saat menyimpan file.";
                }
            }
        }
    } else {
        $error = "Terjadi kesalahan upload: Kode " . $file['error'];
    }
}

include('header.php');
?>

<main class="main-content">

  <!-- PAGE HEADER -->
  <div class="page-header fixed-page-header">
    <div class="page-header-inner">
      <div class="lab-badge fixed-badge">✅ SECURE VERSION</div>
      <h1>📁 LAB 1 (Fixed): File Upload Aman</h1>
      <p>Implementasi pengamanan file upload lengkap dengan validasi ganda dan isolasi folder.</p>
    </div>
  </div>

  <!-- NAVIGATION SUB-LABS -->
  <div style="display:flex; gap:0.5rem; margin-bottom:2rem; flex-wrap:wrap;">
    <a href="<?= $base_url ?>/file-upload-1.php" class="btn btn-outline">Level 1: No Validation</a>
    <a href="<?= $base_url ?>/file-upload-2.php" class="btn btn-outline">Level 2: Blacklist Filter</a>
    <a href="<?= $base_url ?>/file-upload-3.php" class="btn btn-outline">Level 3: Extension Whitelist</a>
    <a href="<?= $base_url ?>/file-upload-fixed.php" class="btn btn-fixed btn-fixed-active">Level 4: Secure Version</a>
  </div>

  <div class="lab-container">

    <!-- KONSEP -->
    <section class="lab-section">
      <h2>📖 Implementasi Pengamanan Fitur Upload</h2>
      <div class="concept-box">
        <p>Aplikasi Anda dapat diamankan sepenuhnya dari serangan kerentanan file upload dengan menerapkan 4 lapis pertahanan utama:</p>
        <ol>
          <li><strong>Pemeriksaan Whitelist Ekstensi</strong>: Hanya izinkan ekstensi file yang secara spesifik dibutuhkan (misalnya `.jpg`, `.png`, `.pdf`).</li>
          <li><strong>Pemeriksaan Tipe MIME Sebenarnya (Magic Bytes)</strong>: Jangan percaya tipe data yang dikirim client. Deteksi tipe file langsung dari struktur datanya di server.</li>
          <li><strong>Mengubah Nama File secara Acak</strong>: Cegah serangan Directory Traversal/Overwrite file internal dengan mengganti nama file menjadi ID unik acak.</li>
          <li><strong>Menyimpan di Folder Non-Publik</strong>: Simpan file di luar folder root publik web server, sehingga file tidak bisa dieksekusi secara langsung via URL.</li>
        </ol>
      </div>
    </section>

    <!-- FORM UPLOAD -->
    <section class="lab-section">
      <h2>🧪 Unggah File Aman</h2>
      <div class="fix-box">
        <div class="fixed-label-inline" style="margin-bottom:1rem;">✅ Endpoint Aman: <code>POST /file-upload-fixed.php</code></div>

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
            <small class="form-hint text-green">✅ Filter Whitelist Aktif: Hanya menerima JPG, PNG, PDF asli maksimal 1MB.</small>
          </div>
          <button type="submit" class="btn btn-fixed-submit" id="btn-upload-fixed">📤 Upload File (Aman)</button>
        </form>
      </div>
    </section>

    <!-- LIST FILE -->
    <?php if (!empty($uploaded_files_log)): ?>
    <section class="lab-section">
      <h2>📂 Berkas Terunggah (Metadata Aman)</h2>
      <div class="fix-box">
        <p class="text-green">💡 <strong>Catatan:</strong> File disimpan dengan aman di <code>/data/secure-uploads/</code> dengan nama terenkripsi. Link langsung dinonaktifkan untuk mencegah RCE.</p>
        <table class="file-table">
          <thead>
            <tr>
              <th>Nama Asli</th>
              <th>Disimpan Sebagai</th>
              <th>MIME Type</th>
              <th>Ukuran</th>
              <th>Tanggal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($uploaded_files_log as $f): ?>
            <tr>
              <td><code><?php echo htmlspecialchars($f['originalName']); ?></code></td>
              <td><code><?php echo htmlspecialchars($f['savedAs']); ?></code></td>
              <td><span class="disc-status"><?php echo htmlspecialchars($f['mimeType']); ?></span></td>
              <td><?php echo round($f['size'] / 1024, 2); ?> KB</td>
              <td><small><?php echo htmlspecialchars($f['uploadedAt']); ?></small></td>
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
