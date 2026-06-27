const express = require('express');
const router = express.Router();
const path = require('path');
const fs = require('fs');

// =============================================
// Database menggunakan file JSON sederhana
// Agar 100% kompatibel dengan Vercel (Serverless)
// Tanpa dependensi WASM/binary C++
// =============================================
const isVercel = process.env.VERCEL === '1' || !!process.env.VERCEL;
const dbDir = isVercel ? '/tmp' : path.join(__dirname, '..', 'data');
if (!fs.existsSync(dbDir)) fs.mkdirSync(dbDir, { recursive: true });

const DB_PATH = path.join(dbDir, 'database.json');

const defaultDb = {
  vouchers: [
    { id: 1, code: 'PROMO100', reward: 100, is_used: 0 }
  ],
  users: [
    { id: 1, username: 'student', points: 0 }
  ],
  redemptions: []
};

// Helper: baca database
function readDb() {
  if (!fs.existsSync(DB_PATH)) {
    fs.writeFileSync(DB_PATH, JSON.stringify(defaultDb, null, 2), 'utf-8');
    return JSON.parse(JSON.stringify(defaultDb));
  }
  try {
    const content = fs.readFileSync(DB_PATH, 'utf-8');
    return JSON.parse(content);
  } catch (e) {
    return JSON.parse(JSON.stringify(defaultDb));
  }
}

// Helper: tulis database
function writeDb(data) {
  try {
    fs.writeFileSync(DB_PATH, JSON.stringify(data, null, 2), 'utf-8');
  } catch (e) {
    console.error('Error writing DB:', e);
  }
}

// Helper: dapatkan state saat ini
function getCurrentState() {
  const dbData = readDb();
  const voucher = dbData.vouchers.find(v => v.code === 'PROMO100');
  const user = dbData.users.find(u => u.username === 'student');
  return { voucher, user };
}

// =============================================
// IN-MEMORY LOCK untuk Fixed Version
// =============================================
let isRedeeming = false;

// =============================================
// ROUTES - VULNERABLE
// =============================================

// GET /lab/race-condition
router.get('/', (req, res) => {
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
router.post('/redeem', async (req, res) => {
  const code = req.body.code || 'PROMO100';

  const dbData = readDb();
  const voucher = dbData.vouchers.find(v => v.code === code);

  if (!voucher) {
    return res.json({ success: false, message: 'Voucher tidak ditemukan.' });
  }
  if (voucher.is_used) {
    return res.json({ success: false, message: 'Voucher sudah pernah digunakan.' });
  }

  // ⚠️ VULNERABLE: Delay buatan – celah race condition
  await new Promise(resolve => setTimeout(resolve, Math.floor(Math.random() * 300) + 200));

  // Ambil state DB terbaru setelah delay (karena concurrent request mungkin sudah mengubahnya)
  const freshDb = readDb();
  const freshVoucher = freshDb.vouchers.find(v => v.code === code);
  const freshUser = freshDb.users.find(u => u.username === 'student');

  // ⚠️ VULNERABLE: Tetap update & tambah poin tanpa memvalidasi ulang freshVoucher.is_used
  freshVoucher.is_used = 1;
  freshUser.points += voucher.reward;
  writeDb(freshDb);

  res.json({
    success: true,
    message: `✅ Voucher berhasil! +${voucher.reward} poin ditambahkan.`,
    newPoints: freshUser.points
  });
});

// POST /lab/race-condition/reset
router.post('/reset', (req, res) => {
  writeDb(defaultDb);
  res.json({ success: true, message: '🔄 Data berhasil direset.' });
});

// =============================================
// ROUTES - FIXED
// =============================================

// GET /lab/race-condition/fixed
router.get('/fixed', (req, res) => {
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
router.post('/fixed/redeem', async (req, res) => {
  const code = req.body.code || 'PROMO100';
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
    const dbData = readDb();
    const voucher = dbData.vouchers.find(v => v.code === code);
    const user = dbData.users.find(u => u.username === username);

    if (!voucher) throw new Error('Voucher tidak ditemukan.');
    if (voucher.is_used) throw new Error('Voucher sudah pernah digunakan.');

    // ✅ FIXED Teknik 3: Unique constraint simulation
    const alreadyRedeemed = dbData.redemptions.find(r => r.voucher_code === code && r.username === username);
    if (alreadyRedeemed) {
      throw new Error('Voucher sudah pernah diredeem oleh akun ini.');
    }

    // Catat redemption
    dbData.redemptions.push({
      voucher_code: code,
      username: username,
      redeemed_at: new Date().toISOString()
    });

    // Update voucher & user points
    voucher.is_used = 1;
    user.points += voucher.reward;
    writeDb(dbData);

    // Simulasi delay (tapi sudah terlindungi lock)
    await new Promise(resolve => setTimeout(resolve, 50));

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
router.post('/fixed/reset', (req, res) => {
  writeDb(defaultDb);
  res.json({ success: true, message: '🔄 Data berhasil direset.' });
});

module.exports = router;
