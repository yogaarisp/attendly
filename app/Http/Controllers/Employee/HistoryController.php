<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        $employee = Auth::user()->employee;
        $now = Carbon::now();

        $month = $request->input('month', $now->format('m'));
        $year = $request->input('year', $now->format('Y'));
        $status = $request->input('status');

        $query = Attendance::where('employee_id', $employee->id)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month);

        if ($status) {
            $query->where('overall_status', $status);
        }

        $attendances = $query->orderByDesc('attendance_date')->paginate(15)->withQueryString();

        // Statistics for current selected month
        $stats = [
            'present' => Attendance::where('employee_id', $employee->id)
                ->whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $month)
                ->where('check_in_status', 'on_time')
                ->count(),
            'late' => Attendance::where('employee_id', $employee->id)
                ->whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $month)
                ->where('check_in_status', 'late')
                ->count(),
            'total' => Attendance::where('employee_id', $employee->id)
                ->whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $month)
                ->count(),
        ];

        return view('employee.history.index', compact('attendances', 'stats', 'month', 'year', 'status', 'employee'));
    }
}
