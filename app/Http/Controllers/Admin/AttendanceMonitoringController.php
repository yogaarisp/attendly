<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceMonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->input('date', Carbon::today('Asia/Jakarta')->toDateString());
        $branchId = $request->input('branch_id');
        $departmentId = $request->input('department_id');
        $status = $request->input('status');
        $search = $request->input('search');

        $query = Attendance::with(['employee.department', 'employee.position', 'branch'])
            ->where('attendance_date', $date);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($departmentId) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $departmentId));
        }

        if ($status) {
            $query->where('overall_status', $status);
        }

        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        $attendances = $query->orderByDesc('check_in_at')->paginate(15)->withQueryString();

        // Summary counts for this date
        $allForDate = Attendance::where('attendance_date', $date)->get();
        $totalEmployees = Employee::where('status', 'active')->count();

        $stats = [
            'total_present' => $allForDate->count(),
            'on_time' => $allForDate->where('check_in_status', 'on_time')->count(),
            'late' => $allForDate->where('check_in_status', 'late')->count(),
            'pending_checkout' => $allForDate->whereNull('check_out_at')->count(),
            'absent' => max(0, $totalEmployees - $allForDate->count()),
        ];

        $branches = Branch::active()->get();
        $departments = Department::active()->get();

        return view('admin.attendance.index', compact(
            'attendances',
            'stats',
            'branches',
            'departments',
            'date',
            'branchId',
            'departmentId',
            'status',
            'search'
        ));
    }

    public function show(Attendance $attendance): View
    {
        $attendance->load(['employee.department', 'employee.position', 'employee.user', 'branch.attendanceSetting']);

        return view('admin.attendance.show', compact('attendance'));
    }
}
