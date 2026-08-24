<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\Branch;
use App\Models\WorkingDay;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceSettingController extends Controller
{
    public function edit(Request $request): View
    {
        $branchId = $request->input('branch_id');
        $branches = Branch::active()->get();

        $selectedBranch = $branchId ? Branch::find($branchId) : $branches->first();

        $setting = $selectedBranch ? AttendanceSetting::firstOrCreate(
            ['branch_id' => $selectedBranch->id],
            [
                'work_start_time' => '08:00:00',
                'work_end_time' => '17:00:00',
                'late_tolerance_minutes' => 15,
                'minimum_gps_accuracy' => 100,
                'attendance_enabled' => true,
            ]
        ) : null;

        // Working Days (0=Sun .. 6=Sat)
        $workingDays = WorkingDay::whereNull('branch_id')->get()->keyBy('day_of_week');

        return view('admin.settings.attendance', compact('branches', 'selectedBranch', 'setting', 'workingDays'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'work_start_time' => ['required', 'date_format:H:i'],
            'work_end_time' => ['required', 'date_format:H:i'],
            'late_tolerance_minutes' => ['required', 'integer', 'min:0', 'max:180'],
            'minimum_gps_accuracy' => ['required', 'integer', 'min:10', 'max:500'],
            'attendance_enabled' => ['required', 'boolean'],
            'working_days' => ['required', 'array'],
        ]);

        DB::transaction(function () use ($validated) {
            AttendanceSetting::updateOrCreate(
                ['branch_id' => $validated['branch_id']],
                [
                    'work_start_time' => $validated['work_start_time'].':00',
                    'work_end_time' => $validated['work_end_time'].':00',
                    'late_tolerance_minutes' => $validated['late_tolerance_minutes'],
                    'minimum_gps_accuracy' => $validated['minimum_gps_accuracy'],
                    'attendance_enabled' => $validated['attendance_enabled'],
                ]
            );

            // Update Working Days (0=Sun .. 6=Sat)
            for ($day = 0; $day <= 6; $day++) {
                $isWorking = isset($validated['working_days'][$day]) && $validated['working_days'][$day] == '1';

                WorkingDay::updateOrCreate(
                    ['branch_id' => null, 'day_of_week' => $day],
                    ['is_working_day' => $isWorking]
                );
            }

            AuditLogService::log('ATTENDANCE_SETTINGS_UPDATED', 'settings', $validated['branch_id'], [
                'start_time' => $validated['work_start_time'],
                'end_time' => $validated['work_end_time'],
                'tolerance' => $validated['late_tolerance_minutes'],
            ]);
        });

        return redirect()->route('admin.settings.attendance', ['branch_id' => $validated['branch_id']])
            ->with('success', 'Pengaturan absensi & hari kerja berhasil disimpan.');
    }
}
