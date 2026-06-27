const express = require('express');
const router = express.Router();

// =============================================
// DATA DUMMY untuk simulasi
// =============================================
const DUMMY_USER_FULL = {
  id: 1,
  name: 'Rama',
  email: 'rama@example.com',
  role: 'user',
  internalUserId: 'USR-2026-0001',
  isAdmin: false,
  debugNote: 'User loaded from internal service',
  passwordHash: '$2b$10$dummyhashvalueforlabeducation',
  sessionToken: 'sess_abc123xyz_lab_dummy_token'
};

const DUMMY_USER_SAFE = {
  name: 'Rama',
  email: 'rama@example.com'
};

const DUMMY_DEBUG_INFO = {
  NODE_ENV: 'development',
  APP_VERSION: '1.0.0',
  INTERNAL_API_URL: 'http://internal-api.local',
  FAKE_SECRET_KEY: 'lab_secret_123',
  DATABASE_PATH: '/app/data/database.sqlite',
  ADMIN_USERNAME: 'admin',
  ADMIN_PASSWORD: 'admin123_DUMMY',
  JWT_SECRET: 'jwt_super_secret_key_DUMMY_ONLY',
  DB_HOST: 'localhost',
  DB_PORT: '5432'
};

// =============================================
// ROUTES - VULNERABLE
// =============================================

// GET /lab/information-disclosure
router.get('/', (req, res) => {
  res.render('information-disclosure', {
    title: 'LAB 2: Information Disclosure',
    page: 'info-disclosure',
    result: null,
    resultType: null
  });
});

// GET /lab/information-disclosure/profile (VULNERABLE)
router.get('/profile', (req, res) => {
  // ⚠️ VULNERABLE: Mengembalikan seluruh data user termasuk field internal
  res.json(DUMMY_USER_FULL);
});

// GET /lab/information-disclosure/debug (VULNERABLE)
router.get('/debug', (req, res) => {
  // ⚠️ VULNERABLE: Menampilkan konfigurasi server dan secret
  res.json({
    status: 'ok',
    debug: true,
    environment: DUMMY_DEBUG_INFO,
    serverInfo: {
      platform: process.platform,
      nodeVersion: process.version,
      arch: process.arch,
      uptime: Math.floor(process.uptime()) + ' seconds',
      memoryUsage: process.memoryUsage()
    }
  });
});

// GET /lab/information-disclosure/error (VULNERABLE)
router.get('/error', (req, res) => {
  // ⚠️ VULNERABLE: Menampilkan stack trace ke user
  try {
    // Sengaja memicu error untuk simulasi
    const obj = null;
    obj.nonExistentMethod(); // akan throw TypeError
  } catch (err) {
    // ⚠️ VULNERABLE: Stack trace dikirim ke response
    res.status(500).json({
      error: true,
      message: err.message,
      stack: err.stack,
      type: err.constructor.name,
      internalNote: 'Error logged to database: /app/data/errors.log',
      adminEmail: 'admin@internal.local',
      debugKey: DUMMY_DEBUG_INFO.FAKE_SECRET_KEY
    });
  }
});

// =============================================
// ROUTES - FIXED
// =============================================

// GET /lab/information-disclosure/fixed
router.get('/fixed', (req, res) => {
  res.render('fixed-information-disclosure', {
    title: 'LAB 2 (Fixed): Information Disclosure – Versi Aman',
    page: 'fixed-info-disclosure',
    result: null,
    resultType: null
  });
});

// GET /lab/information-disclosure/fixed/profile (FIXED)
router.get('/fixed/profile', (req, res) => {
  // ✅ FIXED: Hanya tampilkan data yang diperlukan
  res.json(DUMMY_USER_SAFE);
});

// GET /lab/information-disclosure/fixed/debug (FIXED)
router.get('/fixed/debug', (req, res) => {
  // ✅ FIXED: Endpoint debug tidak tersedia di "production"
  res.status(403).json({
    error: 'Akses ditolak.',
    message: 'Endpoint ini tidak tersedia.'
  });
});

// GET /lab/information-disclosure/fixed/error (FIXED)
router.get('/fixed/error', (req, res) => {
  // ✅ FIXED: Error handler yang aman
  try {
    const obj = null;
    obj.nonExistentMethod();
  } catch (err) {
    // ✅ FIXED: Detail error hanya di server log, tidak dikirim ke user
    console.error('[SAFE ERROR HANDLER]', err.stack);
    res.status(500).json({
      error: true,
      message: 'Terjadi kesalahan pada server. Silakan hubungi administrator.'
    });
  }
});

module.exports = router;
