<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = Auth::user();
        $employee = $user->employee()->with(['department', 'position', 'branch.attendanceSetting'])->first();

        return view('employee.profile.show', compact('user', 'employee'));
    }
}
