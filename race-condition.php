<?php
$title = "Race Condition";

// Setup database state menggunakan file JSON di dalam folder data/
$db_file = __DIR__ . '/data/database.json';
if (!file_exists(dirname($db_file))) {
    mkdir(dirname($db_file), 0777, true);
}

$default_db = [
    'vouchers' => [
        ['id' => 1, 'code' => 'PROMO100', 'reward' => 100, 'is_used' => 0]
    ],
    'users' => [
        ['id' => 1, 'username' => 'student', 'points' => 0]
    ],
    'redemptions' => []
];

// Helper membaca DB
function get_db_data($db_file, $default_db) {
    if (!file_exists($db_file)) {
        file_put_contents($db_file, json_encode($default_db, JSON_PRETTY_PRINT));
        return $default_db;
    }
    return json_decode(file_get_contents($db_file), true) ?: $default_db;
}

// Helper menyimpan DB
function save_db_data($db_file, $data) {
    file_put_contents($db_file, json_encode($data, JSON_PRETTY_PRINT));
}

// RESTORASI DATA JIKA REQUEST RESET
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'reset') {
        save_db_data($db_file, $default_db);
        echo json_encode(['success' => true, 'message' => '🔄 Data berhasil direset.']);
        exit;
    }
    
    // ENDPOINT REDEEM (⚠️ VULNERABLE)
    if ($_GET['action'] === 'redeem') {
        $db = get_db_data($db_file, $default_db);
        $code = 'PROMO100';
        
        $voucher = null;
        foreach ($db['vouchers'] as &$v) {
            if ($v['code'] === $code) {
                $voucher = &$v;
                break;
            }
        }
        
        if (!$voucher) {
            echo json_encode(['success' => false, 'message' => 'Voucher tidak ditemukan.']);
            exit;
        }
        
        if ($voucher['is_used'] == 1) {
            echo json_encode(['success' => false, 'message' => 'Voucher sudah pernah digunakan.']);
            exit;
        }
        
        // ⚠️ VULNERABLE: Delay buatan – celah race condition
        usleep(rand(200000, 500000)); // 200ms - 500ms
        
        // Ambil data terbaru lagi (karena request konkuren mungkin mengubah database)
        $fresh_db = get_db_data($db_file, $default_db);
        
        // Cari voucher & user terbaru
        foreach ($fresh_db['vouchers'] as &$fv) {
            if ($fv['code'] === $code) {
                // ⚠️ VULNERABLE: Tetap tandai dipakai & tambahkan poin tanpa validasi ulang status
                $fv['is_used'] = 1;
                break;
            }
        }
        foreach ($fresh_db['users'] as &$fu) {
            if ($fu['username'] === 'student') {
                $fu['points'] += 100;
                $points = $fu['points'];
                break;
            }
        }
        
        save_db_data($db_file, $fresh_db);
        
        echo json_encode([
            'success' => true,
            'message' => '✅ Voucher berhasil! +100 poin ditambahkan.',
            'newPoints' => $points
        ]);
        exit;
    }
}

// BACA STATE SEKARANG UNTUK DISPLAY UI
$db = get_db_data($db_file, $default_db);
$voucher = $db['vouchers'][0];
$user = $db['users'][0];

include('header.php');
?>

<main class="main-content">

  <div class="page-header vuln-page-header">
    <div class="page-header-inner">
      <div class="lab-badge vuln-badge">⚠️ VULNERABLE VERSION</div>
      <h1>⚡ LAB 3: Race Condition</h1>
      <p>Simulasi eksploitasi voucher sekali pakai dengan mengirim banyak request bersamaan.</p>
    </div>
  </div>

  <div class="lab-container">

    <!-- KONSEP -->
    <section class="lab-section">
      <h2>📖 Konsep</h2>
      <div class="concept-box">
        <p><strong>Race Condition</strong> terjadi ketika beberapa request dikirim hampir bersamaan, sehingga lebih dari satu request dapat lolos sebelum sistem memperbarui status data.</p>
        <div class="race-diagram">
          <div class="race-step">
            <span class="race-req">Request 1</span>
            <span class="race-arrow">→</span>
            <span class="race-action">Cek voucher (belum digunakan ✓)</span>
          </div>
          <div class="race-step">
            <span class="race-req">Request 2</span>
            <span class="race-arrow">→</span>
            <span class="race-action">Cek voucher (belum digunakan ✓) ← Masuk sebelum update!</span>
          </div>
          <div class="race-step">
            <span class="race-req">Request 1</span>
            <span class="race-arrow">→</span>
            <span class="race-action">Tambah poin + tandai used</span>
          </div>
          <div class="race-step">
            <span class="race-req">Request 2</span>
            <span class="race-arrow">→</span>
            <span class="race-action">Tambah poin lagi! ⚠️</span>
          </div>
        </div>
      </div>
    </section>

    <!-- STATUS BOARD -->
    <section class="lab-section">
      <h2>📊 Status Saat Ini</h2>
      <div class="status-board vuln-board" id="status-board">
        <div class="status-item">
          <div class="status-label">👤 User</div>
          <div class="status-value" id="stat-username"><?php echo htmlspecialchars($user['username']); ?></div>
        </div>
        <div class="status-item">
          <div class="status-label">💰 Poin</div>
          <div class="status-value status-points" id="stat-points"><?php echo $user['points']; ?></div>
        </div>
        <div class="status-item">
          <div class="status-label">🎟️ Voucher</div>
          <div class="status-value" id="stat-voucher"><?php echo htmlspecialchars($voucher['code']); ?></div>
        </div>
        <div class="status-item">
          <div class="status-label">📋 Status Voucher</div>
          <div class="status-value <?php echo $voucher['is_used'] ? 'status-used' : 'status-available'; ?>" id="stat-voucher-status">
            <?php echo $voucher['is_used'] ? '✅ Sudah Digunakan' : '🟢 Tersedia'; ?>
          </div>
        </div>
        <div class="status-item">
          <div class="status-label">🎁 Reward</div>
          <div class="status-value"><?php echo $voucher['reward']; ?> poin</div>
        </div>
      </div>
    </section>

    <!-- SIMULASI VULNERABLE -->
    <section class="lab-section">
      <h2>🧪 Simulasi Serangan Race Condition</h2>
      <div class="vuln-box">
        <div class="vuln-label">⚠️ Endpoint Vulnerable: <code>POST /race-condition.php?action=redeem</code></div>
        <p>Server memiliki delay 200–500ms antara pengecekan dan update — celah untuk race condition!</p>

        <div class="race-buttons">
          <button class="btn btn-vuln" id="btn-redeem-once" onclick="redeemOnce()">
            🎟️ Redeem Voucher Sekali
          </button>
          <button class="btn btn-race" id="btn-race-attack" onclick="raceAttack()">
            ⚡ Simulasikan 10 Request Bersamaan
          </button>
          <button class="btn btn-reset" id="btn-reset-vuln" onclick="resetVuln()">
            🔄 Reset Data
          </button>
        </div>

        <!-- LOG OUTPUT -->
        <div class="race-log-container">
          <div class="race-log-header">
            <span>📋 Log Response</span>
            <button onclick="document.getElementById('race-log').innerHTML=''" class="btn-clear-log">🗑️ Clear</button>
          </div>
          <div id="race-log" class="race-log"></div>
        </div>
      </div>
    </section>

    <!-- BURP SUITE GUIDE -->
    <section class="lab-section">
      <h2>🦊 Cara Menggunakan Burp Suite – Race Condition Attack</h2>
      <div class="burp-guide-box">
        <p style="color:var(--text-secondary);margin-bottom:1rem;">
          Gunakan **Burp Suite Intruder** atau **Turbo Intruder** untuk mengirimkan payload secara paralel ke endpoint di atas.
        </p>
        <div class="steps-list">
          <div class="step-item">
            <span class="step-num">1</span>
            <div>
              <strong>Tangkap Request Redeem:</strong>
              <p>Tangkap request <code>POST /race-condition.php?action=redeem</code> via Burp Proxy → Kirim ke Intruder.</p>
            </div>
          </div>
          <div class="step-item">
            <span class="step-num">2</span>
            <div>
              <strong>Intruder Setup:</strong>
              <p>Atur Attack Type ke **Sniper**, gunakan **Null Payloads**, dan set payload count sebanyak **10** atau lebih. Centang **Send requests in parallel** di Resource Pool.</p>
            </div>
          </div>
          <div class="step-item">
            <span class="step-num">3</span>
            <div>
              <strong>Jalankan Serangan:</strong>
              <p>Jalankan serangan. Amati apakah Anda menerima status sukses (HTTP 200) lebih dari satu kali.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- NAVIGASI -->
    <div class="nav-to-fixed">
      <a href="/race-condition-fixed.php" class="btn btn-fixed-large" id="btn-goto-fixed-race">
        ✅ Lihat Versi Aman (Fixed) →
      </a>
    </div>

  </div>
</main>

<?php include('footer.php'); ?>

<script>
const ENDPOINT = '/race-condition.php?action=redeem';
const RESET_ENDPOINT = '/race-condition.php?action=reset';

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

  addLog(`<strong>Hasil: ${successCount} dari 10 request berhasil redeem!</strong>${successCount > 1 ? ' ← RACE CONDITION BERHASIL! 🎯' : ''}`, successCount > 1 ? 'warning' : 'info');

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
