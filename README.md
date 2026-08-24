# Attendly — Smart Employee Attendance System

Sistem absensi karyawan berbasis web yang modern, dilengkapi verifikasi selfie kamera dan validasi GPS real-time. Dibangun di atas Laravel 13 dengan antarmuka yang responsif dan mendukung mode gelap/terang.

---

## Daftar Isi

1. [Gambaran Umum](#gambaran-umum)
2. [Teknologi yang Digunakan](#teknologi-yang-digunakan)
3. [Alur Sistem](#alur-sistem)
4. [Fitur Utama](#fitur-utama)
5. [Struktur Pengguna & Peran](#struktur-pengguna--peran)
6. [Alur Absensi Karyawan](#alur-absensi-karyawan)
7. [Panel Admin](#panel-admin)
8. [Struktur Database](#struktur-database)
9. [Keamanan & Validasi](#keamanan--validasi)
10. [Cara Instalasi](#cara-instalasi)

---

## Gambaran Umum

**Attendly** adalah sistem manajemen kehadiran karyawan yang menggantikan sistem absensi manual atau fingerprint konvensional. Karyawan melakukan absensi langsung dari browser smartphone mereka dengan dua lapis verifikasi:

1. **Selfie Kamera** — memastikan karyawan yang absen adalah orangnya langsung
2. **Validasi GPS** — memastikan karyawan berada di dalam radius lokasi kantor yang telah ditentukan

Admin dapat memantau kehadiran seluruh karyawan secara real-time, mengekspor laporan, dan mengatur jadwal kerja per kantor cabang.

---

## Teknologi yang Digunakan

| Komponen | Teknologi |
|---|---|
| Backend Framework | Laravel 13 (PHP 8.3) |
| Database | SQLite (dapat diganti MySQL/PostgreSQL) |
| Frontend Styling | Tailwind CSS (via CDN) |
| Ikon | Lucide Icons |
| Export Laporan | PhpSpreadsheet (Excel) + DomPDF (PDF) |
| GPS Calculation | Haversine Formula (server-side) |
| Kamera | Browser Web API (`getUserMedia`) |
| Autentikasi | Laravel Session Auth |

---

## Alur Sistem

Berikut gambaran besar bagaimana sistem bekerja dari awal hingga akhir:

```
[ Karyawan buka browser ]
        │
        ▼
[ Login dengan Email & Password ]
        │
        ├─── Role: admin  ──▶ [ Admin Panel Dashboard ]
        │
        └─── Role: employee ──▶ [ Dashboard Karyawan ]
                                        │
                              ┌─────────┴─────────┐
                              ▼                   ▼
                       [ Absen Masuk ]     [ Absen Pulang ]
                              │                   │
                    ┌─────────┴──┐       ┌────────┴──┐
                    ▼            ▼       ▼           ▼
               [Buka Kamera] [GPS Lock] [Buka Kamera] [GPS Lock]
                    │            │           │           │
                    └────────────┘           └───────────┘
                          │                       │
                    [ Ambil Selfie ]        [ Ambil Selfie ]
                          │                       │
                    [ Submit ke Server ]    [ Submit ke Server ]
                          │                       │
               ┌──────────┴──────────┐   ┌────────┴─────────┐
               ▼                     ▼   ▼                   ▼
        [Validasi GPS]        [Cek Status]  [Validasi GPS]  [Simpan Data]
        [Hitung Jarak]        [On Time /]   [Hitung Jarak]
        [Simpan Foto]         [ Terlambat]  [Simpan Foto]
               │                     │
               └─────────────────────┘
                          │
               [ Data tersimpan di DB ]
                          │
               [ Audit Log tercatat ]
                          │
               [ Admin dapat memantau real-time ]
```

---

## Fitur Utama

### Untuk Karyawan
- **Dashboard harian** — menampilkan status absensi hari ini, jam masuk/pulang, dan statistik bulan berjalan
- **Absen Masuk** — verifikasi selfie + GPS, mendeteksi status tepat waktu atau terlambat otomatis
- **Absen Pulang** — verifikasi selfie + GPS saat pulang
- **Riwayat Absensi** — filter per bulan dan tahun, tampil rekap statistik
- **Profil Karyawan** — informasi data diri, departemen, posisi, dan jadwal kerja yang berlaku
- **Offline Detection** — banner peringatan otomatis jika koneksi internet terputus
- **Dark / Light Mode** — toggle tema gelap dan terang, tersimpan di browser

### Untuk Admin
- **Dashboard Analitik** — ringkasan hadir, terlambat, belum checkout, absen hari ini + grafik tren 7 hari
- **Monitoring Presensi** — filter real-time berdasarkan tanggal, cabang, departemen, dan nama/NIK karyawan
- **Detail Presensi** — lihat foto selfie masuk/pulang, koordinat GPS, akurasi, jarak ke kantor, dan peta lokasi (OpenStreetMap)
- **Manajemen Karyawan** — tambah, edit, nonaktifkan akun karyawan
- **Manajemen Kantor Cabang** — atur koordinat GPS, radius absensi, jam kerja, toleransi keterlambatan per cabang
- **Manajemen Departemen & Jabatan** — master data struktur organisasi
- **Laporan & Ekspor** — filter multi-kriteria, ekspor ke Excel (.xlsx) dan PDF
- **Pengaturan Jam Kerja** — konfigurasi jadwal per cabang
- **Audit Trail Log** — rekam jejak seluruh aktivitas sistem (login, absensi, ekspor, manipulasi data)

---

## Struktur Pengguna & Peran

Sistem memiliki dua peran yang sepenuhnya terpisah:

```
User
├── role: "admin"
│     └── Akses ke /admin/* (Admin Panel)
│         Tidak memiliki data Employee
│
└── role: "employee"
      └── Akses ke /employee/* (Portal Karyawan)
          Memiliki data Employee yang terhubung
```

Pemisahan akses dijaga oleh middleware `RoleMiddleware` dan `EnsureActiveEmployee`. Jika karyawan dinonaktifkan oleh admin, akses ke portal karyawan akan otomatis diblokir.

---

## Alur Absensi Karyawan

### Proses Absen Masuk (Check In)

```
Karyawan tekan tombol "ABSEN MASUK"
          │
          ▼
Browser minta izin Kamera + GPS
          │
    ┌─────┴─────┐
    ▼           ▼
[Kamera ON]  [GPS Lock]
    │           │ (koordinat + akurasi)
    └─────┬─────┘
          │
    Ambil foto selfie
          │
    Tekan "Konfirmasi Absen"
          │
    POST /employee/attendance/checkin
    {photo: base64, lat, lng, accuracy}
          │
    ════════════════ SERVER ════════════════
          │
    1. Cek status karyawan (aktif?)
    2. Cek cabang aktif & attendance enabled
    3. Cek hari kerja (WorkingDay)
    4. Cek duplikasi absensi hari ini
    5. Validasi GPS:
       ├── Akurasi GPS ≤ batas minimum? ✓
       └── Jarak (Haversine) ≤ radius kantor? ✓
    6. Simpan foto ke storage (private)
    7. Hitung status: On Time / Terlambat
       (jam masuk vs jam kerja + toleransi)
    8. Simpan record Attendance ke database
    9. Catat Audit Log
          │
    ════════════════════════════════════════
          │
    Response JSON: {success, jam masuk, status, jarak}
          │
    Modal sukses muncul di layar karyawan
```

### Proses Absen Pulang (Check Out)

Alur yang sama dengan check-in, dengan tambahan:
- Server memastikan record check-in hari ini sudah ada
- Menghitung status `early_leave` jika pulang sebelum jam kerja selesai
- Status kehadiran keseluruhan (`overall_status`) tetap `late` jika karyawan masuk terlambat

---

## Panel Admin

### Monitoring Presensi Real-time

Admin dapat memfilter data presensi berdasarkan:
- **Tanggal** — default hari ini
- **Kantor Cabang** — filter per lokasi
- **Departemen** — filter per divisi
- **Pencarian** — nama karyawan atau NIK

Ringkasan statistik di bagian atas halaman menampilkan jumlah **Total Hadir**, **Tepat Waktu**, **Terlambat**, **Belum Checkout**, dan **Belum Hadir** secara otomatis.

### Detail Presensi

Setiap record absensi dapat dibuka untuk melihat:
- Foto selfie masuk dan pulang
- Koordinat GPS beserta akurasi sinyal
- Jarak aktual karyawan dari kantor saat absen
- Peta lokasi interaktif (OpenStreetMap embed)

### Konfigurasi Per Cabang

Setiap kantor cabang memiliki pengaturan independen:

| Pengaturan | Keterangan |
|---|---|
| Koordinat GPS | Titik pusat lokasi kantor (latitude & longitude) |
| Radius Absensi | Jarak maksimal karyawan boleh absen (meter) |
| Jam Masuk Standar | Waktu kerja dimulai |
| Jam Pulang Standar | Waktu kerja berakhir |
| Toleransi Terlambat | Batas menit sebelum dianggap terlambat |
| Akurasi GPS Minimum | Batas akurasi sinyal GPS yang diterima |
| Zona Waktu | WIB / WITA / WIT (timezone per cabang) |
| Hari Kerja | Konfigurasi hari aktif (Senin-Jumat, dll) |

---

## Struktur Database

```
users                    ← Akun login (admin / employee)
│
├── employees            ← Data profil karyawan
│   ├── department_id   → departments
│   ├── position_id     → positions
│   └── branch_id       → branches
│
branches                 ← Data kantor cabang
│   ├── attendance_settings  ← Jam kerja, radius, toleransi
│   └── working_days         ← Jadwal hari kerja per cabang
│
attendances              ← Record absensi harian
│   ├── employee_id     → employees
│   ├── branch_id       → branches
│   ├── check_in_at / check_out_at
│   ├── foto selfie (path, disimpan private)
│   ├── koordinat GPS (lat, lng, accuracy, distance)
│   └── status (on_time / late / early_leave / present)
│
audit_logs               ← Jejak aktivitas sistem
    ├── user_id         → users
    ├── module          (attendance, employee, branch, dll)
    ├── action          (CHECK_IN, LOGIN, EXPORT, dll)
    ├── ip_address
    └── metadata        (JSON detail kejadian)
```

---

## Keamanan & Validasi

| Lapisan | Mekanisme |
|---|---|
| Autentikasi | Laravel Session Auth — semua route dilindungi middleware `auth` |
| Otorisasi Peran | `RoleMiddleware` — memisahkan akses admin dan karyawan |
| Status Karyawan | `EnsureActiveEmployee` — karyawan nonaktif diblokir otomatis |
| Validasi GPS | Haversine formula di server, tidak bergantung pada data dari client |
| Akurasi GPS | Batas minimum akurasi sinyal dapat dikonfigurasi per cabang |
| Foto Absensi | Disimpan di storage **private** (tidak dapat diakses langsung via URL) |
| Akses Foto | Dilindungi oleh `AttendancePolicy` — hanya pemilik atau admin yang bisa melihat |
| Duplikasi Absensi | Pengecekan dengan `lockForUpdate()` (database-level lock) mencegah race condition |
| Audit Trail | Setiap aksi kritis dicatat otomatis (IP, waktu, user, payload) |
| CSRF | Semua form dilindungi Laravel CSRF token |

---

## Cara Instalasi

### Prasyarat

- PHP >= 8.3
- Composer
- Node.js (untuk build asset, opsional karena menggunakan CDN)

### Langkah Instalasi

```bash
# 1. Clone repository
git clone <repo-url> attendly
cd attendly

# 2. Install dependencies PHP
composer install

# 3. Salin file environment
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Buat database SQLite (atau konfigurasi MySQL di .env)
touch database/database.sqlite

# 6. Jalankan migrasi & seeder
php artisan migrate --seed

# 7. Buat symlink storage
php artisan storage:link

# 8. Jalankan server lokal
php artisan serve
```

Atau gunakan shortcut composer:

```bash
composer setup
```

### Akun Default (Setelah Seeder)

| Role | Email | Password |
|---|---|---|
| Administrator | admin@example.com | password |
| Karyawan Demo | employee@example.com | password |

> **Catatan:** Ganti password default sebelum deploy ke production.

---

## Struktur Folder Utama

```
attendly/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          ← Controller panel admin
│   │   │   ├── Employee/       ← Controller portal karyawan
│   │   │   └── Auth/           ← Login / Logout
│   │   └── Middleware/
│   │       ├── RoleMiddleware.php
│   │       └── EnsureActiveEmployee.php
│   ├── Models/                 ← Eloquent models
│   └── Services/
│       ├── AttendanceService.php   ← Logika check-in / check-out
│       ├── GeolocationService.php  ← Haversine & validasi radius
│       ├── FileUploadService.php   ← Simpan & ambil foto
│       └── AuditLogService.php     ← Pencatatan aktivitas
├── database/
│   └── migrations/             ← Skema database
├── resources/
│   └── views/
│       ├── layouts/            ← Template utama (admin, employee, auth)
│       ├── admin/              ← Halaman panel admin
│       ├── employee/           ← Halaman portal karyawan
│       └── auth/               ← Halaman login
└── routes/
    └── web.php                 ← Definisi seluruh route
```

---

*Attendly — Dibuat dengan Laravel 13. Dokumentasi ini ditujukan untuk keperluan handover dan onboarding klien.*
