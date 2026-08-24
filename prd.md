# PRD FINAL & MASTER DEVELOPMENT PROMPT

# ATTENDLY — SMART EMPLOYEE ATTENDANCE SYSTEM

---

# 1. ROLE AI DEVELOPER

Anda bertindak sebagai:

* Senior Software Architect
* Senior Laravel Developer
* Senior UI/UX Engineer
* Database Designer
* Security Engineer
* QA Engineer
* DevOps Engineer

Tugas Anda adalah membangun aplikasi absensi karyawan production-ready berdasarkan PRD ini.

Jangan hanya membuat UI mockup.

Aplikasi harus memiliki:

* Frontend
* Backend
* Database
* Authentication
* Authorization
* Attendance engine
* Camera/selfie capture
* GPS verification
* Employee management
* Branch/location management
* Work schedule management
* Attendance history
* Reporting
* Excel export
* PDF export
* Audit log
* Security
* Responsive PWA

Jangan mengubah requirement utama tanpa alasan teknis yang kuat.

Jika terdapat requirement yang belum jelas, gunakan pendekatan paling aman, sederhana, scalable, dan mudah dipelihara.

Dokumentasikan keputusan teknis yang diambil.

---

# 2. NAMA APLIKASI

Nama:

**Attendly**

Tagline:

**Smart Employee Attendance System**

---

# 3. TUJUAN SISTEM

Attendly adalah sistem absensi karyawan berbasis web/PWA.

Tujuan utama:

Memudahkan karyawan melakukan absensi menggunakan:

1. Kamera/selfie
2. GPS/location
3. Server time
4. Aturan jam kerja

Sistem menyediakan dashboard admin untuk:

* Mengelola karyawan
* Mengelola cabang
* Mengatur lokasi absensi
* Mengatur jam kerja
* Melihat absensi
* Melihat bukti selfie
* Melihat lokasi absensi
* Melihat laporan
* Export Excel
* Export PDF
* Melihat audit log

Sistem harus sederhana untuk digunakan oleh perusahaan yang memiliki jam kerja standar.

JANGAN membuat sistem shift pada versi pertama.

---

# 4. KONSEP ABSENSI

Attendly menggunakan konsep:

**Work Schedule / Attendance Settings**

bukan Shift Management.

Contoh aturan:

Jam masuk:
08:00

Toleransi:
15 menit

Jam pulang:
17:00

Contoh:

07:45 → Tepat waktu

07:59 → Tepat waktu

08:10 → Tepat waktu

08:15 → Tepat waktu

08:16 → Terlambat

09:00 → Terlambat

---

# 5. SCOPE MVP

Fitur wajib:

## Employee

* Login
* Dashboard
* Check In
* Check Out
* Camera selfie
* GPS
* Attendance history
* Profile
* Logout

## Admin

* Dashboard
* Employee management
* Branch management
* Attendance settings
* Attendance monitoring
* Attendance detail
* Reports
* Excel export
* PDF export
* Audit logs
* System settings

---

# 6. OUT OF SCOPE MVP

Jangan implementasikan:

* Shift
* Shift assignment
* Payroll
* Leave management
* Overtime management
* Face recognition
* Liveness detection
* Fingerprint integration
* WhatsApp integration
* Native Android application
* Native iOS application

Namun arsitektur harus memungkinkan fitur tersebut ditambahkan di masa depan.

---

# 7. STACK TEKNOLOGI

## Backend

Gunakan:

* PHP 8.3+
* Laravel 12
* Laravel Blade
* Eloquent ORM
* Laravel Validation
* Laravel Policies/Gates
* Laravel Storage
* Laravel Scheduler jika diperlukan

## Frontend

Gunakan:

* Blade
* Tailwind CSS
* Alpine.js atau Vanilla JavaScript
* Browser MediaDevices API
* Browser Geolocation API
* PWA

Jangan menggunakan React/Next.js kecuali terdapat alasan teknis yang kuat.

Tujuan:

* sederhana
* cepat
* mudah dimaintain
* mudah dideploy
* cocok untuk VPS/aaPanel

## Database

Gunakan:

* MySQL
  atau
* MariaDB

Gunakan:

* Foreign key
* Index
* Unique constraint
* Proper normalization
* Soft delete jika diperlukan

## Export

Gunakan:

* Laravel Excel / PhpSpreadsheet
* PDF library yang kompatibel dengan Laravel

---

# 8. ARSITEKTUR APLIKASI

Gunakan struktur Laravel yang rapi.

Contoh:

app/
├── Models/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Middleware/
├── Services/
├── Policies/
├── Exports/
└── Jobs/

resources/
├── views/
├── css/
└── js/

routes/
├── web.php
└── api.php

database/
├── migrations/
├── seeders/
└── factories/

Business logic utama attendance harus berada di:

AttendanceService

Controller tidak boleh berisi seluruh logic attendance.

---

# 9. USER ROLE

Minimal dua role:

## ADMIN

Admin dapat:

* Dashboard
* Employee management
* Branch management
* Attendance settings
* Attendance monitoring
* Attendance detail
* Reports
* Export Excel
* Export PDF
* Audit logs
* System settings

## EMPLOYEE

Employee dapat:

* Login
* Dashboard
* Check In
* Check Out
* Camera
* GPS
* Attendance history
* Profile
* Logout

Employee tidak boleh mengakses data employee lain.

---

# 10. AUTHENTICATION

Gunakan Laravel authentication.

Login:

* Email/username
* Password

Security:

* Password menggunakan Laravel Hash
* CSRF protection
* Session regeneration
* Login rate limiting
* Secure logout
* Session invalidation

Gunakan middleware:

auth

dan role authorization.

---

# 11. EMPLOYEE

Field:

* id
* employee_code
* user_id
* full_name
* email
* phone
* gender
* position
* department
* branch_id
* photo
* join_date
* status
* created_at
* updated_at
* deleted_at

Status:

* active
* inactive

Employee inactive tidak dapat melakukan absensi.

---

# 12. DEPARTMENT

Gunakan tabel departments.

Field:

* id
* name
* code
* status
* created_at
* updated_at

Contoh:

IT

Finance

HR

Marketing

Operational

---

# 13. POSITION

Gunakan tabel positions.

Field:

* id
* name
* department_id
* status
* created_at
* updated_at

Contoh:

Software Developer

IT Support

Finance Staff

HR Staff

Manager

---

# 14. BRANCH

Tabel:

branches

Field:

* id
* code
* name
* address
* phone
* latitude
* longitude
* radius_meter
* timezone
* status
* created_at
* updated_at

Contoh:

Kantor Yogyakarta

Latitude:

-7.xxxxx

Longitude:

110.xxxxx

Radius:

100 meter

Timezone:

Asia/Jakarta

---

# 15. WORK SCHEDULE / ATTENDANCE SETTINGS

Tidak ada shift.

Buat konfigurasi jam kerja.

Minimal:

* branch_id
* work_start_time
* work_end_time
* late_tolerance_minutes
* minimum_gps_accuracy
* attendance_enabled
* created_at
* updated_at

Contoh:

Jam masuk:

08:00

Jam pulang:

17:00

Tolerance:

15 menit

Minimum GPS accuracy:

100 meter

---

# 16. WORKING DAYS

Sistem harus mendukung hari kerja.

Contoh default:

Senin
Selasa
Rabu
Kamis
Jumat

Weekend:

Sabtu
Minggu

Admin dapat menentukan hari kerja.

Jika hari tersebut bukan hari kerja:

Employee tidak dapat melakukan absensi normal.

Tampilkan:

"Hari ini bukan hari kerja."

Struktur dapat berupa tabel:

working_days

atau JSON/configuration yang tepat.

Pilih pendekatan yang paling maintainable.

---

# 17. LOCATION VALIDATION

Ketika employee melakukan absensi:

Browser meminta GPS permission.

Frontend mendapatkan:

* latitude
* longitude
* accuracy

Frontend mengirimkan data ke server.

Server menghitung ulang jarak antara:

Employee Location

dan

Branch Location.

Gunakan Haversine formula atau library geografis yang tepat.

Jika:

distance <= radius_meter

maka:

LOCATION VERIFIED

Jika:

distance > radius_meter

maka:

OUTSIDE OFFICE AREA

PENTING:

Jangan percaya distance dari frontend.

Frontend hanya mengirim:

latitude

longitude

accuracy

Backend menghitung distance sendiri.

---

# 18. GPS ACCURACY

Simpan:

* latitude
* longitude
* accuracy
* distance_from_office

Jika GPS accuracy terlalu buruk:

accuracy > configured threshold

maka absensi dapat ditolak.

Pesan:

"Lokasi GPS kurang akurat. Silakan aktifkan GPS atau pindah ke area dengan sinyal GPS yang lebih baik."

Threshold configurable.

---

# 19. CAMERA ATTENDANCE

Camera adalah bagian utama sistem.

Gunakan:

navigator.mediaDevices.getUserMedia()

Default:

facingMode: user

Flow:

Check In

↓

Camera permission

↓

Camera preview

↓

Selfie

↓

Photo preview

↓

Confirm

↓

Upload

↓

Attendance validation

↓

Create attendance

---

# 20. CAMERA STATES

Implementasikan:

1. Camera Loading
2. Camera Permission Required
3. Camera Ready
4. Camera Permission Denied
5. Photo Captured
6. Retake Photo
7. Uploading
8. Upload Failed
9. Success

Jika permission ditolak:

"Camera permission diperlukan untuk melakukan absensi."

Berikan instruksi mengaktifkan permission.

---

# 21. PHOTO REQUIREMENTS

Foto:

* JPEG/WebP
* Max size configurable
* MIME validation
* Extension validation
* File size validation
* Compression/resizing
* Random filename
* UUID filename

Jangan menyimpan filename berdasarkan username.

Contoh:

attendance/2026/08/20/

550e8400-e29b-41d4-a716-446655440000.jpg

---

# 22. CHECK IN

Employee dapat Check In jika:

* authenticated
* account active
* hari kerja
* belum Check In
* camera photo valid
* GPS valid
* location berada dalam radius

Flow:

Dashboard

↓

ABSEN MASUK

↓

Camera

↓

Capture Selfie

↓

Confirm

↓

GPS

↓

Location validation

↓

Server validation

↓

Create attendance

↓

Success

---

# 23. CHECK OUT

Employee dapat Check Out jika:

* authenticated
* sudah Check In
* belum Check Out
* camera photo valid
* GPS valid
* location valid

Flow:

Dashboard

↓

ABSEN PULANG

↓

Camera

↓

Selfie

↓

GPS

↓

Validation

↓

Update attendance

↓

Success

---

# 24. SERVER TIME

Server adalah sumber waktu utama.

Jangan percaya:

* browser timestamp
* mobile timestamp
* client submitted check_in_at

Gunakan timezone branch.

Contoh:

Asia/Jakarta

Timestamp dibuat oleh backend.

---

# 25. ATTENDANCE STATUS

Minimal:

* present
* late
* incomplete
* outside_area
* rejected

Untuk Check In:

Jika:

check_in_time <= work_start_time + tolerance

maka:

ON TIME

Jika melewati:

LATE

---

# 26. ATTENDANCE DATABASE

Tabel:

attendances

Minimal:

* id
* employee_id
* branch_id
* attendance_date
* check_in_at
* check_out_at
* check_in_photo
* check_out_photo
* check_in_latitude
* check_in_longitude
* check_in_accuracy
* check_in_distance
* check_out_latitude
* check_out_longitude
* check_out_accuracy
* check_out_distance
* check_in_status
* check_out_status
* overall_status
* notes
* created_at
* updated_at

Tidak ada:

shift_id

---

# 27. ATTENDANCE UNIQUE RULE

Default business rule:

Satu employee hanya memiliki satu attendance record per tanggal.

Contoh:

Employee:

EMP001

Tanggal:

2026-08-20

Tidak boleh memiliki dua attendance record.

Gunakan:

unique(employee_id, attendance_date)

atau mekanisme database yang sesuai.

---

# 28. ATTENDANCE FLOW

Full flow:

Employee Login

↓

Dashboard

↓

Check In

↓

Camera Permission

↓

Camera Preview

↓

Capture Selfie

↓

Confirm Photo

↓

GPS Permission

↓

Get Location

↓

Backend Validation

↓

Check Working Day

↓

Check Employee Status

↓

Check Duplicate

↓

Check GPS Accuracy

↓

Calculate Distance

↓

Check Radius

↓

Calculate Attendance Status

↓

Store Photo

↓

Create Attendance

↓

Create Audit Log

↓

Success

---

# 29. DASHBOARD EMPLOYEE

Header:

"Selamat pagi, {name}"

Tanggal.

Realtime clock.

Main card:

ABSENSI HARI INI

Jika belum absen:

"Belum Absen Masuk"

Button:

📷 ABSEN MASUK

Jika sudah masuk:

Check In:

08:02 WIB

Button:

📷 ABSEN PULANG

Jika sudah pulang:

Check In:

08:02

Check Out:

17:01

Status:

Absensi selesai.

---

# 30. TODAY ACTIVITY

Tampilkan timeline:

08:02

Check In

Kantor Yogyakarta

17:01

Check Out

Kantor Yogyakarta

Jika belum:

Empty state.

---

# 31. ATTENDANCE HISTORY

Employee dapat melihat:

* tanggal
* check in
* check out
* status
* branch

Filter:

* bulan
* tahun
* status

Desktop:

Table

Mobile:

Cards

---

# 32. PROFILE

Tampilkan:

* Foto
* Nama
* Employee ID
* Email
* Phone
* Department
* Position
* Branch
* Join Date

Employee tidak dapat mengubah data sensitif tanpa mekanisme admin.

---

# 33. ADMIN DASHBOARD

Dashboard:

Total Employees

Hadir

Terlambat

Tidak Hadir

Belum Check Out

Contoh:

120 Employees

108 Hadir

8 Terlambat

4 Tidak Hadir

Tambahkan:

Attendance Overview

dan

Today's Attendance.

---

# 34. TODAY ATTENDANCE

Admin dapat melihat:

Nama

Employee ID

Branch

Check In

Check Out

Status

Location

Action

Gunakan pagination.

Search.

Filter.

---

# 35. EMPLOYEE MANAGEMENT

CRUD:

Create

Read

Update

Deactivate

Employee fields:

* Employee ID
* Name
* Email
* Phone
* Department
* Position
* Branch
* Status

Search.

Filter.

Pagination.

---

# 36. BRANCH MANAGEMENT

CRUD:

* Create
* Read
* Update
* Deactivate

Field:

* Code
* Name
* Address
* Phone
* Latitude
* Longitude
* Radius
* Timezone
* Status

Admin dapat menentukan lokasi kantor.

---

# 37. ATTENDANCE SETTINGS

Admin dapat mengatur:

Jam Masuk

Jam Pulang

Tolerance keterlambatan

GPS Accuracy Threshold

Radius Absensi

Hari Kerja

Status Attendance

Contoh:

Jam masuk:

08:00

Jam pulang:

17:00

Tolerance:

15 menit

Radius:

100 meter

---

# 38. ATTENDANCE DETAIL

Admin dapat membuka detail attendance.

Tampilkan:

Employee

Employee ID

Tanggal

Branch

Check In

Check Out

Status

GPS Check In

GPS Check Out

Distance

GPS Accuracy

Foto Check In

Foto Check Out

Notes

Audit information.

Jika memungkinkan tampilkan map.

---

# 39. REPORT

Admin dapat filter:

* Date From
* Date To
* Employee
* Branch
* Department
* Position
* Status

Table:

No

Tanggal

Employee ID

Nama

Department

Position

Branch

Check In

Check Out

Status

Distance

Location Status

---

# 40. EXPORT EXCEL

Admin dapat:

EXPORT EXCEL

Export mengikuti filter aktif.

Contoh:

Date:

01-08-2026

to:

31-08-2026

Branch:

Yogyakarta

Department:

IT

Maka Excel hanya berisi data sesuai filter.

Columns:

No

Tanggal

Employee ID

Nama

Department

Position

Branch

Check In

Check Out

Status

Check In Latitude

Check In Longitude

Check In Accuracy

Check In Distance

Check Out Latitude

Check Out Longitude

Check Out Accuracy

Check Out Distance

Nama file:

attendance_report_YYYYMMDD_HHMMSS.xlsx

Jangan memasukkan binary photo ke Excel.

Jika diperlukan, masukkan photo reference/path.

---

# 41. EXPORT PDF

Admin dapat export PDF.

PDF:

* Application/company name
* Period
* Filter
* Generated date
* Generated by
* Attendance table

Filename:

attendance_report_YYYYMMDD.pdf

---

# 42. AUDIT LOG

Catat:

* Login
* Logout
* Employee created
* Employee updated
* Employee deactivated
* Branch created
* Branch updated
* Settings updated
* Attendance created
* Attendance checkout
* Attendance rejected
* Export report

Field:

* user_id
* action
* module
* record_id
* IP
* user_agent
* metadata
* created_at

Jangan simpan password.

---

# 43. SECURITY

Implementasikan:

* CSRF
* XSS protection
* SQL injection protection
* Eloquent
* Form Request validation
* Policies
* Authorization
* Rate limiting
* Secure file upload
* MIME validation
* File size validation
* UUID filenames
* Private attendance photo storage
* Authorized photo access
* Session security

Employee tidak boleh mengakses:

* employee lain
* attendance employee lain
* admin dashboard
* admin reports
* audit logs

---

# 44. ANTI MANIPULATION

Backend harus menjadi source of truth.

Client hanya mengirim:

* photo
* latitude
* longitude
* accuracy

Client tidak boleh menentukan:

* attendance status
* check_in_at
* check_out_at
* distance
* valid/invalid location

Server menentukan semuanya.

---

# 45. TRANSACTION

Check In menggunakan database transaction.

Flow:

BEGIN

Validate employee

Validate working day

Validate duplicate

Validate camera/photo

Validate GPS

Calculate distance

Validate radius

Calculate status

Store photo

Create attendance

Create audit log

COMMIT

Jika gagal:

ROLLBACK

---

# 46. FILE SECURITY

Foto attendance harus:

* UUID
* MIME validated
* Size validated
* Stored securely
* Tidak executable
* Tidak accessible secara public tanpa authorization

Gunakan controller authorization untuk menampilkan foto.

Contoh:

GET /attendance/{attendance}/photo/{type}

Controller wajib memastikan user memiliki hak akses.

---

# 47. PERFORMANCE

Gunakan:

* Pagination
* Eager loading
* Database indexing
* Aggregate query
* Cache untuk settings
* Queue jika diperlukan

Dashboard tidak boleh mengambil seluruh tabel attendance.

---

# 48. PWA

Aplikasi harus PWA-ready.

Installable di mobile.

Namun:

ABSENSI OFFLINE TIDAK BOLEH DIIJINKAN.

Jika offline:

"Anda sedang offline. Absensi membutuhkan koneksi internet."

Alasan:

* Server time
* GPS validation
* Security
* Prevent manipulation

---

# 49. RESPONSIVE UI

Employee:

Mobile-first.

Mobile navigation:

Home

History

Profile

CTA utama:

ABSEN

Admin:

Desktop sidebar.

Mobile responsive navigation.

---

# 50. CAMERA UX

Camera interface harus premium.

Header:

"Verifikasi Kehadiran"

Subtitle:

"Pastikan wajah Anda terlihat jelas."

Camera:

Large preview.

Overlay:

Face positioning guide.

Bottom:

Capture button.

Setelah capture:

Preview.

Buttons:

Ambil Ulang

Konfirmasi Absensi

---

# 51. LOCATION UX

Setelah foto:

"Memeriksa lokasi..."

Jika valid:

🟢

"Lokasi terverifikasi"

Distance:

42 meter

Allowed:

100 meter

Jika invalid:

🔴

"Anda berada di luar area kantor."

Jangan menampilkan koordinat mentah kepada employee kecuali diperlukan.

---

# 52. SUCCESS UX

Tampilkan:

✓

"Absensi Berhasil"

Check In:

08:02 WIB

Location:

Kantor Yogyakarta

Status:

Tepat Waktu

Thumbnail selfie.

Button:

Kembali ke Dashboard

---

# 53. ERROR STATES

Handle:

Camera denied

GPS denied

GPS inaccurate

Outside office

Already checked in

Already checked out

Not working day

Inactive employee

Network error

Server error

Invalid photo

Upload failed

Session expired

Pesan harus human-friendly.

Jangan menampilkan stack trace.

---

# 54. DATABASE TABLES

Minimal:

users

employees

departments

positions

branches

attendance_settings

working_days

attendances

audit_logs

system_settings

TIDAK ADA:

shifts

employee_shifts

---

# 55. DATABASE RELATIONSHIPS

Relasi utama:

User

1 : 1

Employee

Employee

N : 1

Branch

Employee

N : 1

Department

Employee

N : 1

Position

Employee

1 : N

Attendance

Branch

1 : N

Attendance

Branch

1 : 1

Attendance Settings

---

# 56. DEMO SEEDER

Buat:

Admin demo

Employee demo

Department demo

Position demo

Branch demo

Attendance settings demo

Working days demo

Attendance demo

Development credentials:

Admin:

[admin@example.com](mailto:admin@example.com)

password:

password

Employee:

[employee@example.com](mailto:employee@example.com)

password:

password

WAJIB memberikan warning bahwa credentials hanya untuk development.

---

# 57. ROUTES

Minimal:

POST /login

POST /logout

GET /dashboard

GET /attendance/history

POST /attendance/check-in

POST /attendance/check-out

GET /profile

GET /admin/dashboard

GET /admin/employees

POST /admin/employees

PUT /admin/employees/{id}

DELETE /admin/employees/{id}

GET /admin/branches

POST /admin/branches

PUT /admin/branches/{id}

GET /admin/settings/attendance

PUT /admin/settings/attendance

GET /admin/attendance

GET /admin/attendance/{id}

GET /admin/reports/attendance

GET /admin/reports/attendance/export/excel

GET /admin/reports/attendance/export/pdf

GET /admin/audit-logs

Sesuaikan route dengan Laravel conventions.

---

# 58. CHECK-IN PAYLOAD

Minimal:

latitude

longitude

accuracy

photo

Jangan menerima:

attendance_date sebagai sumber utama

check_in_at

status

distance

Server harus menentukan semuanya.

---

# 59. TESTING

Buat automated tests.

Minimal:

AuthenticationTest

EmployeeTest

BranchTest

AttendanceSettingsTest

AttendanceCheckInTest

AttendanceCheckOutTest

LocationValidationTest

DuplicateAttendanceTest

AttendanceExportTest

AuthorizationTest

FileUploadTest

---

# 60. TEST CASES

Test:

1. Employee dapat login.
2. Admin dapat login.
3. Employee tidak dapat membuka admin.
4. Employee dapat Check In.
5. Employee tidak dapat Check In dua kali.
6. Employee tidak dapat Check Out sebelum Check In.
7. GPS valid diterima.
8. GPS di luar radius ditolak.
9. GPS accuracy buruk ditolak.
10. Hari non-kerja ditolak.
11. Employee inactive ditolak.
12. Late attendance terdeteksi.
13. Server time digunakan.
14. Photo upload divalidasi.
15. Employee tidak dapat melihat attendance employee lain.
16. Admin dapat melihat attendance.
17. Excel mengikuti filter.
18. PDF mengikuti filter.
19. Unauthorized request ditolak.
20. Audit log dibuat.

---

# 61. ACCEPTANCE CRITERIA

## Login

Given:

User memiliki credentials valid.

When:

User login.

Then:

User masuk dashboard sesuai role.

---

## Check In

Given:

Employee aktif.

Hari kerja.

Belum Check In.

GPS valid.

Photo valid.

When:

Employee melakukan Check In.

Then:

Attendance dibuat.

---

## Outside Radius

Given:

Employee berada di luar radius kantor.

When:

Employee Check In.

Then:

Attendance ditolak.

---

## Duplicate

Given:

Employee sudah Check In.

When:

Employee Check In lagi.

Then:

Request ditolak.

---

## Check Out

Given:

Employee sudah Check In.

Belum Check Out.

When:

Employee Check Out.

Then:

Attendance diperbarui.

---

## Excel

Given:

Admin memilih filter.

When:

Export Excel.

Then:

Excel hanya berisi data sesuai filter.

---

# 62. FUTURE FEATURES

Arsitektur harus memungkinkan:

Face Recognition

Liveness Detection

Leave Management

Permission

Sick Leave

Overtime

Payroll

WhatsApp notification

Email notification

Push notification

Native mobile app

Fingerprint integration

AI anomaly detection

Namun jangan implementasikan pada MVP.

---

# 63. DEVELOPMENT PHASES

## PHASE 1 — FOUNDATION

* Laravel setup
* Database
* Authentication
* Role
* Base layout
* Initial migration
* Seeder

---

## PHASE 2 — EMPLOYEE

* Employee CRUD
* Department
* Position
* Profile
* Employee dashboard

---

## PHASE 3 — BRANCH

* Branch CRUD
* Location
* Radius
* Timezone

---

## PHASE 4 — ATTENDANCE SETTINGS

* Work start
* Work end
* Tolerance
* GPS accuracy
* Working days

---

## PHASE 5 — CAMERA & GPS

* Camera
* Selfie
* GPS
* Permission handling
* Location validation

---

## PHASE 6 — ATTENDANCE ENGINE

* Check In
* Check Out
* Server time
* Status
* Duplicate prevention
* Transaction
* Audit log

---

## PHASE 7 — HISTORY & ADMIN

* Employee history
* Admin attendance
* Attendance detail
* Dashboard statistics

---

## PHASE 8 — REPORTING

* Filter
* Excel
* PDF

---

## PHASE 9 — SECURITY & TESTING

* Authorization
* File security
* Rate limiting
* Feature tests
* Edge cases

---

## PHASE 10 — PRODUCTION

* Environment
* Storage
* Queue
* Scheduler
* Backup
* SSL
* Deployment documentation

---

# 64. DEVELOPMENT RULES FOR AI

JANGAN membuat seluruh aplikasi sekaligus.

Kerjakan secara bertahap.

Setiap phase:

1. Jelaskan apa yang akan dibuat.
2. Implementasikan.
3. Jalankan migration.
4. Jalankan tests.
5. Perbaiki error.
6. Verifikasi functionality.
7. Jelaskan file yang berubah.
8. Jelaskan database yang berubah.
9. Jelaskan route yang berubah.
10. Tunggu approval untuk phase berikutnya jika diperlukan.

Jangan merusak functionality yang sudah berjalan.

Sebelum membuat file baru:

Periksa apakah file/function/service/component tersebut sudah ada.

Hindari duplicate code.

Gunakan Laravel conventions.

Gunakan:

* Form Request
* Service
* Policy
* Eloquent
* Migration
* Seeder
* Feature Test

---

# 65. ENVIRONMENT

Buat:

.env.example

Contoh:

APP_NAME=Attendly

APP_ENV=local

APP_KEY=

APP_DEBUG=true

APP_URL=http://localhost

DB_CONNECTION=mysql

DB_HOST=127.0.0.1

DB_PORT=3306

DB_DATABASE=attendly

DB_USERNAME=root

DB_PASSWORD=

---

# 66. DEPLOYMENT

Target production:

Linux VPS

Nginx

PHP-FPM

MySQL/MariaDB

SSL

Cloudflare optional.

Buat dokumentasi:

* Installation
* Migration
* Seeder
* Storage
* Permission
* Queue
* Scheduler
* Environment
* Backup
* SSL
* Production deployment

---

# 67. FINAL PRODUCT FLOW

EMPLOYEE:

Login

↓

Dashboard

↓

ABSEN MASUK

↓

Camera

↓

Selfie

↓

GPS

↓

Location Validation

↓

Server Validation

↓

Attendance Created

↓

Success

↓

Dashboard

↓

ABSEN PULANG

↓

Camera

↓

Selfie

↓

GPS

↓

Validation

↓

Checkout

↓

Success

---

ADMIN:

Login

↓

Dashboard

↓

Today's Attendance

↓

Attendance Detail

↓

Employee Management

↓

Branch Management

↓

Attendance Settings

↓

Reports

↓

Filter

↓

Export Excel/PDF

↓

Audit Logs

---

# 68. DEFINITION OF DONE

Project dianggap selesai jika:

* Authentication bekerja
* Role authorization bekerja
* Employee CRUD bekerja
* Department bekerja
* Position bekerja
* Branch CRUD bekerja
* Attendance settings bekerja
* Working days bekerja
* Camera bekerja pada browser yang mendukung
* GPS bekerja
* Backend GPS validation bekerja
* Check In bekerja
* Check Out bekerja
* Duplicate attendance dicegah
* Late status bekerja
* Attendance history bekerja
* Admin dashboard bekerja
* Attendance detail bekerja
* Excel export bekerja
* PDF export bekerja
* Audit log bekerja
* Responsive UI bekerja
* PWA configuration bekerja
* Automated tests pass
* Deployment documentation tersedia

Jangan menyatakan fitur selesai jika masih berupa mockup atau dummy.

---

# 69. FIRST TASK — JANGAN CODING DULU

Setelah membaca PRD ini:

JANGAN langsung membuat seluruh aplikasi.

Pertama lakukan analisis.

Tugas pertama:

### 1. DATABASE DESIGN

Buat rancangan:

* Semua tabel
* Semua field
* Data type
* Primary key
* Foreign key
* Index
* Unique constraint
* Relationship

### 2. ERD

Tampilkan hubungan:

users

employees

departments

positions

branches

attendance_settings

working_days

attendances

audit_logs

system_settings

### 3. ARCHITECTURE

Jelaskan:

* Laravel architecture
* Controllers
* Services
* Models
* Requests
* Policies
* Exports
* Jobs
* Middleware

### 4. ROUTE STRUCTURE

Buat daftar route dan authorization masing-masing.

### 5. ATTENDANCE ENGINE

Jelaskan secara detail bagaimana:

Check In

dan

Check Out

akan bekerja.

### 6. GPS VALIDATION

Jelaskan:

* bagaimana koordinat diterima
* bagaimana distance dihitung
* bagaimana radius divalidasi
* bagaimana accuracy diproses

### 7. CAMERA

Jelaskan:

* camera permission
* capture
* preview
* upload
* validation
* storage

### 8. SECURITY REVIEW

Identifikasi potensi:

* GPS spoofing
* duplicate attendance
* unauthorized access
* file upload attack
* session attack
* client-side manipulation

dan jelaskan mitigasinya.

### 9. IMPLEMENTATION PLAN

Buat implementation plan:

Phase 1

Phase 2

Phase 3

dan seterusnya.

### 10. AMBIGUITIES

Identifikasi requirement yang masih ambigu dan berikan rekomendasi.

---

# 70. IMPORTANT

JANGAN membuat migration.

JANGAN membuat controller.

JANGAN membuat model.

JANGAN membuat UI.

JANGAN membuat kode production.

JANGAN membuat seluruh project.

Pada FIRST TASK hanya berikan:

* Database proposal
* ERD
* Architecture proposal
* Route proposal
* Attendance engine design
* Camera/GPS design
* Security review
* Implementation plan
* Potential issues

Setelah analisis selesai:

**WAIT FOR USER APPROVAL.**

Jangan melanjutkan coding sebelum user memberikan approval.

---

# 71. FINAL DEVELOPMENT PRINCIPLE

Prioritaskan:

**Simple > Complex**

**Secure > Fast**

**Maintainable > Over-engineered**

**Server validation > Client validation**

**Production-ready > Demo-only**

Jangan menambahkan fitur yang tidak diperlukan hanya untuk membuat aplikasi terlihat kompleks.

Fokus MVP:

**Camera + GPS + Time + Attendance + Dashboard + Report + Excel.**
