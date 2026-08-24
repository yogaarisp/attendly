<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $departmentId = $request->input('department_id');
        $branchId = $request->input('branch_id');
        $status = $request->input('status');

        $query = Employee::with(['user', 'department', 'position', 'branch']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $employees = $query->orderBy('full_name')->paginate(10)->withQueryString();
        $departments = Department::active()->get();
        $branches = Branch::active()->get();

        return view('admin.employees.index', compact('employees', 'departments', 'branches', 'search', 'departmentId', 'branchId', 'status'));
    }

    public function create(): View
    {
        $departments = Department::active()->with('positions')->get();
        $positions = Position::active()->get();
        $branches = Branch::active()->get();

        return view('admin.employees.create', compact('departments', 'positions', 'branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_code' => ['required', 'string', 'max:30', 'unique:employees,employee_code'],
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email', 'unique:employees,email'],
            'password' => ['required', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['required', 'in:male,female'],
            'department_id' => ['required', 'exists:departments,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'join_date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'employee',
            ]);

            $employee = Employee::create([
                'user_id' => $user->id,
                'employee_code' => $validated['employee_code'],
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'gender' => $validated['gender'],
                'department_id' => $validated['department_id'],
                'position_id' => $validated['position_id'],
                'branch_id' => $validated['branch_id'],
                'join_date' => $validated['join_date'],
                'status' => $validated['status'],
            ]);

            AuditLogService::log('EMPLOYEE_CREATED', 'employee', $employee->id, [
                'employee_code' => $employee->employee_code,
                'name' => $employee->full_name,
            ]);
        });

        return redirect()->route('admin.employees.index')
            ->with('success', 'Karyawan berhasil didaftarkan.');
    }

    public function edit(Employee $employee): View
    {
        $employee->load(['user', 'department', 'position', 'branch']);
        $departments = Department::active()->with('positions')->get();
        $positions = Position::where('department_id', $employee->department_id)->get();
        $branches = Branch::active()->get();

        return view('admin.employees.edit', compact('employee', 'departments', 'positions', 'branches'));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'employee_code' => ['required', 'string', 'max:30', Rule::unique('employees')->ignore($employee->id)],
            'full_name' => ['required', 'string', 'max:150'],
            'email' => [
                'required', 'string', 'email', 'max:150',
                Rule::unique('users', 'email')->ignore($employee->user_id),
                Rule::unique('employees', 'email')->ignore($employee->id),
            ],
            'password' => ['nullable', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['required', 'in:male,female'],
            'department_id' => ['required', 'exists:departments,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'join_date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        DB::transaction(function () use ($validated, $employee) {
            // Update User
            $userPayload = [
                'name' => $validated['full_name'],
                'email' => $validated['email'],
            ];

            if (!empty($validated['password'])) {
                $userPayload['password'] = Hash::make($validated['password']);
            }

            $employee->user->update($userPayload);

            // Update Employee
            $employee->update([
                'employee_code' => $validated['employee_code'],
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'gender' => $validated['gender'],
                'department_id' => $validated['department_id'],
                'position_id' => $validated['position_id'],
                'branch_id' => $validated['branch_id'],
                'join_date' => $validated['join_date'],
                'status' => $validated['status'],
            ]);

            AuditLogService::log('EMPLOYEE_UPDATED', 'employee', $employee->id, [
                'employee_code' => $employee->employee_code,
                'status' => $employee->status,
            ]);
        });

        return redirect()->route('admin.employees.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function toggleStatus(Employee $employee): RedirectResponse
    {
        $newStatus = ($employee->status === 'active') ? 'inactive' : 'active';
        $employee->update(['status' => $newStatus]);

        AuditLogService::log('EMPLOYEE_STATUS_CHANGED', 'employee', $employee->id, [
            'employee_code' => $employee->employee_code,
            'status'        => $newStatus,
            'name'          => $employee->full_name,
        ]);

        $statusMsg = ($newStatus === 'active') ? 'diaktifkan kembali' : 'dinonaktifkan';

        return redirect()->route('admin.employees.index')
            ->with('success', "Karyawan {$employee->full_name} berhasil {$statusMsg}.");
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $name = $employee->full_name;
        $code = $employee->employee_code;

        DB::transaction(function () use ($employee) {
            // Hapus user akun terkait
            $employee->user()->delete();
            // Hapus employee (beserta relasi via cascade di migration)
            $employee->delete();
        });

        AuditLogService::log('EMPLOYEE_DELETED', 'employee', null, [
            'employee_code' => $code,
            'name'          => $name,
        ]);

        return redirect()->route('admin.employees.index')
            ->with('success', "Karyawan {$name} ({$code}) berhasil dihapus secara permanen.");
    }
}
