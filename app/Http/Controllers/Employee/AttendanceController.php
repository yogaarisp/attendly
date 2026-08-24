<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\AttendanceService;
use App\Services\FileUploadService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use InvalidArgumentException;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected FileUploadService $fileUploadService
    ) {}

    /**
     * Show Check-In page.
     */
    public function createCheckIn(): View|\Illuminate\Http\RedirectResponse
    {
        $employee = Auth::user()->employee()->with('branch.attendanceSetting')->first();
        $branch = $employee->branch;
        $timezone = $branch?->timezone ?: 'Asia/Jakarta';
        $today = Carbon::now($timezone)->toDateString();

        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('attendance_date', $today)
            ->first();

        if ($todayAttendance && $todayAttendance->hasCheckedIn()) {
            return redirect()->route('employee.dashboard')
                ->with('info', 'Anda sudah melakukan absensi masuk hari ini.');
        }

        return view('employee.attendance.checkin', compact('employee', 'branch', 'timezone'));
    }

    /**
     * Store Check-In.
     */
    public function storeCheckIn(Request $request): JsonResponse
    {
        $request->validate([
            'latitude'    => ['required', 'numeric', 'between:-90,90'],
            'longitude'   => ['required', 'numeric', 'between:-180,180'],
            'accuracy'    => ['required', 'numeric', 'min:0'],
            'photo'       => ['required', 'string'],
            'gps_samples' => ['sometimes', 'array', 'max:10'],
            'gps_samples.*.lat'       => ['required_with:gps_samples', 'numeric', 'between:-90,90'],
            'gps_samples.*.lng'       => ['required_with:gps_samples', 'numeric', 'between:-180,180'],
            'gps_samples.*.accuracy'  => ['required_with:gps_samples', 'numeric', 'min:0'],
            'gps_samples.*.timestamp' => ['required_with:gps_samples', 'integer'],
        ]);

        $employee = Auth::user()->employee()->with('branch.attendanceSetting')->first();

        try {
            $attendance = $this->attendanceService->processCheckIn(
                $employee,
                (float) $request->input('latitude'),
                (float) $request->input('longitude'),
                (float) $request->input('accuracy'),
                $request->input('photo'),
                $request->input('gps_samples', [])
            );

            return response()->json([
                'success' => true,
                'message' => 'Absensi masuk berhasil dicatat!',
                'data'    => [
                    'check_in_at'    => $attendance->check_in_at->format('H:i:s'),
                    'status'         => $attendance->check_in_status_label,
                    'overall_status' => $attendance->overall_status_label,
                    'distance'       => $attendance->check_in_distance,
                    'is_suspicious'  => $attendance->is_suspicious,
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memproses absensi: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show Check-Out page.
     */
    public function createCheckOut(): View|\Illuminate\Http\RedirectResponse
    {
        $employee = Auth::user()->employee()->with('branch.attendanceSetting')->first();
        $branch = $employee->branch;
        $timezone = $branch?->timezone ?: 'Asia/Jakarta';
        $today = Carbon::now($timezone)->toDateString();

        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('attendance_date', $today)
            ->first();

        if (!$todayAttendance || !$todayAttendance->hasCheckedIn()) {
            return redirect()->route('employee.dashboard')
                ->with('error', 'Anda belum melakukan absensi masuk hari ini.');
        }

        if ($todayAttendance->hasCheckedOut()) {
            return redirect()->route('employee.dashboard')
                ->with('info', 'Anda sudah melakukan absensi pulang hari ini.');
        }

        return view('employee.attendance.checkout', compact('employee', 'branch', 'todayAttendance', 'timezone'));
    }

    /**
     * Store Check-Out.
     */
    public function storeCheckOut(Request $request): JsonResponse
    {
        $request->validate([
            'latitude'    => ['required', 'numeric', 'between:-90,90'],
            'longitude'   => ['required', 'numeric', 'between:-180,180'],
            'accuracy'    => ['required', 'numeric', 'min:0'],
            'photo'       => ['required', 'string'],
            'gps_samples' => ['sometimes', 'array', 'max:10'],
            'gps_samples.*.lat'       => ['required_with:gps_samples', 'numeric', 'between:-90,90'],
            'gps_samples.*.lng'       => ['required_with:gps_samples', 'numeric', 'between:-180,180'],
            'gps_samples.*.accuracy'  => ['required_with:gps_samples', 'numeric', 'min:0'],
            'gps_samples.*.timestamp' => ['required_with:gps_samples', 'integer'],
        ]);

        $employee = Auth::user()->employee()->with('branch.attendanceSetting')->first();

        try {
            $attendance = $this->attendanceService->processCheckOut(
                $employee,
                (float) $request->input('latitude'),
                (float) $request->input('longitude'),
                (float) $request->input('accuracy'),
                $request->input('photo'),
                $request->input('gps_samples', [])
            );

            return response()->json([
                'success' => true,
                'message' => 'Absensi pulang berhasil dicatat!',
                'data' => [
                    'check_out_at' => $attendance->check_out_at->format('H:i:s'),
                    'check_out_status' => $attendance->check_out_status,
                    'overall_status' => $attendance->overall_status_label,
                    'distance' => $attendance->check_out_distance,
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memproses absensi pulang: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Securely stream private attendance photo.
     */
    public function showPhoto(Attendance $attendance, string $type): Response
    {
        Gate::authorize('viewPhoto', $attendance);

        $path = ($type === 'check_out') ? $attendance->check_out_photo : $attendance->check_in_photo;

        if (!$path) {
            abort(404, 'Foto tidak ditemukan.');
        }

        $stream = $this->fileUploadService->getPhotoStream($path);
        if (!$stream) {
            abort(404, 'File foto di storage tidak ditemukan.');
        }

        return response($stream, 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
