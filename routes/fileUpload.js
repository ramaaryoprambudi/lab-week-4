const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const { v4: uuidv4 } = require('uuid');

const isVercel = process.env.VERCEL === '1' || !!process.env.VERCEL;

// =============================================
// VULNERABLE VERSION - Tidak ada validasi
// =============================================
const vulnerableStorage = multer.diskStorage({
  destination: (req, file, cb) => {
    const dest = isVercel ? '/tmp/uploads' : path.join(__dirname, '..', 'public', 'uploads');
    if (!fs.existsSync(dest)) fs.mkdirSync(dest, { recursive: true });
    cb(null, dest);
  },
  filename: (req, file, cb) => {
    // ⚠️ VULNERABLE: Pakai nama asli file dari user
    cb(null, file.originalname);
  }
});

// ⚠️ VULNERABLE: Tidak ada fileFilter, tidak ada limit ukuran
const vulnerableUpload = multer({ storage: vulnerableStorage });

// =============================================
// FIXED VERSION - Dengan validasi lengkap
// =============================================
const ALLOWED_EXTENSIONS = ['.jpg', '.jpeg', '.png', '.pdf'];
const ALLOWED_MIMETYPES = ['image/jpeg', 'image/png', 'application/pdf'];
const MAX_FILE_SIZE = 1 * 1024 * 1024; // 1 MB

const fixedStorage = multer.diskStorage({
  destination: (req, file, cb) => {
    const dest = isVercel ? '/tmp/secure-uploads' : path.join(__dirname, '..', 'data', 'secure-uploads');
    if (!fs.existsSync(dest)) fs.mkdirSync(dest, { recursive: true });
    cb(null, dest);
  },
  filename: (req, file, cb) => {
    // ✅ FIXED: Rename dengan UUID, tidak memakai nama asli
    const ext = path.extname(file.originalname).toLowerCase();
    cb(null, `${uuidv4()}${ext}`);
  }
});

const fixedFileFilter = (req, file, cb) => {
  const ext = path.extname(file.originalname).toLowerCase();
  const mimeType = file.mimetype;

  // ✅ FIXED: Cek extension whitelist
  if (!ALLOWED_EXTENSIONS.includes(ext)) {
    return cb(new Error(`Extension tidak diizinkan. Hanya: ${ALLOWED_EXTENSIONS.join(', ')}`), false);
  }

  // ✅ FIXED: Cek MIME type whitelist
  if (!ALLOWED_MIMETYPES.includes(mimeType)) {
    return cb(new Error(`MIME type tidak diizinkan: ${mimeType}`), false);
  }

  cb(null, true);
};

// ✅ FIXED: Ada limit ukuran file
const fixedUpload = multer({
  storage: fixedStorage,
  fileFilter: fixedFileFilter,
  limits: { fileSize: MAX_FILE_SIZE }
});

// --- Metadata file log (in-memory untuk simplicity) ---
let uploadedFilesLog = [];
let fixedUploadedFilesLog = [];

// =============================================
// ROUTES - VULNERABLE
// =============================================

// GET /lab/file-upload
router.get('/', (req, res) => {
  // Baca file dari folder uploads
  const uploadsDir = isVercel ? '/tmp/uploads' : path.join(__dirname, '..', 'public', 'uploads');
  if (!fs.existsSync(uploadsDir)) fs.mkdirSync(uploadsDir, { recursive: true });
  let files = [];
  if (fs.existsSync(uploadsDir)) {
    files = fs.readdirSync(uploadsDir).map(name => ({
      name,
      url: `/uploads/${name}`,
      size: fs.statSync(path.join(uploadsDir, name)).size
    }));
  }

  res.render('file-upload', {
    title: 'LAB 1: File Upload Vulnerability',
    page: 'file-upload',
    files,
    message: null,
    error: null
  });
});

// POST /lab/file-upload/upload
router.post('/upload', (req, res) => {
  vulnerableUpload.single('file')(req, res, (err) => {
    const uploadsDir = isVercel ? '/tmp/uploads' : path.join(__dirname, '..', 'public', 'uploads');
    if (!fs.existsSync(uploadsDir)) fs.mkdirSync(uploadsDir, { recursive: true });
    let files = [];
    if (fs.existsSync(uploadsDir)) {
      files = fs.readdirSync(uploadsDir).map(name => ({
        name,
        url: `/uploads/${name}`,
        size: fs.statSync(path.join(uploadsDir, name)).size
      }));
    }

    if (err) {
      return res.render('file-upload', {
        title: 'LAB 1: File Upload Vulnerability',
        page: 'file-upload',
        files,
        message: null,
        error: `Upload gagal: ${err.message}`
      });
    }

    if (!req.file) {
      return res.render('file-upload', {
        title: 'LAB 1: File Upload Vulnerability',
        page: 'file-upload',
        files,
        message: null,
        error: 'Tidak ada file yang dipilih.'
      });
    }

    // Refetch files after upload
    files = fs.readdirSync(uploadsDir).map(name => ({
      name,
      url: `/uploads/${name}`,
      size: fs.statSync(path.join(uploadsDir, name)).size
    }));

    res.render('file-upload', {
      title: 'LAB 1: File Upload Vulnerability',
      page: 'file-upload',
      files,
      message: `✅ File berhasil diupload: ${req.file.originalname} → Akses: /uploads/${req.file.filename}`,
      error: null
    });
  });
});

// =============================================
// ROUTES - FIXED
// =============================================

// GET /lab/file-upload/fixed
router.get('/fixed', (req, res) => {
  res.render('fixed-file-upload', {
    title: 'LAB 1 (Fixed): File Upload – Versi Aman',
    page: 'fixed-file-upload',
    files: fixedUploadedFilesLog,
    message: null,
    error: null
  });
});

// POST /lab/file-upload/fixed/upload
router.post('/fixed/upload', (req, res) => {
  fixedUpload.single('file')(req, res, (err) => {
    if (err) {
      let errMsg = err.message;
      if (err.code === 'LIMIT_FILE_SIZE') {
        errMsg = `Ukuran file melebihi batas maksimal (1 MB).`;
      }
      return res.render('fixed-file-upload', {
        title: 'LAB 1 (Fixed): File Upload – Versi Aman',
        page: 'fixed-file-upload',
        files: fixedUploadedFilesLog,
        message: null,
        error: `❌ Upload ditolak: ${errMsg}`
      });
    }

    if (!req.file) {
      return res.render('fixed-file-upload', {
        title: 'LAB 1 (Fixed): File Upload – Versi Aman',
        page: 'fixed-file-upload',
        files: fixedUploadedFilesLog,
        message: null,
        error: 'Tidak ada file yang dipilih.'
      });
    }

    // Simpan metadata
    const meta = {
      originalName: req.file.originalname,
      savedAs: req.file.filename,
      mimeType: req.file.mimetype,
      size: req.file.size,
      uploadedAt: new Date().toLocaleString('id-ID')
    };
    fixedUploadedFilesLog.unshift(meta);

    res.render('fixed-file-upload', {
      title: 'LAB 1 (Fixed): File Upload – Versi Aman',
      page: 'fixed-file-upload',
      files: fixedUploadedFilesLog,
      message: `✅ File diterima dan disimpan dengan aman. Nama asli: "${req.file.originalname}" → Disimpan sebagai: "${req.file.filename}"`,
      error: null
    });
  });
});

module.exports = router;
