<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\Branch;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View
    {
        $branches = Branch::with('attendanceSetting')
            ->withCount(['employees' => fn($q) => $q->where('status', 'active')])
            ->get();

        return view('admin.branches.index', compact('branches'));
    }

    public function create(): View
    {
        return view('admin.branches.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:branches,code'],
            'name' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meter' => ['required', 'integer', 'min:10', 'max:5000'],
            'timezone' => ['required', 'string', 'in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura'],
            'status' => ['required', 'in:active,inactive'],
            'work_start_time' => ['required', 'date_format:H:i'],
            'work_end_time' => ['required', 'date_format:H:i'],
            'late_tolerance_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'minimum_gps_accuracy' => ['required', 'integer', 'min:10', 'max:500'],
        ]);

        DB::transaction(function () use ($validated) {
            $branch = Branch::create([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'radius_meter' => $validated['radius_meter'],
                'timezone' => $validated['timezone'],
                'status' => $validated['status'],
            ]);

            AttendanceSetting::create([
                'branch_id' => $branch->id,
                'work_start_time' => $validated['work_start_time'].':00',
                'work_end_time' => $validated['work_end_time'].':00',
                'late_tolerance_minutes' => $validated['late_tolerance_minutes'],
                'minimum_gps_accuracy' => $validated['minimum_gps_accuracy'],
                'attendance_enabled' => true,
            ]);

            AuditLogService::log('BRANCH_CREATED', 'branch', $branch->id, [
                'name' => $branch->name,
                'radius' => $branch->radius_meter,
            ]);
        });

        return redirect()->route('admin.branches.index')->with('success', 'Kantor cabang berhasil ditambahkan.');
    }

    public function edit(Branch $branch): View
    {
        $branch->load('attendanceSetting');
        return view('admin.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('branches')->ignore($branch->id)],
            'name' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meter' => ['required', 'integer', 'min:10', 'max:5000'],
            'timezone' => ['required', 'string', 'in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura'],
            'status' => ['required', 'in:active,inactive'],
            'work_start_time' => ['required', 'date_format:H:i'],
            'work_end_time' => ['required', 'date_format:H:i'],
            'late_tolerance_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'minimum_gps_accuracy' => ['required', 'integer', 'min:10', 'max:500'],
            'attendance_enabled' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $branch) {
            $branch->update([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'radius_meter' => $validated['radius_meter'],
                'timezone' => $validated['timezone'],
                'status' => $validated['status'],
            ]);

            AttendanceSetting::updateOrCreate(
                ['branch_id' => $branch->id],
                [
                    'work_start_time' => $validated['work_start_time'].':00',
                    'work_end_time' => $validated['work_end_time'].':00',
                    'late_tolerance_minutes' => $validated['late_tolerance_minutes'],
                    'minimum_gps_accuracy' => $validated['minimum_gps_accuracy'],
                    'attendance_enabled' => $validated['attendance_enabled'],
                ]
            );

            AuditLogService::log('BRANCH_UPDATED', 'branch', $branch->id, [
                'name' => $branch->name,
                'radius' => $branch->radius_meter,
            ]);
        });

        return redirect()->route('admin.branches.index')->with('success', 'Data kantor cabang berhasil diperbarui.');
    }
}
