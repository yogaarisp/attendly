<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\WorkingDay;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AttendanceService
{
    public function __construct(
        protected GeolocationService $geolocationService,
        protected FileUploadService $fileUploadService
    ) {}

    /**
     * Process Employee Check In.
     *
     * @param  array  $gpsSamples  Array of GPS readings dari browser
     *                             [['lat'=>float,'lng'=>float,'accuracy'=>float,'timestamp'=>int], ...]
     * @throws InvalidArgumentException
     */
    public function processCheckIn(
        Employee $employee,
        float    $latitude,
        float    $longitude,
        float    $accuracy,
        mixed    $photoData,
        array    $gpsSamples = []
    ): Attendance {
        return DB::transaction(function () use ($employee, $latitude, $longitude, $accuracy, $photoData, $gpsSamples) {

            // 1. Verify employee status
            if (!$employee->isActive()) {
                throw new InvalidArgumentException('Akun karyawan Anda tidak aktif. Tidak dapat melakukan absensi.');
            }

            // 2. Load branch and timezone
            $branch = $employee->branch;
            if (!$branch || $branch->status !== 'active') {
                throw new InvalidArgumentException('Kantor cabang Anda tidak aktif atau tidak ditemukan.');
            }

            $timezone = $branch->timezone ?: 'Asia/Jakarta';
            $now      = Carbon::now($timezone);
            $today    = $now->toDateString();

            // 3. Verify attendance is enabled for branch
            $setting = $branch->attendanceSetting;
            if ($setting && !$setting->attendance_enabled) {
                throw new InvalidArgumentException('Sistem absensi untuk cabang ini sedang dinonaktifkan oleh administrator.');
            }

            // 4. Verify working day
            $dayOfWeek  = $now->dayOfWeek;
            $workingDay = WorkingDay::where('branch_id', $branch->id)
                ->where('day_of_week', $dayOfWeek)
                ->first();

            if (!$workingDay) {
                $workingDay = WorkingDay::whereNull('branch_id')
                    ->where('day_of_week', $dayOfWeek)
                    ->first();
            }

            if ($workingDay && !$workingDay->is_working_day) {
                throw new InvalidArgumentException('Hari ini bukan hari kerja. Anda tidak dapat melakukan absensi.');
            }

            // 5. Check duplicate attendance
            $existingAttendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('attendance_date', $today)
                ->lockForUpdate()
                ->first();

            if ($existingAttendance && $existingAttendance->hasCheckedIn()) {
                throw new InvalidArgumentException('Anda sudah melakukan absensi masuk hari ini.');
            }

            // 6. Validate GPS radius (hard rejection — di luar area / akurasi buruk)
            $locValidation = $this->geolocationService->validateLocation($branch, $latitude, $longitude, $accuracy);
            if (!$locValidation['valid']) {
                AuditLogService::log('CHECK_IN_REJECTED', 'attendance', null, [
                    'employee_id' => $employee->id,
                    'reason'      => $locValidation['error'],
                    'latitude'    => $latitude,
                    'longitude'   => $longitude,
                    'distance'    => $locValidation['distance'],
                ], $employee->user_id);

                throw new InvalidArgumentException($locValidation['error']);
            }

            $distance = $locValidation['distance'];

            // 7. ── Anti-Fake GPS: Analisis multi-sample ─────────────────────
            $suspiciousReasons = [];

            // 7a. Analisis pola GPS dari sample yang dikumpulkan browser
            $sampleAnalysis = $this->geolocationService->analyzeSamples($gpsSamples);
            if ($sampleAnalysis['suspicious']) {
                $suspiciousReasons = array_merge($suspiciousReasons, $sampleAnalysis['reasons']);
            }

            // 7b. Speed plausibility vs. absensi terakhir karyawan (lintas hari)
            $lastAttendance = Attendance::where('employee_id', $employee->id)
                ->whereNotNull('check_in_at')
                ->whereNotNull('check_in_latitude')
                ->whereNotNull('check_in_longitude')
                ->orderByDesc('check_in_at')
                ->first();

            if ($lastAttendance) {
                $speedCheck = $this->geolocationService->checkSpeedPlausibility(
                    (float) $lastAttendance->check_in_latitude,
                    (float) $lastAttendance->check_in_longitude,
                    $lastAttendance->check_in_at->timestamp,
                    $latitude,
                    $longitude,
                    $now->timestamp
                );

                if ($speedCheck['suspicious']) {
                    $suspiciousReasons[] = $speedCheck['reason'];
                }
            }

            $isSuspicious = !empty($suspiciousReasons);

            // 7c. Catat audit log jika terdeteksi mencurigakan
            if ($isSuspicious) {
                AuditLogService::log('CHECK_IN_SUSPICIOUS', 'attendance', null, [
                    'employee_id'       => $employee->id,
                    'employee_code'     => $employee->employee_code,
                    'latitude'          => $latitude,
                    'longitude'         => $longitude,
                    'suspicious_reasons'=> $suspiciousReasons,
                ], $employee->user_id);
            }

            // 8. Store Photo
            $photoPath = $this->fileUploadService->storeAttendancePhoto($photoData);

            // 9. Calculate On Time / Late Status
            $startTimeStr     = $setting ? $setting->work_start_time : '08:00:00';
            $toleranceMinutes = $setting ? $setting->late_tolerance_minutes : 15;

            $workStart      = Carbon::parse("{$today} {$startTimeStr}", $timezone);
            $lateThreshold  = $workStart->copy()->addMinutes($toleranceMinutes);
            $checkInStatus  = $now->lte($lateThreshold) ? 'on_time' : 'late';
            $overallStatus  = ($checkInStatus === 'on_time') ? 'present' : 'late';

            // 10. Save Attendance Record
            $attendance = Attendance::create([
                'employee_id'        => $employee->id,
                'branch_id'          => $branch->id,
                'attendance_date'    => $today,
                'check_in_at'        => $now,
                'check_in_photo'     => $photoPath,
                'check_in_latitude'  => $latitude,
                'check_in_longitude' => $longitude,
                'check_in_accuracy'  => $accuracy,
                'check_in_distance'  => $distance,
                'check_in_status'    => $checkInStatus,
                'overall_status'     => $overallStatus,
                'is_suspicious'      => $isSuspicious,
                'suspicious_reasons' => $isSuspicious ? $suspiciousReasons : null,
                'gps_samples'        => !empty($gpsSamples) ? $gpsSamples : null,
            ]);

            // 11. Audit Log
            AuditLogService::log('CHECK_IN', 'attendance', $attendance->id, [
                'employee_id'    => $employee->id,
                'employee_code'  => $employee->employee_code,
                'branch'         => $branch->name,
                'status'         => $checkInStatus,
                'distance_meters'=> $distance,
                'is_suspicious'  => $isSuspicious,
                'time'           => $now->toDateTimeString(),
            ], $employee->user_id);

            return $attendance;
        });
    }

    /**
     * Process Employee Check Out.
     *
     * @param  array  $gpsSamples  Array of GPS readings dari browser
     * @throws InvalidArgumentException
     */
    public function processCheckOut(
        Employee $employee,
        float    $latitude,
        float    $longitude,
        float    $accuracy,
        mixed    $photoData,
        array    $gpsSamples = []
    ): Attendance {
        return DB::transaction(function () use ($employee, $latitude, $longitude, $accuracy, $photoData, $gpsSamples) {

            $branch = $employee->branch;
            if (!$branch || $branch->status !== 'active') {
                throw new InvalidArgumentException('Kantor cabang Anda tidak aktif atau tidak ditemukan.');
            }

            $timezone = $branch->timezone ?: 'Asia/Jakarta';
            $now      = Carbon::now($timezone);
            $today    = $now->toDateString();

            // 1. Must have check-in record for today
            $attendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('attendance_date', $today)
                ->lockForUpdate()
                ->first();

            if (!$attendance || !$attendance->hasCheckedIn()) {
                throw new InvalidArgumentException('Anda belum melakukan absensi masuk hari ini.');
            }

            if ($attendance->hasCheckedOut()) {
                throw new InvalidArgumentException('Anda sudah melakukan absensi pulang hari ini.');
            }

            // 2. Validate GPS radius
            $locValidation = $this->geolocationService->validateLocation($branch, $latitude, $longitude, $accuracy);
            if (!$locValidation['valid']) {
                AuditLogService::log('CHECK_OUT_REJECTED', 'attendance', $attendance->id, [
                    'employee_id' => $employee->id,
                    'reason'      => $locValidation['error'],
                    'latitude'    => $latitude,
                    'longitude'   => $longitude,
                    'distance'    => $locValidation['distance'],
                ], $employee->user_id);

                throw new InvalidArgumentException($locValidation['error']);
            }

            $distance = $locValidation['distance'];

            // 3. ── Anti-Fake GPS: Analisis multi-sample ─────────────────────
            $suspiciousReasons = $attendance->suspicious_reasons ?? [];

            // 3a. Analisis sample GPS checkout
            $sampleAnalysis = $this->geolocationService->analyzeSamples($gpsSamples);
            if ($sampleAnalysis['suspicious']) {
                $suspiciousReasons = array_merge($suspiciousReasons, array_map(
                    fn($r) => "[Pulang] {$r}",
                    $sampleAnalysis['reasons']
                ));
            }

            // 3b. Speed plausibility: jarak antara check-in dan check-out hari ini
            if ($attendance->check_in_latitude && $attendance->check_in_longitude) {
                $speedCheck = $this->geolocationService->checkSpeedPlausibility(
                    (float) $attendance->check_in_latitude,
                    (float) $attendance->check_in_longitude,
                    $attendance->check_in_at->timestamp,
                    $latitude,
                    $longitude,
                    $now->timestamp
                );

                if ($speedCheck['suspicious']) {
                    $suspiciousReasons[] = "[Pulang] " . $speedCheck['reason'];
                }
            }

            $isSuspicious = !empty($suspiciousReasons);

            if ($isSuspicious && !$attendance->is_suspicious) {
                AuditLogService::log('CHECK_OUT_SUSPICIOUS', 'attendance', $attendance->id, [
                    'employee_id'        => $employee->id,
                    'employee_code'      => $employee->employee_code,
                    'suspicious_reasons' => $suspiciousReasons,
                ], $employee->user_id);
            }

            // 4. Store Photo
            $photoPath = $this->fileUploadService->storeAttendancePhoto($photoData);

            // 5. Check out status calculation
            $setting       = $branch->attendanceSetting;
            $endTimeStr    = $setting ? $setting->work_end_time : '17:00:00';
            $workEnd       = Carbon::parse("{$today} {$endTimeStr}", $timezone);
            $checkOutStatus = $now->lt($workEnd) ? 'early_leave' : 'normal';
            $overallStatus  = ($attendance->check_in_status === 'late') ? 'late' : 'present';

            // 6. Update attendance record
            $attendance->update([
                'check_out_at'        => $now,
                'check_out_photo'     => $photoPath,
                'check_out_latitude'  => $latitude,
                'check_out_longitude' => $longitude,
                'check_out_accuracy'  => $accuracy,
                'check_out_distance'  => $distance,
                'check_out_status'    => $checkOutStatus,
                'overall_status'      => $overallStatus,
                'is_suspicious'       => $isSuspicious,
                'suspicious_reasons'  => $isSuspicious ? $suspiciousReasons : null,
            ]);

            // 7. Audit Log
            AuditLogService::log('CHECK_OUT', 'attendance', $attendance->id, [
                'employee_id'     => $employee->id,
                'employee_code'   => $employee->employee_code,
                'branch'          => $branch->name,
                'status'          => $checkOutStatus,
                'distance_meters' => $distance,
                'is_suspicious'   => $isSuspicious,
                'time'            => $now->toDateTimeString(),
            ], $employee->user_id);

            return $attendance;
        });
    }
}
