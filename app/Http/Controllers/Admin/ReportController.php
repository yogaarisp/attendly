<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Services\AuditLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Build attendance filtered query.
     */
    protected function getFilteredQuery(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today('Asia/Jakarta')->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today('Asia/Jakarta')->toDateString());

        $query = Attendance::with(['employee.department', 'employee.position', 'branch'])
            ->whereBetween('attendance_date', [$startDate, $endDate]);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $request->input('department_id')));
        }

        if ($request->filled('position_id')) {
            $query->whereHas('employee', fn($q) => $q->where('position_id', $request->input('position_id')));
        }

        if ($request->filled('status')) {
            $query->where('overall_status', $request->input('status'));
        }

        return $query->orderBy('attendance_date', 'desc')->orderBy('employee_id');
    }

    public function index(Request $request): View
    {
        $startDate = $request->input('start_date', Carbon::today('Asia/Jakarta')->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today('Asia/Jakarta')->toDateString());

        $query = $this->getFilteredQuery($request);
        $attendances = $query->paginate(20)->withQueryString();

        $employees = Employee::orderBy('full_name')->get();
        $branches = Branch::active()->get();
        $departments = Department::active()->get();
        $positions = Position::active()->get();

        return view('admin.reports.index', compact(
            'attendances',
            'employees',
            'branches',
            'departments',
            'positions',
            'startDate',
            'endDate'
        ));
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $attendances = $this->getFilteredQuery($request)->get();

        AuditLogService::log('EXPORT_EXCEL', 'report', null, [
            'total_rows' => $attendances->count(),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Absensi');

        // Headers
        $headers = [
            'No', 'Tanggal', 'NIK / Kode', 'Nama Karyawan', 'Departemen', 'Jabatan',
            'Cabang', 'Jam Masuk', 'Jam Pulang', 'Status Masuk', 'Status Checkout',
            'Status Akhir', 'Lat Masuk', 'Lng Masuk', 'Akurasi Masuk (m)', 'Jarak Masuk (m)',
            'Lat Pulang', 'Lng Pulang', 'Akurasi Pulang (m)', 'Jarak Pulang (m)',
        ];

        $sheet->fromArray($headers, null, 'A1');

        // Header Styling
        $headerRange = 'A1:T1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4F46E5');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowNum = 2;
        foreach ($attendances as $idx => $att) {
            $sheet->fromArray([
                $idx + 1,
                $att->attendance_date->format('Y-m-d'),
                $att->employee->employee_code,
                $att->employee->full_name,
                $att->employee->department?->name ?? '-',
                $att->employee->position?->name ?? '-',
                $att->branch->name,
                $att->check_in_at ? $att->check_in_at->format('H:i:s') : '-',
                $att->check_out_at ? $att->check_out_at->format('H:i:s') : '-',
                $att->check_in_status_label,
                $att->check_out_status ? ucfirst($att->check_out_status) : '-',
                $att->overall_status_label,
                $att->check_in_latitude ?? '-',
                $att->check_in_longitude ?? '-',
                $att->check_in_accuracy ?? '-',
                $att->check_in_distance ?? '-',
                $att->check_out_latitude ?? '-',
                $att->check_out_longitude ?? '-',
                $att->check_out_accuracy ?? '-',
                $att->check_out_distance ?? '-',
            ], null, "A{$rowNum}");

            $rowNum++;
        }

        // Auto-fit column widths
        foreach (range('A', 'T') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'attendance_report_'.date('Ymd_His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportPdf(Request $request)
    {
        $attendances = $this->getFilteredQuery($request)->get();
        $startDate = $request->input('start_date', Carbon::today('Asia/Jakarta')->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today('Asia/Jakarta')->toDateString());

        AuditLogService::log('EXPORT_PDF', 'report', null, [
            'total_rows' => $attendances->count(),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $pdf = Pdf::loadView('admin.reports.pdf', [
            'attendances' => $attendances,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => Carbon::now('Asia/Jakarta')->format('d F Y H:i:s T'),
            'generatedBy' => auth()->user()->name,
        ])->setPaper('a4', 'landscape');

        $filename = 'attendance_report_'.date('Ymd_His').'.pdf';

        return $pdf->download($filename);
    }
}
