<?php
$title = "Race Condition (Fixed)";

// Setup database
$db_file = __DIR__ . '/data/database.json';
$default_db = [
    'vouchers' => [
        ['id' => 1, 'code' => 'PROMO100', 'reward' => 100, 'is_used' => 0]
    ],
    'users' => [
        ['id' => 1, 'username' => 'student', 'points' => 0]
    ],
    'redemptions' => []
];

function get_db_data($db_file, $default_db) {
    if (!file_exists($db_file)) {
        file_put_contents($db_file, json_encode($default_db, JSON_PRETTY_PRINT));
        return $default_db;
    }
    return json_decode(file_get_contents($db_file), true) ?: $default_db;
}

function save_db_data($db_file, $data) {
    file_put_contents($db_file, json_encode($data, JSON_PRETTY_PRINT));
}

// Lock file untuk in-memory lock simulation di PHP
$lock_file = __DIR__ . '/data/redeem.lock';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'reset') {
        save_db_data($db_file, $default_db);
        if (file_exists($lock_file)) {
            unlink($lock_file);
        }
        echo json_encode(['success' => true, 'message' => '🔄 Data berhasil direset.']);
        exit;
    }
    
    if ($_GET['action'] === 'redeem') {
        $username = 'student';
        $code = 'PROMO100';

        // ✅ FIXED Teknik 1: File-based Lock (Pengganti In-Memory lock di PHP multi-process)
        $fp = fopen($lock_file, "w+");
        
        // Coba kunci file secara eksklusif (Non-blocking LOCK)
        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            echo json_encode(['success' => false, 'message' => '⏳ Sistem sibuk. Transaksi sedang diproses request lain.']);
            fclose($fp);
            exit;
        }

        try {
            $db = get_db_data($db_file, $default_db);
            
            // Cari voucher
            $voucher = null;
            foreach ($db['vouchers'] as &$v) {
                if ($v['code'] === $code) {
                    $voucher = &$v;
                    break;
                }
            }

            if (!$voucher) {
                throw new Exception('Voucher tidak ditemukan.');
            }

            // ✅ FIXED Teknik 2: Validasi Ulang
            if ($voucher['is_used'] == 1) {
                throw new Exception('Voucher sudah pernah digunakan.');
            }

            // ✅ FIXED Teknik 3: Verifikasi Redemption log (Unique constraint simulation)
            $already_redeemed = false;
            foreach ($db['redemptions'] as $r) {
                if ($r['voucher_code'] === $code && $r['username'] === $username) {
                    $already_redeemed = true;
                    break;
                }
            }

            if ($already_redeemed) {
                throw new Exception('Voucher sudah pernah diredeem oleh akun ini.');
            }

            // Catat log claim
            $db['redemptions'][] = [
                'voucher_code' => $code,
                'username' => $username,
                'redeemed_at' => date('c')
            ];

            // Update status voucher & points
            $voucher['is_used'] = 1;
            
            $points = 0;
            foreach ($db['users'] as &$u) {
                if ($u['username'] === $username) {
                    $u['points'] += 100;
                    $points = $u['points'];
                    break;
                }
            }

            save_db_data($db_file, $db);

            // Simulasi delay singkat
            usleep(50000); // 50ms

            echo json_encode([
                'success' => true,
                'message' => '✅ Voucher berhasil diredeem! Poin berhasil ditambahkan.',
                'newPoints' => $points
            ]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '❌ ' . $e->getMessage()]);
        } finally {
            // ✅ Lepas lock & tutup file
            flock($fp, LOCK_UN);
            fclose($fp);
        }
        exit;
    }
}

$db = get_db_data($db_file, $default_db);
$voucher = $db['vouchers'][0];
$user = $db['users'][0];

include('header.php');
?>

<main class="main-content">

  <!-- PAGE HEADER -->
  <div class="page-header fixed-page-header">
    <div class="page-header-inner">
      <div class="lab-badge fixed-badge">✅ SECURE VERSION</div>
      <h1>⚡ LAB 3 (Fixed): Race Condition – Versi Aman</h1>
      <p>Pencegahan manipulasi konkuren menggunakan mekanisme Mutex/Locking dan Validasi Ketat.</p>
    </div>
  </div>

  <!-- NAVIGATION SUB-LABS -->
  <div style="display:flex; gap:0.5rem; margin-bottom:2rem; flex-wrap:wrap;">
    <a href="/race-condition.php" class="btn btn-outline">Level 1: Vulnerable</a>
    <a href="/race-condition-fixed.php" class="btn btn-fixed" style="border: 2px solid var(--accent-green)">Level 2: Secure Version</a>
  </div>

  <div class="lab-container">

    <!-- KONSEP -->
    <section class="lab-section">
      <h2>📖 Cara Mengatasi Race Condition</h2>
      <div class="concept-box">
        <p>Lab ini mengimplementasikan pengamanan tingkat tinggi untuk menangani concurrent request:</p>
        <ol>
          <li><strong>Mekanisme File-Locking (Mutex)</strong>: Mengunci proses akses ke fungsi database secara eksklusif menggunakan <code>flock()</code>. Request kedua yang masuk pada waktu yang sama akan langsung ditolak jika file sedang dikunci.</li>
          <li><strong>Double-Check Validation</strong>: Memvalidasi ulang status voucher langsung dari data terbaru di dalam lock block sebelum melakukan penulisan.</li>
          <li><strong>Unique Constraint Simulation</strong>: Menyimpan riwayat klaim ke dalam tabel redemption log dan melakukan pengecekan unik untuk mematikan peluang klaim ganda.</li>
        </ol>
      </div>
    </section>

    <!-- STATUS BOARD -->
    <section class="lab-section">
      <h2>📊 Status Saat Ini</h2>
      <div class="status-board fixed-board" id="status-board">
        <div class="status-item">
          <div class="status-label">👤 User</div>
          <div class="status-value"><?php echo htmlspecialchars($user['username']); ?></div>
        </div>
        <div class="status-item">
          <div class="status-label">💰 Poin</div>
          <div class="status-value status-points" style="color:var(--accent-green);"><?php echo $user['points']; ?></div>
        </div>
        <div class="status-item">
          <div class="status-label">🎟️ Voucher</div>
          <div class="status-value"><?php echo htmlspecialchars($voucher['code']); ?></div>
        </div>
        <div class="status-item">
          <div class="status-label">📋 Status Voucher</div>
          <div class="status-value <?php echo $voucher['is_used'] ? 'status-used' : 'status-available'; ?>">
            <?php echo $voucher['is_used'] ? '✅ Sudah Digunakan' : '🟢 Tersedia'; ?>
          </div>
        </div>
        <div class="status-item">
          <div class="status-label">🎁 Reward</div>
          <div class="status-value"><?php echo $voucher['reward']; ?> poin</div>
        </div>
      </div>
    </section>

    <!-- FORM TEST -->
    <section class="lab-section">
      <h2>🧪 Uji Coba Proteksi Konkuren</h2>
      <div class="fix-box">
        <div class="fixed-label-inline" style="margin-bottom:1rem;">✅ Endpoint Aman: <code>POST /race-condition-fixed.php?action=redeem</code></div>

        <div class="race-buttons">
          <button class="btn btn-fixed" style="background:var(--accent-green);color:#000;" id="btn-redeem-once" onclick="redeemOnce()">
            🎟️ Redeem Voucher Sekali
          </button>
          <button class="btn btn-race" id="btn-race-attack" onclick="raceAttack()">
            ⚡ Simulasikan 10 Request Bersamaan
          </button>
          <button class="btn btn-reset" id="btn-reset-vuln" onclick="resetVuln()">
            🔄 Reset Data
          </button>
        </div>

        <div class="race-log-container">
          <div class="race-log-header">
            <span>📋 Log Response</span>
            <button onclick="document.getElementById('race-log').innerHTML=''" class="btn-clear-log">🗑️ Clear</button>
          </div>
          <div id="race-log" class="race-log"></div>
        </div>
      </div>
    </section>

  </div>
</main>

<?php include('footer.php'); ?>

<script>
const ENDPOINT = '/race-condition-fixed.php?action=redeem';
const RESET_ENDPOINT = '/race-condition-fixed.php?action=reset';

function addLog(msg, type = 'info') {
  const log = document.getElementById('race-log');
  const entry = document.createElement('div');
  entry.className = `log-entry log-${type}`;
  const time = new Date().toLocaleTimeString('id-ID');
  entry.innerHTML = `<span class="log-time">[${time}]</span> ${msg}`;
  log.prepend(entry);
}

async function redeemOnce() {
  const btn = document.getElementById('btn-redeem-once');
  btn.disabled = true;
  addLog('Mengirim 1 request redeem...', 'info');

  try {
    const res = await fetch(ENDPOINT, {
      method: 'POST'
    });
    const data = await res.json();
    addLog(data.message + (data.newPoints !== undefined ? ` | Poin sekarang: <strong>${data.newPoints}</strong>` : ''), data.success ? 'success' : 'error');
  } catch (err) {
    addLog('Fetch error: ' + err.message, 'error');
  }

  btn.disabled = false;
  setTimeout(() => location.reload(), 1000);
}

async function raceAttack() {
  const btn = document.getElementById('btn-race-attack');
  btn.disabled = true;
  addLog('⚡ Mengirim 10 request secara BERSAMAAN...', 'warning');

  const requests = [];
  for (let i = 0; i < 10; i++) {
    requests.push(
      fetch(ENDPOINT, {
        method: 'POST'
      }).then(r => r.json()).then(data => ({ i: i+1, ...data }))
    );
  }

  const results = await Promise.allSettled(requests);
  let successCount = 0;

  results.forEach(r => {
    if (r.status === 'fulfilled') {
      const d = r.value;
      if (d.success) {
        successCount++;
        addLog(`Request #${d.i}: ✅ ${d.message} | Poin: <strong>${d.newPoints}</strong>`, 'success');
      } else {
        addLog(`Request #${d.i}: ❌ ${d.message}`, 'error');
      }
    } else {
      addLog(`Request error: ${r.reason}`, 'error');
    }
  });

  addLog(`<strong>Hasil: ${successCount} dari 10 request berhasil redeem!</strong>${successCount > 1 ? ' ← RACE CONDITION BERHASIL! 🎯' : ' ← Sistem Aman (Hanya 1 yang lolos) ✅'}`, successCount > 1 ? 'warning' : 'success');

  btn.disabled = false;
  setTimeout(() => location.reload(), 1500);
}

async function resetVuln() {
  try {
    const res = await fetch(RESET_ENDPOINT, { method: 'POST' });
    const data = await res.json();
    addLog(data.message, 'info');
    setTimeout(() => location.reload(), 800);
  } catch (err) {
    addLog('Reset error: ' + err.message, 'error');
  }
}
</script>
