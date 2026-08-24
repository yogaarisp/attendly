<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use App\Models\WorkingDay;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $employeeUser;
    protected Employee $employee;
    protected Branch $branch;
    protected AttendanceSetting $setting;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Admin User
        $this->adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Department & Position
        $dept = Department::create(['code' => 'IT', 'name' => 'Information Tech', 'status' => 'active']);
        $pos = Position::create(['department_id' => $dept->id, 'name' => 'Dev', 'status' => 'active']);

        // 3. Branch (-7.7956, 110.3695, 100m radius)
        $this->branch = Branch::create([
            'code' => 'JOG-01',
            'name' => 'Kantor Yogyakarta',
            'address' => 'Jl. Malioboro',
            'latitude' => -7.79560000,
            'longitude' => 110.36950000,
            'radius_meter' => 100,
            'timezone' => 'Asia/Jakarta',
            'status' => 'active',
        ]);

        // 4. Setting (08:00 start, 15 min tolerance, 100m max accuracy)
        $this->setting = AttendanceSetting::create([
            'branch_id' => $this->branch->id,
            'work_start_time' => '08:00:00',
            'work_end_time' => '17:00:00',
            'late_tolerance_minutes' => 15,
            'minimum_gps_accuracy' => 100,
            'attendance_enabled' => true,
        ]);

        // 5. Working Days (All days active for test baseline)
        for ($day = 0; $day <= 6; $day++) {
            WorkingDay::create([
                'branch_id' => null,
                'day_of_week' => $day,
                'is_working_day' => true,
            ]);
        }

        // 6. Employee User & Profile
        $this->employeeUser = User::create([
            'name' => 'Employee Test',
            'email' => 'employee@test.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
        ]);

        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'employee_code' => 'EMP-TEST-01',
            'full_name' => 'Employee Test',
            'email' => 'employee@test.com',
            'gender' => 'male',
            'department_id' => $dept->id,
            'position_id' => $pos->id,
            'branch_id' => $this->branch->id,
            'join_date' => '2025-01-01',
            'status' => 'active',
        ]);
    }

    /** Helper base64 photo */
    protected function getDummyBase64Photo(): string
    {
        // 1x1 pixel transparent gif/jpg data uri
        return 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=';
    }

    /** 1. Employee dapat login */
    public function test_employee_can_login()
    {
        $response = $this->post('/login', [
            'email' => 'employee@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->employeeUser);
    }

    /** 2. Admin dapat login */
    public function test_admin_can_login()
    {
        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($this->adminUser);
    }

    /** 3. Employee tidak dapat membuka admin dashboard (403) */
    public function test_employee_cannot_access_admin_area()
    {
        $response = $this->actingAs($this->employeeUser)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    /** 4. Employee dapat Check In dengan GPS dan foto valid */
    public function test_employee_can_check_in()
    {
        $response = $this->actingAs($this->employeeUser)->postJson('/attendance/check-in', [
            'latitude' => -7.795610, // ~15m from office
            'longitude' => 110.369510,
            'accuracy' => 15,
            'photo' => $this->getDummyBase64Photo(),
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $this->employee->id,
        ]);
        $attendance = Attendance::where('employee_id', $this->employee->id)->first();
        $this->assertEquals(Carbon::now('Asia/Jakarta')->toDateString(), $attendance->attendance_date->toDateString());
    }

    /** 5. Employee tidak dapat Check In dua kali pada tanggal yang sama */
    public function test_employee_cannot_check_in_twice()
    {
        // First check in
        $this->actingAs($this->employeeUser)->postJson('/attendance/check-in', [
            'latitude' => -7.795610,
            'longitude' => 110.369510,
            'accuracy' => 15,
            'photo' => $this->getDummyBase64Photo(),
        ])->assertStatus(200);

        // Second check in attempt
        $response = $this->actingAs($this->employeeUser)->postJson('/attendance/check-in', [
            'latitude' => -7.795610,
            'longitude' => 110.369510,
            'accuracy' => 15,
            'photo' => $this->getDummyBase64Photo(),
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    /** 6. Employee tidak dapat Check Out sebelum Check In */
    public function test_employee_cannot_checkout_before_checkin()
    {
        $response = $this->actingAs($this->employeeUser)->postJson('/attendance/check-out', [
            'latitude' => -7.795610,
            'longitude' => 110.369510,
            'accuracy' => 15,
            'photo' => $this->getDummyBase64Photo(),
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    /** 7. Employee dapat Check Out setelah Check In */
    public function test_employee_can_checkout_after_checkin()
    {
        $this->actingAs($this->employeeUser)->postJson('/attendance/check-in', [
            'latitude' => -7.795610,
            'longitude' => 110.369510,
            'accuracy' => 15,
            'photo' => $this->getDummyBase64Photo(),
        ])->assertStatus(200);

        $response = $this->actingAs($this->employeeUser)->postJson('/attendance/check-out', [
            'latitude' => -7.795610,
            'longitude' => 110.369510,
            'accuracy' => 15,
            'photo' => $this->getDummyBase64Photo(),
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $attendance = Attendance::where('employee_id', $this->employee->id)->first();
        $this->assertNotNull($attendance->check_out_at);
    }

    /** 8. GPS di luar radius kantor ditolak */
    public function test_outside_radius_is_rejected()
    {
        // 5 km away from office
        $response = $this->actingAs($this->employeeUser)->postJson('/attendance/check-in', [
            'latitude' => -7.850000,
            'longitude' => 110.400000,
            'accuracy' => 10,
            'photo' => $this->getDummyBase64Photo(),
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertStringContainsString('luar area', $response->json('message'));
    }

    /** 9. Akurasi GPS buruk (> threshold) ditolak */
    public function test_poor_gps_accuracy_is_rejected()
    {
        // Accuracy 250m (> 100m limit)
        $response = $this->actingAs($this->employeeUser)->postJson('/attendance/check-in', [
            'latitude' => -7.795610,
            'longitude' => 110.369510,
            'accuracy' => 250,
            'photo' => $this->getDummyBase64Photo(),
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertStringContainsString('Akurasi GPS', $response->json('message'));
    }

    /** 10. Hari non-kerja ditolak */
    public function test_non_working_day_is_rejected()
    {
        $todayDayOfWeek = Carbon::now('Asia/Jakarta')->dayOfWeek;
        WorkingDay::where('day_of_week', $todayDayOfWeek)->update(['is_working_day' => false]);

        $response = $this->actingAs($this->employeeUser)->postJson('/attendance/check-in', [
            'latitude' => -7.795610,
            'longitude' => 110.369510,
            'accuracy' => 15,
            'photo' => $this->getDummyBase64Photo(),
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('bukan hari kerja', $response->json('message'));
    }

    /** 11. Karyawan inactive ditolak */
    public function test_inactive_employee_is_rejected()
    {
        $this->employee->update(['status' => 'inactive']);

        $response = $this->actingAs($this->employeeUser)->postJson('/attendance/check-in', [
            'latitude' => -7.795610,
            'longitude' => 110.369510,
            'accuracy' => 15,
            'photo' => $this->getDummyBase64Photo(),
        ]);

        $response->assertStatus(403);
    }

    /** 12. Admin dapat melihat live monitoring dan detail presensi */
    public function test_admin_can_view_monitoring_and_detail()
    {
        // Check in
        $this->actingAs($this->employeeUser)->postJson('/attendance/check-in', [
            'latitude' => -7.795610,
            'longitude' => 110.369510,
            'accuracy' => 15,
            'photo' => $this->getDummyBase64Photo(),
        ])->assertStatus(200);

        $attendance = Attendance::first();

        // Admin view index
        $this->actingAs($this->adminUser)->get('/admin/attendance')->assertStatus(200);

        // Admin view show
        $this->actingAs($this->adminUser)->get("/admin/attendance/{$attendance->id}")->assertStatus(200);
    }

    /** 13. Audit log tercatat */
    public function test_audit_logs_are_recorded()
    {
        $this->actingAs($this->employeeUser)->postJson('/attendance/check-in', [
            'latitude' => -7.795610,
            'longitude' => 110.369510,
            'accuracy' => 15,
            'photo' => $this->getDummyBase64Photo(),
        ])->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'CHECK_IN',
            'module' => 'attendance',
        ]);
    }

    /** 14. Export Excel dan PDF berhasil */
    public function test_admin_can_export_reports()
    {
        $this->actingAs($this->adminUser)->get('/admin/reports/attendance/export/excel')->assertStatus(200);
        $this->actingAs($this->adminUser)->get('/admin/reports/attendance/export/pdf')->assertStatus(200);
    }
}
