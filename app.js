require('dotenv').config();
const express = require('express');
const path = require('path');
const fs = require('fs');

const app = express();
const PORT = process.env.PORT || 3000;
const HOST = '0.0.0.0'; // ✅ Bind ke semua interface agar bisa diakses via Burp proxy

// Setup EJS
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Static files
app.use(express.static(path.join(__dirname, 'public')));
app.use(express.urlencoded({ extended: true }));
app.use(express.json());

// =============================================
// ⚠️ VULNERABLE MIDDLEWARE – HTTP Headers Bocor
// Ini mensimulasikan server yang salah konfigurasi
// dan membocorkan informasi lewat HTTP headers
// =============================================
app.use((req, res, next) => {
  // ⚠️ VULNERABLE: Membocorkan teknologi stack palsu (mislabeling juga bahaya)
  res.setHeader('Server', 'Apache/2.4.52 (Ubuntu)');
  res.setHeader('X-Powered-By', 'PHP/7.4.3');

  // ⚠️ VULNERABLE: Header internal yang tidak boleh ada di production
  res.setHeader('X-Debug-Token', 'lab-debug-abc123');
  res.setHeader('X-Internal-Build', 'v1.0.0-dev');
  res.setHeader('X-App-Environment', 'development');
  res.setHeader('X-Admin-Panel', '/admin');
  res.setHeader('X-DB-Host', 'localhost:5432');
  res.setHeader('X-Backup-Path', '/backup');

  // ⚠️ VULNERABLE: Cookie tanpa HttpOnly, Secure, SameSite
  // (hanya di-set sekali saat pertama kali request)
  if (!req.headers.cookie || !req.headers.cookie.includes('session=')) {
    res.setHeader('Set-Cookie', [
      'session=student_sess_abc123_dummy; Path=/',  // ⚠️ No HttpOnly, No Secure
      'role=user; Path=/',                           // ⚠️ Sensitive data in cookie
      'debug=true; Path=/'                           // ⚠️ Debug flag exposed
    ]);
  }

  next();
});

const isVercel = process.env.VERCEL === '1' || !!process.env.VERCEL;

// Pastikan folder uploads ada
const uploadsDir = isVercel ? '/tmp/uploads' : path.join(__dirname, 'public', 'uploads');
if (!fs.existsSync(uploadsDir)) fs.mkdirSync(uploadsDir, { recursive: true });

const fixedUploadsDir = isVercel ? '/tmp/secure-uploads' : path.join(__dirname, 'data', 'secure-uploads');
if (!fs.existsSync(fixedUploadsDir)) fs.mkdirSync(fixedUploadsDir, { recursive: true });

if (isVercel) {
  // Sajikan folder uploads statis di Vercel dari /tmp/uploads
  app.use('/uploads', express.static('/tmp/uploads'));
}

// Routes
const fileUploadRoutes          = require('./routes/fileUpload');
const informationDisclosureRoutes = require('./routes/informationDisclosure');
const raceConditionRoutes       = require('./routes/raceCondition');
const reconRoutes               = require('./routes/recon');

app.use('/lab/file-upload', fileUploadRoutes);
app.use('/lab/information-disclosure', informationDisclosureRoutes);
app.use('/lab/race-condition', raceConditionRoutes);
app.use('/', reconRoutes); // Hidden endpoints untuk dirsearch (di root)

// =============================================
// robots.txt – ⚠️ VULNERABLE: Membocorkan path sensitif
// =============================================
app.get('/robots.txt', (req, res) => {
  res.type('text/plain');
  res.send(`# robots.txt – CyberLAB Week 4
# ⚠️ WARNING: File ini membocorkan path internal kepada mesin pencari
# Attacker sering membaca robots.txt untuk reconnaissance!

User-agent: *
Disallow: /admin
Disallow: /admin/dashboard
Disallow: /backup
Disallow: /config.json
Disallow: /api/keys
Disallow: /api/users
Disallow: /api/v1/debug
Disallow: /.env
Disallow: /.git
Disallow: /console
Disallow: /server-status
Disallow: /lab/information-disclosure/debug
Disallow: /data/

Allow: /
`);
});

// Halaman Utama
app.get('/', (req, res) => {
  res.render('index', {
    title: 'Cyber Security LAB – Week 4',
    page: 'home'
  });
});

// 404 Handler
app.use((req, res) => {
  res.status(404).render('error', {
    title: '404 - Halaman Tidak Ditemukan',
    message: 'Halaman yang kamu cari tidak ada.',
    code: 404,
    page: 'error'
  });
});

// Error Handler Global (versi aman — tidak menampilkan stack trace)
app.use((err, req, res, next) => {
  console.error('[SERVER ERROR]', err.stack);
  res.status(500).render('error', {
    title: '500 - Server Error',
    message: 'Terjadi kesalahan pada server.',
    code: 500,
    page: 'error'
  });
});

if (!isVercel) {
  const server = app.listen(PORT, HOST, () => {
    // Tampilkan IP lokal
    const { networkInterfaces } = require('os');
    const nets = networkInterfaces();
    let localIp = 'localhost';
    for (const name of Object.keys(nets)) {
      for (const net of nets[name]) {
        if (net.family === 'IPv4' && !net.internal) {
          localIp = net.address;
          break;
        }
      }
    }

    console.log(`\n🔐 Cyber Security LAB - Week 4`);
    console.log(`🚀 Server berjalan di:`);
    console.log(`   → http://localhost:${PORT}        (local)`);
    console.log(`   → http://${localIp}:${PORT}  (network – untuk Burp proxy)`);
    console.log(`\n📋 Daftar Lab:`);
    console.log(`   📁 File Upload       → http://localhost:${PORT}/lab/file-upload`);
    console.log(`   🔍 Info Disclosure   → http://localhost:${PORT}/lab/information-disclosure`);
    console.log(`   ⚡ Race Condition    → http://localhost:${PORT}/lab/race-condition`);
    console.log(`   🕵️  Recon Lab         → http://localhost:${PORT}/lab/recon`);
    console.log(`\n🔧 Tools:`);
    console.log(`   🤖 robots.txt        → http://localhost:${PORT}/robots.txt`);
    console.log(`   🔑 Hidden Admin      → http://localhost:${PORT}/admin`);
    console.log(`   🌐 Set Burp proxy ke → http://${localIp}:${PORT}`);
    console.log(`\n⚠️  Hanya untuk edukasi lokal!\n`);
  });

  // ✅ Handle error EADDRINUSE dengan pesan yang jelas
  server.on('error', (err) => {
    if (err.code === 'EADDRINUSE') {
      console.error(`\n❌ ERROR: Port ${PORT} sudah digunakan oleh proses lain!\n`);
      console.error(`   Jalankan perintah ini untuk menghentikan proses lama:\n`);
      console.error(`   lsof -ti:${PORT} | xargs kill -9\n`);
      console.error(`   Lalu jalankan ulang: npm run dev\n`);
      process.exit(1);
    } else {
      console.error('[SERVER ERROR]', err);
      process.exit(1);
    }
  });
}

module.exports = app;

