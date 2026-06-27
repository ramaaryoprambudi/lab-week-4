# 🔐 Cyber Security LAB – Week 4
## Advanced Attack & Simulation (PHP Native Version)

> **⚠️ PERINGATAN:** Project ini dibuat **hanya untuk pembelajaran lokal/lab**. Jangan deploy ke internet. Jangan gunakan teknik ini di sistem milik orang lain.

---

## 📋 Deskripsi

Project ini adalah aplikasi web edukasi berbasis **PHP Native** yang mensimulasikan 4 jenis kerentanan keamanan web umum serta cara mitigasinya:

1. **File Upload Vulnerability** – Mengunggah file tanpa validasi (3 tingkatan bypass) hingga versi aman.
2. **Information Disclosure** – Kebocoran data sensitif melalui respon API, debug, dan stack trace.
3. **Race Condition** – Eksploitasi celah waktu pemrosesan konkuren pada voucher hadiah.
4. **Recon & Hidden Endpoints** – Pengintaian directory brute-force menggunakan dirsearch/gobuster dan analisis header HTTP.

Setiap lab dirancang secara interaktif menggunakan gaya desain **modern cyber-punk (glassmorphism & neon glow)** yang responsif.

---

## 🗂️ Struktur Project

```
cyber-lab-week4/
├── config.php                      # Bootstrapping & basis URL dinamis
├── index.php                       # Halaman Utama Dashboard
├── header.php                      # Partial layout header & navigasi
├── footer.php                      # Partial layout footer
├── robots.txt                      # File robots.txt (Simulasi Recon)
├── config.json                     # File konfigurasi terekspos (Simulasi Recon)
├── .gitignore                      # Git ignore
├── admin/                          # Panel admin (Simulasi Recon)
│   ├── index.php                   # Admin login page (dummy)
│   ├── dashboard.php               # API dashboard statistik
│   └── users.php                   # API user list
├── api/                            # API internal (Simulasi Info Disclosure)
│   ├── debug.php                   # Debug info
│   ├── keys.php                    # Webhook API keys
│   └── users.php                   # User tokens list
├── data/                           # Folder database JSON & data secure
│   ├── database.json               # JSON database (auto-created)
│   ├── secure-uploads/             # Folder unggahan aman (non-publik)
│   └── secure-upload-log.json      # Log riwayat unggahan aman
├── public/                         # Aset publik & folder upload rentan
│   ├── css/
│   │   └── style.css               # Main stylesheet (Cyber-punk style)
│   └── uploads/                    # Folder upload vulnerable (publik)
│       └── fake-webshell.html      # Berkas HTML untuk simulasi webshell
├── file-upload-1.php               # LAB 1 – Level 1: Tanpa Validasi
├── file-upload-2.php               # LAB 1 – Level 2: Blacklist Filter
├── file-upload-3.php               # LAB 1 – Level 3: MIME Type Bypass
├── file-upload-fixed.php           # LAB 1 – Level 4: Versi Aman (Secure)
├── info-disclosure.php             # LAB 2 – Versi Vulnerable
├── info-disclosure-fixed.php       # LAB 2 – Versi Aman (Secure)
├── race-condition.php              # LAB 3 – Versi Vulnerable (usleep delay)
├── race-condition-fixed.php        # LAB 3 – Versi Aman (flock file lock)
└── recon.php                       # LAB 4 – Panduan Recon & dirsearch scan
```

---

## 🚀 Cara Install & Menjalankan

### 1. Salin ke Web Server Lokal
Salin atau clone folder project ini ke dalam direktori publik web server lokal Anda:
- **MAMP**: `/Applications/MAMP/htdocs/`
- **XAMPP**: `C:\xampp\htdocs\`
- **Laragon**: `C:\laragon\www\`

### 2. Jalankan Apache Web Server
Aktifkan web server lokal Anda melalui control panel MAMP / XAMPP / Laragon (pastikan modul PHP aktif, disarankan PHP versi 7.4 s.d 8.x).

### 3. Akses di Browser
Buka browser dan arahkan ke alamat folder project Anda. Karena project menggunakan inisialisasi basis URL dinamis (`config.php`), Anda bebas meletakkannya di subdirektori manapun. Contoh:
```
http://localhost:8888/labs-week-4/cyber-lab-week4/index.php
```

---

## 🧪 Penjelasan Masing-masing Lab

### LAB 1: File Upload Vulnerability
- **Level 1**: Server menerima file apapun tanpa proteksi. Bisa digunakan untuk mengunggah shell `.php` secara langsung.
- **Level 2**: Server menggunakan filter blacklist sederhana (hanya memblokir `.php` lowercase). Dapat di-bypass dengan manipulasi huruf kapital (`.pHp`) atau ekstensi alternatif (`.phtml`, `.phar`).
- **Level 3**: Server memvalidasi header parameter `Content-Type` bawaan browser. Dapat di-bypass melalui Burp Suite dengan mengubah header menjadi `image/jpeg`.
- **Level 4 (Secure)**: Validasi whitelist ketat, validasi tipe MIME asli menggunakan Magic Bytes (`mime_content_type`), pengubahan nama secara acak (UUID), dan penyimpanan di folder non-publik (`/data/secure-uploads/`).

### LAB 2: Information Disclosure
- Simulasi kebocoran kredensial rahasia, data server internal, key API, dan error stack trace.
- Versi aman menonaktifkan pelaporan error internal ke publik (`ini_set('display_errors', 0)`), memblokir endpoint debug di luar *development env*, serta menyaring field sensitif dari API response.

### LAB 3: Race Condition
- Eksploitasi penggunaan voucher promo bersamaan sebelum status data diperbarui.
- Versi aman mengimplementasikan locking berbasis berkas (`flock()`) sebagai pengganti mutex di PHP multi-process, validasi ganda, serta pencatatan log unik redemption.

### LAB 4: Recon & Hidden Endpoints
- Penggunaan alat bantu directory brute-force (`dirsearch`, `gobuster`, `ffuf`) untuk menemukan panel `/admin/` dan file sensitif lainnya.
- Membaca robots.txt untuk menemukan petunjuk direktori internal yang disembunyikan developer.

---

## ⚙️ Kebutuhan Sistem
- PHP Interpreter (v7.4 - v8.x)
- Apache Web Server (MAMP / XAMPP / Laragon)
- Web Browser modern
- (Opsional untuk latihan) Burp Suite & Python (untuk dirsearch)

---

*Cyber Security LAB – Week 4 | Advanced Attack & Simulation*
