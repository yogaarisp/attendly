# Spesifikasi Fitur: Form Cuti & Izin Karyawan

> Status: **BELUM DIEKSEKUSI** — dokumen ini hanya rencana implementasi  
> Dibuat: August 2026  
> Prioritas: Medium

---

## Daftar Isi

1. [Gambaran Fitur](#gambaran-fitur)
2. [Tipe Pengajuan](#tipe-pengajuan)
3. [Alur Sistem](#alur-sistem)
4. [Database](#database)
5. [File yang Perlu Dibuat](#file-yang-perlu-dibuat)
6. [Detail Implementasi](#detail-implementasi)
7. [UI / UX Notes](#ui--ux-notes)

---

## Gambaran Fitur

Karyawan dapat mengajukan permohonan **Cuti** atau **Izin** langsung dari portal karyawan. Admin menerima notifikasi dan dapat menyetujui atau menolak pengajuan tersebut beserta catatan alasan.

**Siapa yang terlibat:**
- **Karyawan** — mengajukan, melihat status, membatalkan (jika masih pending)
- **Admin** — melihat semua pengajuan, approve, reject, tambah catatan

---

## Tipe Pengajuan

### Cuti (`type: leave`)

| Kode | Label | Keterangan |
|---|---|---|
| `annual` | Cuti Tahunan | Jatah cuti tahunan karyawan |
| `sick` | Cuti Sakit | Sakit dengan/tanpa surat dokter |
| `maternity` | Cuti Melahirkan | Khusus karyawan perempuan |
| `important` | Cuti Penting | Pernikahan, kematian keluarga, dll |
| `unpaid` | Cuti Tanpa Bayar | Di luar jatah cuti |

### Izin (`type: permission`)

| Kode | Label | Keterangan |
|---|---|---|
| `late` | Izin Terlambat | Konfirmasi datang terlambat |
| `early_leave` | Izin Pulang Awal | Perlu pulang sebelum jam kerja selesai |
| `absence` | Izin Tidak Masuk | Tidak hadir 1 hari penuh (non-cuti) |
| `wfh` | Work From Home | Bekerja dari rumah |

---

## Alur Sistem

```
[ Karyawan ]
     │
     ▼
Isi Form Pengajuan
(tipe, tanggal mulai, tanggal selesai, alasan, upload lampiran opsional)
     │
     ▼
Status: PENDING ──────────────────────────────────┐
     │                                             │
     │                                    Karyawan bisa BATALKAN
     │                                    (selama masih PENDING)
     ▼
[ Admin menerima pengajuan ]
     │
     ├── APPROVE ──▶ Status: APPROVED
     │               Karyawan dapat notifikasi
     │               Absensi hari tersebut otomatis tercatat "Cuti/Izin"
     │
     └── REJECT  ──▶ Status: REJECTED
                     Admin wajib isi catatan alasan penolakan
                     Karyawan dapat notifikasi
```

---

## Database

### Tabel: `leave_requests`

```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
employee_id         BIGINT UNSIGNED NOT NULL  -- FK ke employees
branch_id           BIGINT UNSIGNED NOT NULL  -- FK ke branches
type                ENUM('leave', 'permission') NOT NULL
category            VARCHAR(50) NOT NULL       -- annual, sick, late, dll
start_date          DATE NOT NULL
end_date            DATE NOT NULL              -- sama dengan start_date jika 1 hari
total_days          TINYINT UNSIGNED NOT NULL  -- dihitung otomatis
reason              TEXT NOT NULL              -- alasan pengajuan
attachment          VARCHAR(255) NULL          -- path file lampiran (opsional)
status              ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending'
reviewed_by         BIGINT UNSIGNED NULL       -- FK ke users (admin yang review)
reviewed_at         TIMESTAMP NULL
admin_note          TEXT NULL                  -- catatan approve/reject dari admin
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEX: employee_id, status, start_date
```

### Relasi

```
leave_requests
├── employee_id  → employees.id
├── branch_id    → branches.id
└── reviewed_by  → users.id
```

---

## File yang Perlu Dibuat

### Migration
```
database/migrations/2026_08_23_000011_create_leave_requests_table.php
```

### Model
```
app/Models/LeaveRequest.php
```

### Controllers
```
app/Http/Controllers/Employee/LeaveRequestController.php
app/Http/Controllers/Admin/LeaveRequestController.php
```

### Views Employee
```
resources/views/employee/leave/index.blade.php    ← riwayat + tombol ajukan
resources/views/employee/leave/create.blade.php   ← form pengajuan baru
resources/views/employee/leave/show.blade.php     ← detail status pengajuan
```

### Views Admin
```
resources/views/admin/leave_requests/index.blade.php   ← list semua pengajuan
resources/views/admin/leave_requests/show.blade.php    ← detail + form approve/reject
```

### Routes (tambahan di `routes/web.php`)
```php
// Employee
Route::get('/employee/leave', [LeaveRequestController::class, 'index'])
    ->name('employee.leave.index');
Route::get('/employee/leave/create', [LeaveRequestController::class, 'create'])
    ->name('employee.leave.create');
Route::post('/employee/leave', [LeaveRequestController::class, 'store'])
    ->name('employee.leave.store');
Route::get('/employee/leave/{id}', [LeaveRequestController::class, 'show'])
    ->name('employee.leave.show');
Route::patch('/employee/leave/{id}/cancel', [LeaveRequestController::class, 'cancel'])
    ->name('employee.leave.cancel');

// Admin
Route::get('/admin/leave-requests', [AdminLeaveRequestController::class, 'index'])
    ->name('admin.leave-requests.index');
Route::get('/admin/leave-requests/{id}', [AdminLeaveRequestController::class, 'show'])
    ->name('admin.leave-requests.show');
Route::patch('/admin/leave-requests/{id}/approve', [AdminLeaveRequestController::class, 'approve'])
    ->name('admin.leave-requests.approve');
Route::patch('/admin/leave-requests/{id}/reject', [AdminLeaveRequestController::class, 'reject'])
    ->name('admin.leave-requests.reject');
```

### Sidebar Admin (tambahan di `layouts/admin.blade.php`)
```html
<!-- di section OPERASIONAL -->
<a href="{{ route('admin.leave-requests.index') }}">
    Cuti & Izin
</a>
```

### Navbar Employee (tambahan di `layouts/employee.blade.php`)
```html
<!-- tab baru atau akses dari menu history -->
```

---

## Detail Implementasi

### `LeaveRequest` Model

```php
// Relasi
public function employee(): BelongsTo
public function branch(): BelongsTo
public function reviewer(): BelongsTo  // user admin

// Helper methods
public function isPending(): bool
public function isApproved(): bool
public function isRejected(): bool
public function isCancelled(): bool
public function canBeCancelled(): bool  // hanya jika pending

// Accessor
public function getStatusLabelAttribute(): string
public function getCategoryLabelAttribute(): string
public function getTotalDaysAttribute(): int  // hitung dari start - end date

// Casts
'start_date'   => 'date'
'end_date'     => 'date'
'reviewed_at'  => 'datetime'
```

### `Employee\LeaveRequestController`

```php
index()   — tampilkan riwayat pengajuan milik karyawan login
           filter: status, bulan, tahun
           tampil total pending, approved, rejected

create()  — tampilkan form pengajuan
           validasi: tidak boleh overlap dengan pengajuan lain yang pending/approved

store()   — simpan pengajuan baru
           hitung total_days otomatis (exclude hari libur jika perlu)
           simpan attachment jika ada
           catat audit log: LEAVE_REQUEST_CREATED

show()    — detail satu pengajuan
           tampil status, catatan admin, timeline

cancel()  — batalkan pengajuan
           hanya boleh jika status == pending
           catat audit log: LEAVE_REQUEST_CANCELLED
```

### `Admin\LeaveRequestController`

```php
index()   — list semua pengajuan dari semua karyawan
           filter: status, tipe, cabang, departemen, tanggal
           tampil badge counter per status

show()    — detail pengajuan
           tampil info karyawan, alasan, lampiran
           form approve/reject dengan kolom admin_note

approve() — set status = approved, isi reviewed_by & reviewed_at
           admin_note opsional
           catat audit log: LEAVE_REQUEST_APPROVED

reject()  — set status = rejected, isi reviewed_by & reviewed_at
           admin_note WAJIB diisi
           catat audit log: LEAVE_REQUEST_REJECTED
```

### Validasi Form Pengajuan (store)

```php
'type'       => required | in:leave,permission
'category'   => required | string
'start_date' => required | date | after_or_equal:today
'end_date'   => required | date | after_or_equal:start_date
'reason'     => required | string | min:10 | max:1000
'attachment' => nullable | file | mimes:pdf,jpg,jpeg,png | max:2048
```

---

## UI / UX Notes

### Form Pengajuan (Employee)

- Dropdown **Tipe** (Cuti / Izin) → otomatis filter **Kategori** yang relevan
- Date picker **Tanggal Mulai** dan **Tanggal Selesai**
- Counter otomatis tampil **"X hari kerja"** saat tanggal dipilih
- Field **Alasan** textarea minimal 10 karakter
- Upload **Lampiran** opsional (PDF/foto, max 2MB)
- Tombol **Kirim Pengajuan**

### Halaman Riwayat (Employee)

- Tab atau filter: Semua / Pending / Disetujui / Ditolak
- Setiap card: tipe, kategori, tanggal, durasi, status badge berwarna
- Status badge:
  - `pending` → kuning
  - `approved` → hijau
  - `rejected` → merah
  - `cancelled` → abu

### Halaman Admin

- Tabel dengan kolom: Karyawan, Tipe, Tanggal, Durasi, Alasan (truncate), Status, Aksi
- Filter: Semua Cabang, Semua Departemen, Status, Periode
- Counter badge di sidebar: jumlah pengajuan pending yang belum diproses
- Di halaman detail: tombol **Setujui** (hijau) dan **Tolak** (merah)
- Saat Tolak: modal popup muncul, wajib isi alasan penolakan

---

## Catatan Tambahan

- **Attachment** disimpan di `storage/app/private/leave-attachments/` (sama seperti foto absensi — private, diakses via controller)
- **Audit log** menggunakan `AuditLogService` yang sudah ada, module: `leave_request`
- **Overlap check**: saat karyawan mengajukan, sistem cek apakah sudah ada pengajuan di tanggal yang sama dengan status `pending` atau `approved` — jika ada, tolak dan tampilkan pesan error
- **Integrasi absensi**: saat status berubah ke `approved`, absensi di tanggal tersebut bisa otomatis dibuat dengan status `leave`/`permission` — *opsional, bisa dikerjakan di fase 2*

---

*Dokumen ini dibuat sebagai referensi implementasi. Eksekusi dilakukan saat fitur ini diprioritaskan.*
