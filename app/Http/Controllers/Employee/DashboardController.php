<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\WorkingDay;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $employee = $user->employee()->with(['branch.attendanceSetting', 'department', 'position'])->first();
        $branch = $employee->branch;
        $setting = $branch?->attendanceSetting;

        $timezone = $branch?->timezone ?: 'Asia/Jakarta';
        $now = Carbon::now($timezone);
        $today = $now->toDateString();

        // Today's attendance
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('attendance_date', $today)
            ->first();

        // Check if today is working day
        $dayOfWeek = $now->dayOfWeek;
        $workingDay = WorkingDay::where('branch_id', $branch?->id)
            ->where('day_of_week', $dayOfWeek)
            ->first() ?? WorkingDay::whereNull('branch_id')->where('day_of_week', $dayOfWeek)->first();

        $isWorkingDay = $workingDay ? (bool) $workingDay->is_working_day : ($dayOfWeek >= 1 && $dayOfWeek <= 5);

        // Recent 5 attendances for mini history / timeline
        $recentAttendances = Attendance::where('employee_id', $employee->id)
            ->orderByDesc('attendance_date')
            ->limit(5)
            ->get();

        // Monthly statistics (present, late, total)
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();

        $monthStats = [
            'present' => Attendance::where('employee_id', $employee->id)
                ->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
                ->where('check_in_status', 'on_time')
                ->count(),
            'late' => Attendance::where('employee_id', $employee->id)
                ->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
                ->where('check_in_status', 'late')
                ->count(),
            'total' => Attendance::where('employee_id', $employee->id)
                ->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
                ->count(),
        ];

        return view('employee.dashboard', compact(
            'employee',
            'branch',
            'setting',
            'todayAttendance',
            'isWorkingDay',
            'recentAttendances',
            'monthStats',
            'timezone'
        ));
    }
}
