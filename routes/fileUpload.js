const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const { v4: uuidv4 } = require('uuid');

const isVercel = process.env.VERCEL === '1' || !!process.env.VERCEL;

// Multer Storage Helper
const getStorage = (isFixed) => {
  return multer.diskStorage({
    destination: (req, file, cb) => {
      const dest = isVercel
        ? (isFixed ? '/tmp/secure-uploads' : '/tmp/uploads')
        : (isFixed ? path.join(__dirname, '..', 'data', 'secure-uploads') : path.join(__dirname, '..', 'public', 'uploads'));
      
      if (!fs.existsSync(dest)) fs.mkdirSync(dest, { recursive: true });
      cb(null, dest);
    },
    filename: (req, file, cb) => {
      if (isFixed) {
        const ext = path.extname(file.originalname).toLowerCase();
        cb(null, `${uuidv4()}${ext}`);
      } else {
        // Rentan: Gunakan nama asli dari pengguna
        cb(null, file.originalname);
      }
    }
  });
};

// =============================================
// KONFIGURASI MULTER UNTUK TIAP LAB
// =============================================

// LAB 1: Tanpa Validasi Apapun
const uploadLab1 = multer({ storage: getStorage(false) });

// LAB 2: Blacklist Ekstensi Lemah (Hanya blokir ".php" literal lowercase)
const blacklistFilter = (req, file, cb) => {
  const filename = file.originalname;
  if (filename.endsWith('.php')) {
    return cb(new Error('Unggah ditolak: File .php diblokir oleh sistem blacklist!'), false);
  }
  cb(null, true);
};
const uploadLab2 = multer({
  storage: getStorage(false),
  fileFilter: blacklistFilter
});

// LAB 3: Validasi Ekstensi dengan Whitelist, tanpa mencocokkan MIME type yang sesungguhnya
// Memvalidasi Content-Type header dari request tanpa validasi ekstensi sesungguhnya,
// ATAU sebaliknya. Di sini kita memvalidasi ekstensi harus (.jpg, .jpeg, .png)
// tetapi membiarkan request lolos jika MIME type di header dipalsukan.
const weakExtensionMimeFilter = (req, file, cb) => {
  const ext = path.extname(file.originalname).toLowerCase();
  const allowedExts = ['.jpg', '.jpeg', '.png'];
  
  if (!allowedExts.includes(ext)) {
    return cb(new Error('Unggah ditolak: Hanya menerima ekstensi gambar (.jpg, .jpeg, .png)!'), false);
  }
  cb(null, true);
};
const uploadLab3 = multer({
  storage: getStorage(false),
  fileFilter: weakExtensionMimeFilter
});

// FIXED VERSION: Validasi Lengkap (Whitelist Ext + Whitelist MIME + Size Limit)
const ALLOWED_EXTENSIONS = ['.jpg', '.jpeg', '.png', '.pdf'];
const ALLOWED_MIMETYPES = ['image/jpeg', 'image/png', 'application/pdf'];
const MAX_FILE_SIZE = 1 * 1024 * 1024; // 1 MB

const fixedFileFilter = (req, file, cb) => {
  const ext = path.extname(file.originalname).toLowerCase();
  const mimeType = file.mimetype;

  if (!ALLOWED_EXTENSIONS.includes(ext)) {
    return cb(new Error(`Extension tidak diizinkan. Hanya: ${ALLOWED_EXTENSIONS.join(', ')}`), false);
  }
  if (!ALLOWED_MIMETYPES.includes(mimeType)) {
    return cb(new Error(`MIME type tidak diizinkan: ${mimeType}`), false);
  }
  cb(null, true);
};

const uploadFixed = multer({
  storage: getStorage(true),
  fileFilter: fixedFileFilter,
  limits: { fileSize: MAX_FILE_SIZE }
});

// Log metadata in-memory
let fixedUploadedFilesLog = [];

// Helper untuk membaca file uploads
function getUploadedFiles() {
  const uploadsDir = isVercel ? '/tmp/uploads' : path.join(__dirname, '..', 'public', 'uploads');
  if (!fs.existsSync(uploadsDir)) fs.mkdirSync(uploadsDir, { recursive: true });
  return fs.readdirSync(uploadsDir).map(name => ({
    name,
    url: `/uploads/${name}`,
    size: fs.statSync(path.join(uploadsDir, name)).size
  }));
}

// Redirect base path ke Lab 1
router.get('/', (req, res) => {
  res.redirect('/lab/file-upload/1');
});

// =============================================
// ROUTES - LAB 1 (Tanpa Validasi)
// =============================================
router.get('/1', (req, res) => {
  res.render('file-upload-1', {
    title: 'Lab 1: Tanpa Validasi',
    page: 'file-upload-1',
    files: getUploadedFiles(),
    message: null,
    error: null
  });
});

router.post('/1/upload', (req, res) => {
  uploadLab1.single('file')(req, res, (err) => {
    const files = getUploadedFiles();
    if (err) {
      return res.render('file-upload-1', {
        title: 'Lab 1: Tanpa Validasi',
        page: 'file-upload-1',
        files,
        message: null,
        error: `Unggah gagal: ${err.message}`
      });
    }
    if (!req.file) {
      return res.render('file-upload-1', {
        title: 'Lab 1: Tanpa Validasi',
        page: 'file-upload-1',
        files,
        message: null,
        error: 'Pilih file terlebih dahulu!'
      });
    }
    res.render('file-upload-1', {
      title: 'Lab 1: Tanpa Validasi',
      page: 'file-upload-1',
      files: getUploadedFiles(),
      message: `✅ File berhasil diunggah tanpa restriksi: ${req.file.originalname}`,
      error: null
    });
  });
});

// =============================================
// ROUTES - LAB 2 (Blacklist Ekstensi)
// =============================================
router.get('/2', (req, res) => {
  res.render('file-upload-2', {
    title: 'Lab 2: Filter Blacklist Ekstensi',
    page: 'file-upload-2',
    files: getUploadedFiles(),
    message: null,
    error: null
  });
});

router.post('/2/upload', (req, res) => {
  uploadLab2.single('file')(req, res, (err) => {
    const files = getUploadedFiles();
    if (err) {
      return res.render('file-upload-2', {
        title: 'Lab 2: Filter Blacklist Ekstensi',
        page: 'file-upload-2',
        files,
        message: null,
        error: `Unggah ditolak: ${err.message}`
      });
    }
    if (!req.file) {
      return res.render('file-upload-2', {
        title: 'Lab 2: Filter Blacklist Ekstensi',
        page: 'file-upload-2',
        files,
        message: null,
        error: 'Pilih file terlebih dahulu!'
      });
    }
    res.render('file-upload-2', {
      title: 'Lab 2: Filter Blacklist Ekstensi',
      page: 'file-upload-2',
      files: getUploadedFiles(),
      message: `✅ File lolos filter blacklist: ${req.file.originalname}`,
      error: null
    });
  });
});

// =============================================
// ROUTES - LAB 3 (MIME Type / Content-Type Bypass)
// =============================================
router.get('/3', (req, res) => {
  res.render('file-upload-3', {
    title: 'Lab 3: Bypass Validasi Ekstensi Gambar',
    page: 'file-upload-3',
    files: getUploadedFiles(),
    message: null,
    error: null
  });
});

router.post('/3/upload', (req, res) => {
  uploadLab3.single('file')(req, res, (err) => {
    const files = getUploadedFiles();
    if (err) {
      return res.render('file-upload-3', {
        title: 'Lab 3: Bypass Validasi Ekstensi Gambar',
        page: 'file-upload-3',
        files,
        message: null,
        error: `Unggah ditolak: ${err.message}`
      });
    }
    if (!req.file) {
      return res.render('file-upload-3', {
        title: 'Lab 3: Bypass Validasi Ekstensi Gambar',
        page: 'file-upload-3',
        files,
        message: null,
        error: 'Pilih file terlebih dahulu!'
      });
    }
    res.render('file-upload-3', {
      title: 'Lab 3: Bypass Validasi Ekstensi Gambar',
      page: 'file-upload-3',
      files: getUploadedFiles(),
      message: `✅ File berhasil diunggah (Ekstensi Gambar Terpenuhi): ${req.file.originalname}`,
      error: null
    });
  });
});

// =============================================
// ROUTES - FIXED (Versi Aman)
// =============================================
router.get('/fixed', (req, res) => {
  res.render('fixed-file-upload', {
    title: 'File Upload – Versi Aman (Fixed)',
    page: 'fixed-file-upload',
    files: fixedUploadedFilesLog,
    message: null,
    error: null
  });
});

router.post('/fixed/upload', (req, res) => {
  uploadFixed.single('file')(req, res, (err) => {
    if (err) {
      let errMsg = err.message;
      if (err.code === 'LIMIT_FILE_SIZE') {
        errMsg = 'Ukuran file melebihi batas maksimal (1 MB).';
      }
      return res.render('fixed-file-upload', {
        title: 'File Upload – Versi Aman (Fixed)',
        page: 'fixed-file-upload',
        files: fixedUploadedFilesLog,
        message: null,
        error: `❌ Upload ditolak: ${errMsg}`
      });
    }
    if (!req.file) {
      return res.render('fixed-file-upload', {
        title: 'File Upload – Versi Aman (Fixed)',
        page: 'fixed-file-upload',
        files: fixedUploadedFilesLog,
        message: null,
        error: 'Pilih file terlebih dahulu!'
      });
    }

    const meta = {
      originalName: req.file.originalname,
      savedAs: req.file.filename,
      mimeType: req.file.mimetype,
      size: req.file.size,
      uploadedAt: new Date().toLocaleString('id-ID')
    };
    fixedUploadedFilesLog.unshift(meta);

    res.render('fixed-file-upload', {
      title: 'File Upload – Versi Aman (Fixed)',
      page: 'fixed-file-upload',
      files: fixedUploadedFilesLog,
      message: `✅ File diterima dan disimpan dengan aman. Nama asli: "${req.file.originalname}" → Disimpan sebagai: "${req.file.filename}"`,
      error: null
    });
  });
});

module.exports = router;
