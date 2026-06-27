const express = require('express');
const router = express.Router();
const path = require('path');
const fs = require('fs');

// =============================================
// Database menggunakan sql.js (pure JavaScript)
// Data disimpan ke file .sqlite agar persistent
// =============================================
const initSqlJs = require('sql.js');

const isVercel = process.env.VERCEL === '1' || !!process.env.VERCEL;
const dataDir = isVercel ? '/tmp' : path.join(__dirname, '..', 'data');
if (!fs.existsSync(dataDir)) fs.mkdirSync(dataDir, { recursive: true });

const DB_PATH = path.join(dataDir, 'database.sqlite');

let db;
let SQL;

// Simpan DB ke file setiap kali ada perubahan
function saveDb() {
  if (db) {
    const data = db.export();
    fs.writeFileSync(DB_PATH, Buffer.from(data));
  }
}

// Inisialisasi database async
async function initDb() {
  SQL = await initSqlJs();

  if (fs.existsSync(DB_PATH)) {
    const fileBuffer = fs.readFileSync(DB_PATH);
    db = new SQL.Database(fileBuffer);
  } else {
    db = new SQL.Database();
  }

  // Buat tabel
  db.run(`
    CREATE TABLE IF NOT EXISTS vouchers (
      id INTEGER PRIMARY KEY,
      code TEXT UNIQUE NOT NULL,
      reward INTEGER NOT NULL,
      is_used INTEGER DEFAULT 0
    );
  `);

  db.run(`
    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY,
      username TEXT UNIQUE NOT NULL,
      points INTEGER DEFAULT 0
    );
  `);

  db.run(`
    CREATE TABLE IF NOT EXISTS redemptions (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      voucher_code TEXT NOT NULL,
      username TEXT NOT NULL,
      redeemed_at TEXT NOT NULL,
      UNIQUE(voucher_code, username)
    );
  `);

  // Seed data awal
  const voucherExists = db.exec("SELECT * FROM vouchers WHERE code = 'PROMO100'");
  if (!voucherExists.length || !voucherExists[0].values.length) {
    db.run("INSERT OR IGNORE INTO vouchers (code, reward, is_used) VALUES ('PROMO100', 100, 0)");
  }

  const userExists = db.exec("SELECT * FROM users WHERE username = 'student'");
  if (!userExists.length || !userExists[0].values.length) {
    db.run("INSERT OR IGNORE INTO users (username, points) VALUES ('student', 0)");
  }

  saveDb();
}

// Helper: jalankan query dan ambil satu baris
function getOne(query, params = []) {
  const stmt = db.prepare(query);
  stmt.bind(params);
  if (stmt.step()) {
    const row = stmt.getAsObject();
    stmt.free();
    return row;
  }
  stmt.free();
  return null;
}

// Helper: ambil state saat ini
function getCurrentState() {
  const voucher = getOne('SELECT * FROM vouchers WHERE code = ?', ['PROMO100']);
  const user    = getOne('SELECT * FROM users WHERE username = ?', ['student']);
  return { voucher, user };
}

// =============================================
// IN-MEMORY LOCK untuk Fixed Version
// =============================================
let isRedeeming = false;

// =============================================
// Inisialisasi DB saat module di-load
// =============================================
let dbReady = false;
initDb().then(() => {
  dbReady = true;
  console.log('✅ SQLite (sql.js) database siap');
}).catch(err => {
  console.error('❌ Database init error:', err);
});

// Middleware: pastikan DB sudah siap
function ensureDb(req, res, next) {
  if (!dbReady) {
    return res.status(503).json({ error: 'Database belum siap, tunggu sebentar.' });
  }
  next();
}

// =============================================
// ROUTES – VULNERABLE
// =============================================

// GET /lab/race-condition
router.get('/', ensureDb, (req, res) => {
  const { voucher, user } = getCurrentState();
  res.render('race-condition', {
    title: 'LAB 3: Race Condition',
    page: 'race-condition',
    voucher,
    user,
    message: null,
    error: null
  });
});

// POST /lab/race-condition/redeem (VULNERABLE)
router.post('/redeem', ensureDb, async (req, res) => {
  const code = req.body.code || 'PROMO100';

  // ⚠️ VULNERABLE: Cek voucher terpisah dari update
  const voucher = getOne('SELECT * FROM vouchers WHERE code = ?', [code]);

  if (!voucher) {
    return res.json({ success: false, message: 'Voucher tidak ditemukan.' });
  }
  if (voucher.is_used) {
    return res.json({ success: false, message: 'Voucher sudah pernah digunakan.' });
  }

  // ⚠️ VULNERABLE: Delay buatan – celah race condition
  await new Promise(resolve => setTimeout(resolve, Math.floor(Math.random() * 300) + 200));

  // ⚠️ VULNERABLE: Update setelah delay (bisa di-race)
  db.run('UPDATE vouchers SET is_used = 1 WHERE code = ?', [code]);
  db.run('UPDATE users SET points = points + ? WHERE username = ?', [voucher.reward, 'student']);
  saveDb();

  const user = getOne('SELECT * FROM users WHERE username = ?', ['student']);

  res.json({
    success: true,
    message: `✅ Voucher berhasil! +${voucher.reward} poin ditambahkan.`,
    newPoints: user.points
  });
});

// POST /lab/race-condition/reset
router.post('/reset', ensureDb, (req, res) => {
  db.run('UPDATE vouchers SET is_used = 0 WHERE code = ?', ['PROMO100']);
  db.run('UPDATE users SET points = 0 WHERE username = ?', ['student']);
  db.run('DELETE FROM redemptions');
  saveDb();
  res.json({ success: true, message: '🔄 Data berhasil direset.' });
});

// =============================================
// ROUTES – FIXED
// =============================================

// GET /lab/race-condition/fixed
router.get('/fixed', ensureDb, (req, res) => {
  const { voucher, user } = getCurrentState();
  res.render('fixed-race-condition', {
    title: 'LAB 3 (Fixed): Race Condition – Versi Aman',
    page: 'fixed-race-condition',
    voucher,
    user,
    message: null,
    error: null
  });
});

// POST /lab/race-condition/fixed/redeem (FIXED)
router.post('/fixed/redeem', ensureDb, async (req, res) => {
  const code     = req.body.code || 'PROMO100';
  const username = 'student';

  // ✅ FIXED Teknik 1: In-Memory Lock
  if (isRedeeming) {
    return res.json({
      success: false,
      message: '⏳ Redeem sedang diproses oleh request lain, coba lagi nanti.'
    });
  }
  isRedeeming = true;

  try {
    // ✅ FIXED Teknik 2: Cek dan update dalam satu blok terlindungi lock
    const voucher = getOne('SELECT * FROM vouchers WHERE code = ?', [code]);

    if (!voucher) throw new Error('Voucher tidak ditemukan.');
    if (voucher.is_used) throw new Error('Voucher sudah pernah digunakan.');

    // ✅ FIXED Teknik 3: Unique constraint — akan throw error jika sudah ada
    try {
      db.run(
        'INSERT INTO redemptions (voucher_code, username, redeemed_at) VALUES (?, ?, ?)',
        [code, username, new Date().toISOString()]
      );
    } catch (constraintErr) {
      throw new Error('Voucher sudah pernah diredeem oleh akun ini.');
    }

    db.run('UPDATE vouchers SET is_used = 1 WHERE code = ?', [code]);
    db.run('UPDATE users SET points = points + ? WHERE username = ?', [voucher.reward, username]);
    saveDb();

    // Simulasi delay (tapi sudah terlindungi lock)
    await new Promise(resolve => setTimeout(resolve, 50));

    const user = getOne('SELECT * FROM users WHERE username = ?', [username]);

    res.json({
      success: true,
      message: `✅ Voucher berhasil diredeem! +${voucher.reward} poin ditambahkan.`,
      newPoints: user.points
    });

  } catch (err) {
    res.json({ success: false, message: `❌ ${err.message}` });
  } finally {
    isRedeeming = false; // ✅ Selalu lepas lock
  }
});

// POST /lab/race-condition/fixed/reset
router.post('/fixed/reset', ensureDb, (req, res) => {
  db.run('UPDATE vouchers SET is_used = 0 WHERE code = ?', ['PROMO100']);
  db.run('UPDATE users SET points = 0 WHERE username = ?', ['student']);
  db.run('DELETE FROM redemptions');
  saveDb();
  res.json({ success: true, message: '🔄 Data berhasil direset.' });
});

module.exports = router;
