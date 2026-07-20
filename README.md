# SICERDAS Mukomuko

Sistem Informasi Creative Financing Daerah dan Tanggung Jawab Sosial Perusahaan Kabupaten Mukomuko.

Aplikasi web PHP native (pola MVC) dengan database MySQL untuk mendata mitra/pihak swasta, mengelola katalog program prioritas daerah, mencatat komitmen dan realisasi pendanaan non-APBD, serta memfasilitasi pelaporan dan verifikasi CSR/TJSL.

## Fitur

- **Autentikasi & RBAC** — 6 peran: Super Admin, Admin BAPPERIDA, Verifikator, OPD, Mitra/Perusahaan, Pimpinan Daerah. Proteksi CSRF, password bcrypt, session hardening.
- **Master Data** — 10 referensi (tahun anggaran, periode laporan, sumber pendanaan, bentuk kontribusi, bidang program, bidang usaha, jenis badan usaha, OPD, kecamatan, desa) dengan CRUD generik.
- **Pihak Swasta/Mitra** — profil lengkap, PIC CSR, dokumen legal, status kepatuhan, riwayat komitmen dan laporan.
- **Program Prioritas** — alur usulan OPD → verifikasi BAPPERIDA → publikasi ke katalog, dengan perhitungan funding gap otomatis.
- **Katalog Program** — katalog internal mitra (dengan aksi "Tertarik Mendukung") dan katalog publik.
- **Komitmen & Realisasi** — dukungan multi-mitra per program, realisasi bertahap dengan bukti, validasi nilai tidak melebihi sisa kebutuhan/komitmen, status otomatis.
- **Laporan CSR** — multi-kegiatan per laporan, lampiran, alur verifikasi (setujui & kunci / minta revisi / tolak), nomor registrasi otomatis, cetak PDF.
- **Dashboard per peran** — KPI, grafik komitmen vs realisasi (Chart.js), program mendesak, perusahaan belum melapor.
- **Rekapitulasi & Ekspor** — rekap per perusahaan/bidang/kecamatan dengan filter tahun & periode, ekspor Excel dan cetak/PDF.
- **Audit Trail & Notifikasi** — pencatatan seluruh aktivitas penting dan notifikasi dalam aplikasi.
- **Portal Transparansi Publik** — landing page, statistik agregat, katalog program, mitra pendukung.
- **Multibahasa** — Indonesia (default) dan English (portal mitra/perusahaan dan halaman publik).
- **Tabel server-side** — seluruh tabel daftar menggunakan DataTables server-side processing (nomor urut di kiri, ikon aksi di kanan untuk data master).
- **Responsif** — sidebar off-canvas di layar kecil, kartu dan tabel adaptif.

## Kebutuhan

- PHP >= 8.1 (ekstensi: pdo_mysql, mbstring, fileinfo)
- MySQL >= 8.0 (atau MariaDB >= 10.6)

## Instalasi

1. Buat database dan user MySQL:

```sql
CREATE DATABASE sicerdas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sicerdas'@'localhost' IDENTIFIED BY 'sicerdas123';
GRANT ALL PRIVILEGES ON sicerdas.* TO 'sicerdas'@'localhost';
```

2. Sesuaikan koneksi melalui environment variable (opsional, lihat `app/Config/config.php`): `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.

3. Jalankan seeder (membuat skema + data master + akun demo):

```bash
php database/seed.php
```

4. Jalankan server pengembangan:

```bash
php -S 0.0.0.0:8000 -t public
```

Untuk produksi, arahkan document root web server (Nginx/Apache) ke folder `public/` dan rutekan semua permintaan non-file ke `public/index.php`.

## Akun Demo

Semua akun menggunakan kata sandi `password123`:

| Email | Peran |
| --- | --- |
| superadmin@mukomukokab.go.id | Super Administrator |
| admin@mukomukokab.go.id | Admin BAPPERIDA |
| verifikator@mukomukokab.go.id | Verifikator BAPPERIDA |
| disdik@mukomukokab.go.id | OPD (Dinas Pendidikan) |
| mitra@agromuko.co.id | Mitra/Perusahaan |
| pimpinan@mukomukokab.go.id | Pimpinan Daerah |

## Struktur Proyek

```
app/
  Config/       # konfigurasi & definisi rute
  Controllers/  # controller per modul
  Core/         # mini-framework: router, DB, auth, CSRF, i18n, DataTable, audit, upload
  Models/       # logika domain (funding gap, status program)
  Views/        # template PHP per modul + layout
database/
  schema.sql    # skema MySQL
  seed.php      # seeder skema + data awal + akun demo
lang/           # kamus bahasa id/en
public/         # document root (index.php, aset, upload)
```
