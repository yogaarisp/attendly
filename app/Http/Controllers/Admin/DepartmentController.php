<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $departments = Department::withCount(['positions', 'employees'])->get();
        return view('admin.departments.index', compact('departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:departments,code'],
            'name' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $dept = Department::create($validated);

        AuditLogService::log('DEPARTMENT_CREATED', 'department', $dept->id, ['name' => $dept->name]);

        return redirect()->route('admin.departments.index')->with('success', 'Departemen berhasil ditambahkan.');
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('departments')->ignore($department->id)],
            'name' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $department->update($validated);

        AuditLogService::log('DEPARTMENT_UPDATED', 'department', $department->id, ['name' => $department->name]);

        return redirect()->route('admin.departments.index')->with('success', 'Departemen berhasil diperbarui.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->employees()->count() > 0) {
            return redirect()->route('admin.departments.index')
                ->with('error', 'Departemen tidak dapat dihapus karena masih memiliki karyawan.');
        }

        $department->positions()->delete();
        $department->delete();

        AuditLogService::log('DEPARTMENT_DELETED', 'department', $department->id, ['name' => $department->name]);

        return redirect()->route('admin.departments.index')->with('success', 'Departemen berhasil dihapus.');
    }
}
