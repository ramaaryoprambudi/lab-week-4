# 🔐 Cyber Security LAB – Week 4
## Advanced Attack & Simulation

> **⚠️ PERINGATAN:** Project ini dibuat **hanya untuk pembelajaran lokal/lab**. Jangan deploy ke internet. Jangan gunakan teknik ini di sistem milik orang lain.

---

## 📋 Deskripsi

Project ini adalah aplikasi web edukasi berbasis **Express.js + EJS** yang mensimulasikan 3 kerentanan keamanan web umum:

1. **File Upload Vulnerability** – Upload file tanpa validasi
2. **Information Disclosure** – Kebocoran data sensitif melalui API
3. **Race Condition** – Eksploitasi celah waktu antara check dan update

Setiap lab memiliki dua versi:
- **Versi Vulnerable** – sengaja rentan untuk demonstrasi
- **Versi Fixed/Aman** – implementasi yang benar sebagai solusi

---

## 🗂️ Struktur Project

```
cyber-lab-week4/
├── app.js                          # Entry point Express
├── package.json
├── README.md
├── .env.example
├── data/
│   ├── database.sqlite             # SQLite (auto-dibuat)
│   └── secure-uploads/            # Folder upload aman (tidak publik)
├── public/
│   ├── css/
│   │   └── style.css
│   └── uploads/                   # Folder upload vulnerable (publik)
│       └── fake-webshell.html     # Demo file simulasi
├── views/
│   ├── partials/
│   │   ├── navbar.ejs
│   │   └── footer.ejs
│   ├── index.ejs                  # Halaman utama
│   ├── error.ejs                  # Halaman error
│   ├── file-upload.ejs            # LAB 1 – Vulnerable
│   ├── fixed-file-upload.ejs      # LAB 1 – Fixed
│   ├── information-disclosure.ejs # LAB 2 – Vulnerable
│   ├── fixed-information-disclosure.ejs # LAB 2 – Fixed
│   ├── race-condition.ejs         # LAB 3 – Vulnerable
│   └── fixed-race-condition.ejs   # LAB 3 – Fixed
└── routes/
    ├── fileUpload.js
    ├── informationDisclosure.js
    └── raceCondition.js
```

---

## 🚀 Cara Install & Menjalankan

### 1. Install Dependencies

```bash
cd cyber-lab-week4
npm install
```

### 2. Konfigurasi Environment (opsional)

```bash
cp .env.example .env
```

### 3. Jalankan Server

**Mode Development (dengan nodemon):**
```bash
npm run dev
```

**Mode Production:**
```bash
npm start
```

### 4. Akses di Browser

```
http://localhost:3000
```

---

## 🗺️ Daftar Route

| Route | Deskripsi |
|---|---|
| `GET /` | Halaman utama |
| `GET /lab/file-upload` | LAB 1 – File Upload (Vulnerable) |
| `POST /lab/file-upload/upload` | Upload endpoint (Vulnerable) |
| `GET /lab/file-upload/fixed` | LAB 1 – File Upload (Fixed) |
| `POST /lab/file-upload/fixed/upload` | Upload endpoint (Fixed) |
| `GET /lab/information-disclosure` | LAB 2 – Info Disclosure (Vulnerable) |
| `GET /lab/information-disclosure/profile` | API: user data bocor |
| `GET /lab/information-disclosure/debug` | API: debug/config bocor |
| `GET /lab/information-disclosure/error` | API: stack trace bocor |
| `GET /lab/information-disclosure/fixed` | LAB 2 – Info Disclosure (Fixed) |
| `GET /lab/information-disclosure/fixed/profile` | API: user data aman |
| `GET /lab/information-disclosure/fixed/debug` | API: debug ditutup (403) |
| `GET /lab/information-disclosure/fixed/error` | API: error aman |
| `GET /lab/race-condition` | LAB 3 – Race Condition (Vulnerable) |
| `POST /lab/race-condition/redeem` | Redeem voucher (Vulnerable) |
| `POST /lab/race-condition/reset` | Reset data |
| `GET /lab/race-condition/fixed` | LAB 3 – Race Condition (Fixed) |
| `POST /lab/race-condition/fixed/redeem` | Redeem voucher (Fixed) |
| `POST /lab/race-condition/fixed/reset` | Reset data |

---

## 🧪 Penjelasan Masing-masing Lab

### LAB 1: File Upload Vulnerability

**Konsep:** Aplikasi menerima file tanpa validasi extension, MIME type, ukuran, atau isi file.

**Cara Demo Vulnerable:**
1. Buka `/lab/file-upload`
2. Upload file apapun (`.html`, `.txt`, `.exe`, dll)
3. File tersimpan di `public/uploads/` dan bisa diakses via URL

**Yang Diperbaiki di Fixed Version:**
- Whitelist extension: `.jpg`, `.jpeg`, `.png`, `.pdf`
- Validasi MIME type: `image/jpeg`, `image/png`, `application/pdf`
- Batas ukuran: maksimal 1 MB
- Rename file dengan UUID
- Simpan di `data/secure-uploads/` (tidak publik)

---

### LAB 2: Information Disclosure

**Konsep:** Aplikasi membocorkan informasi sensitif melalui API response, debug endpoint, dan error stack trace.

**Cara Demo Vulnerable:**
1. Buka `/lab/information-disclosure`
2. Klik tombol "Profile Leak" → lihat field internal (internalUserId, debugNote, dll)
3. Klik tombol "Debug Leak" → lihat secret key, config server
4. Klik tombol "Error Stack Trace" → lihat stack trace aplikasi

**Yang Diperbaiki di Fixed Version:**
- API profile hanya kembalikan `name` dan `email`
- Debug endpoint ditutup dengan 403 Forbidden
- Error handler menampilkan pesan umum, detail di server log

---

### LAB 3: Race Condition

**Konsep:** Voucher sekali pakai bisa diredeem berkali-kali karena pengecekan dan update dilakukan terpisah dengan delay di antaranya.

**Cara Demo Vulnerable:**
1. Buka `/lab/race-condition`
2. Klik "Simulasikan 10 Request Bersamaan"
3. Perhatikan: lebih dari 1 request bisa berhasil → poin bertambah > 100

**Yang Diperbaiki di Fixed Version (3 Teknik):**
1. **In-Memory Lock:** Flag `isRedeeming = true` mencegah concurrent request
2. **Transaksi SQLite:** Pengecekan + update dalam satu transaksi atomik
3. **Unique Constraint:** Tabel `redemptions` dengan UNIQUE constraint mencegah duplikasi di level DB

---

## ⚙️ Dependencies

| Package | Versi | Fungsi |
|---|---|---|
| express | ^4.19.2 | Web framework |
| ejs | ^3.1.10 | Template engine |
| multer | ^1.4.5-lts.1 | File upload middleware |
| better-sqlite3 | ^9.4.3 | Database SQLite |
| uuid | ^9.0.1 | Generate UUID untuk rename file |
| dotenv | ^16.4.5 | Environment variables |
| nodemon | ^3.1.3 | Auto-restart development (devDep) |

---

## 🔒 Catatan Keamanan

1. **Project hanya untuk edukasi** – Jangan gunakan di sistem milik orang lain
2. **Jangan deploy ke internet** – Ini adalah lab lokal
3. **Jangan gunakan data asli** – Semua data di lab ini adalah dummy/palsu
4. **Tidak ada malware** – File simulasi hanya teks biasa, tidak ada kode berbahaya
5. **Tidak ada eksekusi OS command** – Lab tidak menjalankan perintah sistem apapun
6. **Data dummy** – Token, secret key, dan credential di lab ini tidak valid di mana pun

---

## 📚 Referensi Pembelajaran

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OWASP File Upload Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html)
- [OWASP Information Exposure](https://owasp.org/www-community/vulnerabilities/Information_exposure_through_query_strings_in_url)
- [OWASP Race Condition](https://owasp.org/www-community/vulnerabilities/Race_Condition)
- [PortSwigger Web Security Academy](https://portswigger.net/web-security)

---

*Cyber Security LAB – Week 4 | Advanced Attack & Simulation*
