<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WorkingDay;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with demo data.
     */
    public function run(): void
    {
        // 1. System Settings
        SystemSetting::create([
            'key' => 'company_name',
            'value' => 'PT Attendly Digital Indonesia',
            'description' => 'Nama Perusahaan',
        ]);
        SystemSetting::create([
            'key' => 'app_version',
            'value' => '1.0.0-MVP',
            'description' => 'Versi Aplikasi',
        ]);

        // 2. Admin User
        $adminUser = User::create([
            'name' => 'Administrator Attendly',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 3. Departments
        $deptIT = Department::create(['code' => 'IT', 'name' => 'Information Technology', 'status' => 'active']);
        $deptHR = Department::create(['code' => 'HR', 'name' => 'Human Resources', 'status' => 'active']);
        $deptFinance = Department::create(['code' => 'FIN', 'name' => 'Finance & Accounting', 'status' => 'active']);
        $deptOps = Department::create(['code' => 'OPS', 'name' => 'Operations', 'status' => 'active']);

        // 4. Positions
        $posDev = Position::create(['department_id' => $deptIT->id, 'name' => 'Senior Laravel Developer', 'status' => 'active']);
        $posQA = Position::create(['department_id' => $deptIT->id, 'name' => 'QA Engineer', 'status' => 'active']);
        $posHR = Position::create(['department_id' => $deptHR->id, 'name' => 'HR Specialist', 'status' => 'active']);
        $posFin = Position::create(['department_id' => $deptFinance->id, 'name' => 'Finance Staff', 'status' => 'active']);
        $posOps = Position::create(['department_id' => $deptOps->id, 'name' => 'Operations Lead', 'status' => 'active']);

        // 5. Branches
        $branchJogja = Branch::create([
            'code' => 'JOG-01',
            'name' => 'Kantor Yogyakarta',
            'address' => 'Jl. Malioboro No. 45, Kota Yogyakarta, D.I. Yogyakarta 55271',
            'phone' => '0274-555123',
            'latitude' => -7.79560000,
            'longitude' => 110.36950000,
            'radius_meter' => 100,
            'timezone' => 'Asia/Jakarta',
            'status' => 'active',
        ]);

        $branchJakarta = Branch::create([
            'code' => 'JKT-01',
            'name' => 'Kantor Jakarta',
            'address' => 'Jl. Jend. Sudirman Kav. 52-53, SCBD, Jakarta Selatan 12190',
            'phone' => '021-555987',
            'latitude' => -6.22500000,
            'longitude' => 106.80900000,
            'radius_meter' => 150,
            'timezone' => 'Asia/Jakarta',
            'status' => 'active',
        ]);

        // 6. Attendance Settings per branch
        AttendanceSetting::create([
            'branch_id' => $branchJogja->id,
            'work_start_time' => '08:00:00',
            'work_end_time' => '17:00:00',
            'late_tolerance_minutes' => 15,
            'minimum_gps_accuracy' => 100,
            'attendance_enabled' => true,
        ]);

        AttendanceSetting::create([
            'branch_id' => $branchJakarta->id,
            'work_start_time' => '08:30:00',
            'work_end_time' => '17:30:00',
            'late_tolerance_minutes' => 15,
            'minimum_gps_accuracy' => 100,
            'attendance_enabled' => true,
        ]);

        // 7. Working Days (Global Default: Mon-Fri active, Sat-Sun off)
        for ($day = 0; $day <= 6; $day++) {
            WorkingDay::create([
                'branch_id' => null,
                'day_of_week' => $day,
                'is_working_day' => ($day >= 1 && $day <= 5), // 1=Mon..5=Fri
            ]);
        }

        // 8. Demo Employee User 1
        $empUser1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'employee@example.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
        ]);

        $employee1 = Employee::create([
            'user_id' => $empUser1->id,
            'employee_code' => 'EMP-2026-001',
            'full_name' => 'Budi Santoso',
            'email' => 'employee@example.com',
            'phone' => '081234567890',
            'gender' => 'male',
            'department_id' => $deptIT->id,
            'position_id' => $posDev->id,
            'branch_id' => $branchJogja->id,
            'join_date' => '2025-01-15',
            'status' => 'active',
        ]);

        // Demo Employee User 2 (HR staff)
        $empUser2 = User::create([
            'name' => 'Siti Rahmawati',
            'email' => 'siti@example.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
        ]);

        $employee2 = Employee::create([
            'user_id' => $empUser2->id,
            'employee_code' => 'EMP-2026-002',
            'full_name' => 'Siti Rahmawati',
            'email' => 'siti@example.com',
            'phone' => '081298765432',
            'gender' => 'female',
            'department_id' => $deptHR->id,
            'position_id' => $posHR->id,
            'branch_id' => $branchJogja->id,
            'join_date' => '2025-02-01',
            'status' => 'active',
        ]);

        // Demo Employee User 3 (Jakarta staff)
        $empUser3 = User::create([
            'name' => 'Andi Wijaya',
            'email' => 'andi@example.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
        ]);

        $employee3 = Employee::create([
            'user_id' => $empUser3->id,
            'employee_code' => 'EMP-2026-003',
            'full_name' => 'Andi Wijaya',
            'email' => 'andi@example.com',
            'phone' => '081311223344',
            'gender' => 'male',
            'department_id' => $deptOps->id,
            'position_id' => $posOps->id,
            'branch_id' => $branchJakarta->id,
            'join_date' => '2025-03-10',
            'status' => 'active',
        ]);

        // 9. Historical sample attendances for reports & charts
        $today = Carbon::today('Asia/Jakarta');
        for ($i = 5; $i >= 1; $i--) {
            $pastDate = $today->copy()->subDays($i);
            // Skip weekend in demo history
            if ($pastDate->isWeekend()) {
                continue;
            }

            Attendance::create([
                'employee_id' => $employee1->id,
                'branch_id' => $branchJogja->id,
                'attendance_date' => $pastDate->toDateString(),
                'check_in_at' => $pastDate->copy()->setHour(7)->setMinute(55),
                'check_out_at' => $pastDate->copy()->setHour(17)->setMinute(5),
                'check_in_latitude' => -7.795620,
                'check_in_longitude' => 110.369510,
                'check_in_accuracy' => 12.5,
                'check_in_distance' => 15.2,
                'check_out_latitude' => -7.795610,
                'check_out_longitude' => 110.369505,
                'check_out_accuracy' => 10.0,
                'check_out_distance' => 8.4,
                'check_in_status' => 'on_time',
                'check_out_status' => 'normal',
                'overall_status' => 'present',
            ]);

            Attendance::create([
                'employee_id' => $employee2->id,
                'branch_id' => $branchJogja->id,
                'attendance_date' => $pastDate->toDateString(),
                'check_in_at' => $pastDate->copy()->setHour(8)->setMinute(20), // Late
                'check_out_at' => $pastDate->copy()->setHour(17)->setMinute(1),
                'check_in_latitude' => -7.795630,
                'check_in_longitude' => 110.369520,
                'check_in_accuracy' => 15.0,
                'check_in_distance' => 22.0,
                'check_out_latitude' => -7.795615,
                'check_out_longitude' => 110.369510,
                'check_out_accuracy' => 14.0,
                'check_out_distance' => 11.5,
                'check_in_status' => 'late',
                'check_out_status' => 'normal',
                'overall_status' => 'late',
            ]);
        }
    }
}
