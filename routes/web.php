<?php

use App\Http\Controllers\Admin\AttendanceMonitoringController;
use App\Http\Controllers\Admin\AttendanceSettingController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\HistoryController;
use App\Http\Controllers\Employee\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root redirect
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    return auth()->user()->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('employee.dashboard');
});

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Employee Protected Routes
Route::middleware(['auth', 'role:employee', 'active_employee'])->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
    Route::get('/attendance/check-in', [EmployeeAttendanceController::class, 'createCheckIn'])->name('employee.attendance.checkin');
    Route::post('/attendance/check-in', [EmployeeAttendanceController::class, 'storeCheckIn'])
        ->middleware('throttle:6,1')
        ->name('employee.attendance.checkin.store');

    Route::get('/attendance/check-out', [EmployeeAttendanceController::class, 'createCheckOut'])->name('employee.attendance.checkout');
    Route::post('/attendance/check-out', [EmployeeAttendanceController::class, 'storeCheckOut'])
        ->middleware('throttle:6,1')
        ->name('employee.attendance.checkout.store');

    Route::get('/attendance/history', [HistoryController::class, 'index'])->name('employee.history');
    Route::get('/profile', [ProfileController::class, 'show'])->name('employee.profile');
});

// Authorized Photo View Route (Shared Policy for Admin & Employee)
Route::get('/attendance/{attendance}/photo/{type}', [EmployeeAttendanceController::class, 'showPhoto'])
    ->middleware('auth')
    ->name('attendance.photo');

// Admin Protected Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Employees
    Route::resource('employees', EmployeeController::class)->except(['show']);

    // Departments & Positions
    Route::resource('departments', DepartmentController::class)->except(['create', 'edit', 'show']);
    Route::resource('positions', PositionController::class)->except(['create', 'edit', 'show']);
    Route::get('/positions/by-department/{department}', [PositionController::class, 'getByDepartment'])->name('positions.by-dept');

    // Branches & Settings
    Route::resource('branches', BranchController::class)->except(['show']);
    Route::get('/settings/attendance', [AttendanceSettingController::class, 'edit'])->name('settings.attendance');
    Route::put('/settings/attendance', [AttendanceSettingController::class, 'update'])->name('settings.attendance.update');

    // Attendance Live Monitoring & Detail
    Route::get('/attendance', [AttendanceMonitoringController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{attendance}', [AttendanceMonitoringController::class, 'show'])->name('attendance.show');

    // Reports & Exports
    Route::get('/reports/attendance', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/attendance/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('/reports/attendance/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});
