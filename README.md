# Sistem Analisis Kesesuaian Kompetensi Mahasiswa terhadap Lowongan Kerja

Aplikasi berbasis web untuk menganalisis kesesuaian kompetensi mahasiswa terhadap persyaratan lowongan kerja menggunakan metode **Profile Matching** dan dukungan **Artificial Intelligence (AI)**.

Aplikasi membantu mahasiswa mengelola profil kompetensi, mengunggah CV, memperoleh hasil pencocokan lowongan, serta mendapatkan rekomendasi pengembangan kompetensi.

## Fitur Aplikasi

### Mahasiswa
- Registrasi dan login.
- Dashboard dan pengelolaan profil mahasiswa.
- Upload dan pengelolaan CV.
- Ekstraksi informasi CV menggunakan AI.
- Konfirmasi dan penyimpanan hasil ekstraksi CV.
- Pengelolaan kompetensi mahasiswa.
- Pencocokan kompetensi dengan persyaratan lowongan kerja menggunakan Profile Matching.
- Perhitungan pencocokan seluruh lowongan aktif.
- Peringkat kesesuaian lowongan kerja.
- Rekomendasi pengembangan kompetensi menggunakan AI.

### Admin
- Dashboard admin.
- Pengelolaan data lowongan kerja.
- Pengelolaan kompetensi persyaratan lowongan.
- Verifikasi kompetensi mahasiswa.
- Persetujuan dan penolakan pengajuan kompetensi.
- Riwayat verifikasi kompetensi.

## Teknologi yang Digunakan

| Teknologi | Keterangan |
|---|---|
| PHP 8.3+ | Bahasa pemrograman backend |
| Laravel 13 | Framework aplikasi web |
| MySQL | Database relasional |
| Tailwind CSS 4 | Styling antarmuka |
| Vite 7 | Frontend build tool |
| JavaScript | Interaksi frontend |
| Groq API | Layanan AI untuk ekstraksi CV dan rekomendasi |
| PHPWord | Pengolahan dokumen Word |
| Smalot PDF Parser | Pembacaan dokumen PDF |
| PHPUnit 12 | Pengujian otomatis |

## Persyaratan Sistem

Pastikan perangkat telah memiliki:
- PHP 8.3 atau lebih baru.
- Composer.
- MySQL.
- Node.js dan npm.
- Git.

## Instalasi dan Konfigurasi

### 1. Clone Repository

```bash
git clone https://github.com/heyitsmedewa/sistem-analisis-kompetensi.git
cd sistem-analisis-kompetensi
```

### 2. Instal Dependensi Backend

```bash
composer install
```

### 3. Instal Dependensi Frontend

```bash
npm install
```

### 4. Siapkan File Environment

Untuk Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

### 5. Buat Application Key

```bash
php artisan key:generate
```

### 6. Konfigurasi Database

Buat database MySQL dengan nama `skripsi_kompetensi`.

Sesuaikan konfigurasi pada file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=skripsi_kompetensi
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan username dan password dengan konfigurasi MySQL masing-masing.

### 7. Konfigurasi Layanan AI

Aplikasi menggunakan Groq API untuk proses ekstraksi CV dan pembuatan rekomendasi AI.

Tambahkan konfigurasi berikut pada file `.env`:

```env
GROQ_API_KEY=
GROQ_MODEL=
```

Isi dengan API key dan nama model Groq yang tersedia. Jangan mengunggah API key ke repository publik.

### 8. Jalankan Migrasi dan Seeder

```bash
php artisan migrate --seed
```

Seeder menyiapkan:
- Role `admin` dan `mahasiswa`.
- Data kompetensi.
- Data sinonim kompetensi.

Seeder tidak membuat akun pengguna demo secara otomatis.

### 9. Build Frontend

```bash
npm run build
```

### 10. Jalankan Aplikasi

```bash
php artisan serve
```

Akses aplikasi melalui:

`http://127.0.0.1:8000`

## Pengujian

Untuk menjalankan seluruh pengujian otomatis:

```bash
php artisan test
```

Pengujian menggunakan konfigurasi testing pada `phpunit.xml`, termasuk database SQLite in-memory.

## Struktur Direktori

```text
app/
├── Http/
│   └── Controllers/
├── Models/
└── Services/

config/
database/
├── migrations/
├── factories/
└── seeders/

resources/
├── css/
├── js/
└── views/

routes/
└── web.php

tests/
├── Feature/
└── Unit/
```

## Role Pengguna

| Role | Hak Akses |
|---|---|
| Admin | Mengelola lowongan kerja, kompetensi persyaratan, dan verifikasi kompetensi mahasiswa |
| Mahasiswa | Mengelola profil, CV, kompetensi, pencocokan lowongan, dan rekomendasi AI |

## Catatan Keamanan

- Jangan mengunggah file `.env` ke repository.
- Jangan menyimpan API key atau kredensial database dalam source code.
- Gunakan konfigurasi environment yang sesuai dengan server deployment.
- Untuk lingkungan produksi, gunakan `APP_DEBUG=false`.
- Gunakan kredensial database yang aman.

## Repository

[GitHub - Sistem Analisis Kompetensi](https://github.com/heyitsmedewa/sistem-analisis-kompetensi)

## Lisensi

Proyek ini dikembangkan sebagai aplikasi tugas dan penelitian akademik.