<?php
$title = "Dashboard";
include('header.php');
?>

<main class="main-content">

  <!-- HERO SECTION -->
  <section class="hero">
    <div class="hero-badge">🎓 Pertemuan 4 &nbsp;–&nbsp; Advanced Attack &amp; Simulation (PHP Native)</div>
    <h1 class="hero-title">Cyber Security <span class="highlight">LAB</span></h1>
    <p class="hero-subtitle">Pelajari, simulasikan, dan pahami kerentanan web secara aman di lingkungan lab lokal berbasis PHP.</p>
    <div class="hero-tags">
      <span class="tag tag-red">File Upload Vulnerability</span>
      <span class="tag tag-orange">Information Disclosure</span>
      <span class="tag tag-yellow">Race Condition</span>
    </div>
  </section>

  <!-- LAB CARDS -->
  <section class="labs-section">
    <h2 class="section-title">🧪 Pilih Lab</h2>
    <p class="section-desc">Setiap lab memiliki versi <strong class="text-red">Vulnerable</strong> (sengaja rentan untuk simulasi) dan <strong class="text-green">Fixed</strong> (versi aman sebagai solusi).</p>

    <div class="lab-grid">

      <!-- LAB 1 -->
      <div class="lab-card">
        <div class="lab-card-header vuln-header">
          <div class="lab-number">LAB 01</div>
          <div class="lab-icon">📁</div>
        </div>
        <div class="lab-card-body">
          <h3>File Upload Vulnerability</h3>
          <p>Uji coba 3 tingkatan filter unggahan file dari tingkat tanpa proteksi, bypass blacklist, hingga bypass validasi MIME tipe header.</p>
          <ul class="lab-topics">
            <li>Level 1: Tanpa validasi apapun</li>
            <li>Level 2: Blacklist ekstensi (.pHp, .phtml)</li>
            <li>Level 3: Whitelist & Content-Type bypass</li>
            <li>Level 4: Whitelist komprehensif & UUID rename</li>
          </ul>
          <div class="lab-buttons" style="display:flex; flex-direction:column; gap:0.5rem; width:100%;">
            <div style="display:flex; gap:0.5rem; width:100%;">
              <a href="/file-upload-1.php" class="btn btn-vuln" style="flex:1; text-align:center; padding:0.4rem 0.5rem; font-size:0.75rem;">Level 1</a>
              <a href="/file-upload-2.php" class="btn btn-vuln" style="flex:1; text-align:center; padding:0.4rem 0.5rem; font-size:0.75rem;">Level 2</a>
              <a href="/file-upload-3.php" class="btn btn-vuln" style="flex:1; text-align:center; padding:0.4rem 0.5rem; font-size:0.75rem;">Level 3</a>
            </div>
            <a href="/file-upload-fixed.php" class="btn btn-fixed" style="width:100%; text-align:center;">✅ Level 4 (Secure)</a>
          </div>
        </div>
      </div>

      <!-- LAB 2 -->
      <div class="lab-card">
        <div class="lab-card-header info-header">
          <div class="lab-number">LAB 02</div>
          <div class="lab-icon">🔍</div>
        </div>
        <div class="lab-card-body">
          <h3>Information Disclosure</h3>
          <p>Aplikasi membocorkan data sensitif seperti stack trace, konfigurasi server, secret key, dan detail internal.</p>
          <ul class="lab-topics">
            <li>Stack trace exposure</li>
            <li>Debug mode di production</li>
            <li>Filter field API response</li>
            <li>Perlindungan secret &amp; config</li>
          </ul>
          <div class="lab-buttons">
            <a href="/info-disclosure.php" class="btn btn-vuln" id="btn-lab2-vuln">⚠️ Vulnerable</a>
            <a href="/info-disclosure-fixed.php" class="btn btn-fixed" id="btn-lab2-fixed">✅ Fixed</a>
          </div>
        </div>
      </div>

      <!-- LAB 3 -->
      <div class="lab-card">
        <div class="lab-card-header race-header">
          <div class="lab-number">LAB 03</div>
          <div class="lab-icon">⚡</div>
        </div>
        <div class="lab-card-body">
          <h3>Race Condition</h3>
          <p>Request bersamaan dikirim sebelum status diperbarui — voucher sekali pakai bisa digunakan berkali-kali.</p>
          <ul class="lab-topics">
            <li>In-memory locking</li>
            <li>Simulasi update konkuren</li>
            <li>File database JSON</li>
            <li>Burp Intruder / Turbo Intruder</li>
          </ul>
          <div class="lab-buttons">
            <a href="/race-condition.php" class="btn btn-vuln" id="btn-lab3-vuln">⚠️ Vulnerable</a>
            <a href="/race-condition-fixed.php" class="btn btn-fixed" id="btn-lab3-fixed">✅ Fixed</a>
          </div>
        </div>
      </div>

      <!-- LAB 4 – RECON -->
      <div class="lab-card" style="border-color:rgba(94,53,177,0.3);">
        <div class="lab-card-header" style="background:linear-gradient(135deg,#0d0a1a,#1a1030);">
          <div class="lab-number">LAB 04</div>
          <div class="lab-icon">🕵️</div>
        </div>
        <div class="lab-card-body">
          <h3>Recon & Hidden Endpoints</h3>
          <p>Temukan endpoint tersembunyi menggunakan dirsearch/gobuster, dan analisis HTTP headers berbahaya via Burp Suite.</p>
          <ul class="lab-topics">
            <li>dirsearch / gobuster / ffuf</li>
            <li>robots.txt analysis</li>
            <li>HTTP header analysis (Burp)</li>
            <li>Cookie security flags</li>
          </ul>
          <div class="lab-buttons">
            <a href="/recon.php" class="btn" style="background:rgba(94,53,177,0.15);color:#b39ddb;border:1px solid rgba(94,53,177,0.4);" id="btn-lab4-recon">🕵️ Buka Lab Recon</a>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- INFO SECTION -->
  <section class="info-section">
    <div class="info-box">
      <h3>📚 Tentang Lab Ini</h3>
      <p>Lab ini adalah bagian dari materi <strong>Cyber Security – Pertemuan 4</strong> yang membahas simulasi serangan lanjutan dan cara mitigasinya. Semua data di lab ini adalah <strong>dummy/palsu</strong> dan hanya untuk tujuan edukasi.</p>
    </div>
    <div class="info-box info-box-warning">
      <h3>🚫 Yang Tidak Boleh Dilakukan</h3>
      <ul>
        <li>Jangan deploy ke internet atau server publik</li>
        <li>Jangan gunakan data asli (password, token, dll)</li>
        <li>Jangan gunakan teknik ini di sistem milik orang lain</li>
        <li>Jangan buat atau jalankan payload berbahaya nyata</li>
      </ul>
    </div>
  </section>

</main>

<?php include('footer.php'); ?>
