<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today('Asia/Jakarta')->toDateString();

        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();

        // Today's stats
        $todayAttendances = Attendance::where('attendance_date', $today)->get();
        $totalPresentToday = $todayAttendances->count();
        $onTimeCount = $todayAttendances->where('check_in_status', 'on_time')->count();
        $lateCount = $todayAttendances->where('check_in_status', 'late')->count();
        $pendingCheckoutCount = $todayAttendances->whereNull('check_out_at')->count();
        $absentCount = max(0, $activeEmployees - $totalPresentToday);

        // Recent today's attendances with employee & branch
        $recentToday = Attendance::with(['employee.department', 'employee.position', 'branch'])
            ->where('attendance_date', $today)
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        // Branch summary
        $branches = Branch::withCount(['employees' => fn($q) => $q->where('status', 'active')])->get();

        // Department summary
        $departments = Department::withCount('employees')->get();

        // 7-day attendance trend
        $trendDates = [];
        $trendPresent = [];
        $trendLate = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today('Asia/Jakarta')->subDays($i);
            $dateStr = $date->toDateString();
            $trendDates[] = $date->format('d M');

            $dayRecords = Attendance::where('attendance_date', $dateStr)->get();
            $trendPresent[] = $dayRecords->where('check_in_status', 'on_time')->count();
            $trendLate[] = $dayRecords->where('check_in_status', 'late')->count();
        }

        return view('admin.dashboard', compact(
            'totalEmployees',
            'activeEmployees',
            'totalPresentToday',
            'onTimeCount',
            'lateCount',
            'pendingCheckoutCount',
            'absentCount',
            'recentToday',
            'branches',
            'departments',
            'trendDates',
            'trendPresent',
            'trendLate'
        ));
    }
}
