<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function index(Request $request): View
    {
        $departmentId = $request->input('department_id');
        $query = Position::with('department')->withCount('employees');

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $positions = $query->get();
        $departments = Department::active()->get();

        return view('admin.positions.index', compact('positions', 'departments', 'departmentId'));
    }

    public function getByDepartment(Department $department): JsonResponse
    {
        return response()->json($department->positions()->where('status', 'active')->get());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $pos = Position::create($validated);

        AuditLogService::log('POSITION_CREATED', 'position', $pos->id, ['name' => $pos->name]);

        return redirect()->route('admin.positions.index')->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $position->update($validated);

        AuditLogService::log('POSITION_UPDATED', 'position', $position->id, ['name' => $position->name]);

        return redirect()->route('admin.positions.index')->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        if ($position->employees()->count() > 0) {
            return redirect()->route('admin.positions.index')
                ->with('error', 'Jabatan tidak dapat dihapus karena masih digunakan oleh karyawan.');
        }

        $position->delete();

        AuditLogService::log('POSITION_DELETED', 'position', $position->id, ['name' => $position->name]);

        return redirect()->route('admin.positions.index')->with('success', 'Jabatan berhasil dihapus.');
    }
}
