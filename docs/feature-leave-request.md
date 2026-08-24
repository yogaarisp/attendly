# Feature Spec: Form Cuti & Izin

Dokumen ini berisi rancangan lengkap fitur pengajuan cuti dan izin karyawan.
Status: **BELUM DIEKSEKUSI** — siap diimplementasikan kapan saja.

---

## Daftar Isi

1. [Gambaran Fitur](#gambaran-fitur)
2. [Tipe Pengajuan](#tipe-pengajuan)
3. [Alur Sistem](#alur-sistem)
4. [Database](#database)
5. [File yang Perlu Dibuat](#file-yang-perlu-dibuat)
6. [File yang Perlu Dimodifikasi](#file-yang-perlu-dimodifikasi)
7. [Detail Setiap File](#detail-setiap-file)
8. [Urutan Eksekusi](#urutan-eksekusi)

---

## Gambaran Fitur

Karyawan dapat mengajukan **Cuti** atau **Izin** langsung dari portal karyawan. Admin menerima notifikasi dan dapat menyetujui atau menolak pengajuan dengan catatan. Status pengajuan ditampilkan di dashboard karyawan dan tercatat di audit log.

---

## Tipe Pengajuan

### Cuti (`leave`)
| Kode | Label |
|---|---|
| `annual` | Cuti Tahunan |
| `sick` | Cuti Sakit |
| `maternity` | Cuti Melahirkan |
| `paternity` | Cuti Ayah |
| `emergency` | Cuti Darurat / Duka |
| `unpaid` | Cuti Tanpa Upah |

### Izin (`permission`)
| Kode | Label |
|---|---|
| `late` | Izin Terlambat |
| `early_leave` | Izin Pulang Awal |
| `personal` | Izin Keperluan Pribadi |
| `medical` | Izin Berobat / Dokter |
| `wfh` | Izin Work From Home |

---

## Alur Sistem

```
[ Karyawan ]
     │
     ▼
Buka halaman "Pengajuan" → pilih tipe (Cuti / Izin)
     │
     ▼
Isi form:
  - Tipe cuti/izin
  - Tanggal mulai & selesai (untuk cuti)
  - Tanggal & jam (untuk izin harian)
  - Alasan / keterangan
  - Upload dokumen pendukung (opsional, misal: surat dokter)
     │
     ▼
Submit → status: PENDING
     │
     ▼
[ Admin ]
     │
     ▼
Lihat daftar pengajuan di Admin Panel → filter by status/tipe/cabang
     │
  ┌──┴──┐
  ▼     ▼
APPROVE  REJECT
  │       │
  │    + catatan alasan penolakan (wajib)
  │
  ▼
Status update → APPROVED / REJECTED
     │
     ▼
Karyawan lihat status di dashboard & riwayat pengajuan
     │
     ▼
Audit log tercatat
```

---

## Database

### Tabel Baru: `leave_requests`

```sql
CREATE TABLE leave_requests (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id     BIGINT UNSIGNED NOT NULL,
    branch_id       BIGINT UNSIGNED NOT NULL,

    -- Tipe: 'leave' (cuti) atau 'permission' (izin)
    request_type    ENUM('leave', 'permission') NOT NULL,

    -- Sub-tipe berdasarkan request_type di atas
    leave_type      VARCHAR(50) NOT NULL,
    -- leave: annual, sick, maternity, paternity, emergency, unpaid
    -- permission: late, early_leave, personal, medical, wfh

    -- Rentang tanggal (untuk cuti multi-hari)
    start_date      DATE NOT NULL,
    end_date        DATE NOT NULL,

    -- Untuk izin: jam mulai & selesai (nullable untuk cuti)
    start_time      TIME NULL,
    end_time        TIME NULL,

    -- Jumlah hari kerja yang terpengaruh (dihitung otomatis)
    total_days      DECIMAL(4,1) NOT NULL DEFAULT 1,

    -- Alasan dari karyawan
    reason          TEXT NOT NULL,

    -- Dokumen pendukung (path file, nullable)
    attachment      VARCHAR(255) NULL,

    -- Status pengajuan
    status          ENUM('pending', 'approved', 'rejected', 'cancelled') 
                    DEFAULT 'pending' NOT NULL,

    -- Siapa yang approve/reject dan kapan
    reviewed_by     BIGINT UNSIGNED NULL,  -- user_id admin
    reviewed_at     TIMESTAMP NULL,
    review_note     TEXT NULL,  -- catatan admin saat reject (wajib jika reject)

    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (branch_id)   REFERENCES branches(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
);
```

### Migration File
```
database/migrations/2026_08_23_000011_create_leave_requests_table.php
```

---

## File yang Perlu Dibuat

```
app/
├── Models/
│   └── LeaveRequest.php
│
├── Http/
│   └── Controllers/
│       ├── Employee/
│       │   └── LeaveRequestController.php   ← create, store, index, cancel
│       └── Admin/
│           └── LeaveRequestController.php   ← index, show, approve, reject
│
database/
└── migrations/
    └── 2026_08_23_000011_create_leave_requests_table.php

resources/views/
├── employee/
│   └── leave/
│       ├── index.blade.php      ← riwayat pengajuan karyawan
│       └── create.blade.php     ← form pengajuan baru
└── admin/
    └── leaves/
        ├── index.blade.php      ← semua pengajuan (filter status, cabang)
        └── show.blade.php       ← detail + tombol approve/reject
```

---

## File yang Perlu Dimodifikasi

| File | Perubahan |
|---|---|
| `routes/web.php` | Tambah route employee & admin untuk leave |
| `resources/views/layouts/employee.blade.php` | Tambah menu "Pengajuan" di bottom nav |
| `resources/views/employee/dashboard.blade.php` | Tambah widget status pengajuan terbaru |
| `resources/views/layouts/admin.blade.php` | Tambah menu "Pengajuan Cuti & Izin" di sidebar |
| `resources/views/admin/dashboard.blade.php` | Tambah counter pengajuan pending |
| `app/Models/Employee.php` | Tambah relasi `hasMany(LeaveRequest::class)` |

---

## Detail Setiap File

### 1. Migration

```php
Schema::create('leave_requests', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained()->restrictOnDelete();
    $table->foreignId('branch_id')->constrained()->restrictOnDelete();
    $table->enum('request_type', ['leave', 'permission']);
    $table->string('leave_type', 50);
    $table->date('start_date');
    $table->date('end_date');
    $table->time('start_time')->nullable();
    $table->time('end_time')->nullable();
    $table->decimal('total_days', 4, 1)->default(1);
    $table->text('reason');
    $table->string('attachment', 255)->nullable();
    $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])
          ->default('pending')
          ->index();
    $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('reviewed_at')->nullable();
    $table->text('review_note')->nullable();
    $table->timestamps();
});
```

---

### 2. Model: `LeaveRequest`

Relasi:
- `belongsTo(Employee::class)`
- `belongsTo(Branch::class)`
- `belongsTo(User::class, 'reviewed_by')` — reviewer

Accessor yang perlu dibuat:
- `getLeaveTypeLabelAttribute()` — label bahasa Indonesia
- `getStatusLabelAttribute()` — label status
- `getStatusColorAttribute()` — warna badge (emerald/amber/rose/slate)
- `getDurationTextAttribute()` — "3 hari" atau "08:00 - 10:00"

Scope:
- `scopePending($query)`
- `scopeApproved($query)`
- `scopeForEmployee($query, $employeeId)`

---

### 3. Employee LeaveRequestController

```
GET  /employee/leave              → index()   — riwayat pengajuan
GET  /employee/leave/create       → create()  — form baru
POST /employee/leave              → store()   — simpan pengajuan
POST /employee/leave/{id}/cancel  → cancel()  — batalkan pengajuan (hanya jika masih pending)
```

Validasi di `store()`:
- `request_type` — required, in:leave,permission
- `leave_type` — required
- `start_date` — required, date, after_or_equal:today
- `end_date` — required, date, after_or_equal:start_date
- `reason` — required, min:10
- `attachment` — nullable, file, mimes:pdf,jpg,jpeg,png, max:2048

---

### 4. Admin LeaveRequestController

```
GET  /admin/leaves                    → index()   — semua pengajuan
GET  /admin/leaves/{id}              → show()    — detail pengajuan
POST /admin/leaves/{id}/approve      → approve() — setujui
POST /admin/leaves/{id}/reject       → reject()  — tolak + wajib review_note
```

Filter di `index()`:
- `status` (pending/approved/rejected)
- `request_type` (leave/permission)
- `branch_id`
- `date_from` / `date_to`
- `search` (nama karyawan / NIK)

---

### 5. Routes yang perlu ditambah ke `routes/web.php`

```php
// Employee routes
Route::middleware(['auth', 'role:employee', 'active.employee'])
    ->prefix('employee')
    ->name('employee.')
    ->group(function () {
        // ... existing routes ...
        Route::get('leave', [EmployeeLeaveController::class, 'index'])->name('leave.index');
        Route::get('leave/create', [EmployeeLeaveController::class, 'create'])->name('leave.create');
        Route::post('leave', [EmployeeLeaveController::class, 'store'])->name('leave.store');
        Route::post('leave/{leaveRequest}/cancel', [EmployeeLeaveController::class, 'cancel'])->name('leave.cancel');
    });

// Admin routes
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // ... existing routes ...
        Route::get('leaves', [AdminLeaveController::class, 'index'])->name('leaves.index');
        Route::get('leaves/{leaveRequest}', [AdminLeaveController::class, 'show'])->name('leaves.show');
        Route::post('leaves/{leaveRequest}/approve', [AdminLeaveController::class, 'approve'])->name('leaves.approve');
        Route::post('leaves/{leaveRequest}/reject', [AdminLeaveController::class, 'reject'])->name('leaves.reject');
    });
```

---

### 6. Tampilan Employee: `create.blade.php`

Form field:
- **Tipe Pengajuan** — radio button: Cuti / Izin
- **Jenis** — dropdown dinamis (berubah sesuai tipe yang dipilih)
- **Tanggal Mulai** — date picker
- **Tanggal Selesai** — date picker (untuk cuti); disembunyikan untuk izin harian
- **Jam Mulai / Jam Selesai** — time picker (hanya muncul untuk izin)
- **Alasan** — textarea, min 10 karakter
- **Dokumen Pendukung** — file upload (PDF/JPG/PNG, max 2MB), opsional
- **Total estimasi** — dihitung otomatis via JS (berapa hari/jam)

---

### 7. Tampilan Admin: `index.blade.php`

- Badge counter **Pending** di atas tabel (merah bila > 0)
- Filter: Status, Tipe, Cabang, Tanggal, Search
- Kolom tabel: Karyawan, Tipe, Tanggal, Durasi, Diajukan, Status, Aksi
- Tombol **Setujui** dan **Tolak** langsung dari baris (quick action)
- Baris pending diberi highlight kuning tipis

---

### 8. Tampilan Admin: `show.blade.php`

- Info lengkap karyawan + pengajuan
- Preview dokumen pendukung (jika ada)
- Form approve: tombol hijau + konfirmasi
- Form reject: textarea wajib untuk alasan penolakan + tombol merah
- Riwayat pengajuan sebelumnya oleh karyawan yang sama

---

## Urutan Eksekusi

Kalau sudah siap diimplementasikan, kerjakan dengan urutan ini:

1. **Migration** — buat tabel `leave_requests`
2. **Model** `LeaveRequest` — relasi, accessor, scope
3. **Update** `Employee` model — tambah relasi
4. **Employee Controller** — index, create, store, cancel
5. **Admin Controller** — index, show, approve, reject
6. **Routes** — tambah ke `web.php`
7. **Views Employee** — `leave/create.blade.php`, `leave/index.blade.php`
8. **Views Admin** — `leaves/index.blade.php`, `leaves/show.blade.php`
9. **Update Layout** — tambah menu di sidebar admin & bottom nav employee
10. **Update Dashboard** — widget pending di admin, status terbaru di employee
11. **Audit Log** — catat setiap approve/reject
12. **Push ke GitHub**

---

*Dokumen ini dibuat sebagai referensi implementasi. Eksekusi dilakukan terpisah saat fitur dibutuhkan.*
