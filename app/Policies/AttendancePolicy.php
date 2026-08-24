<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    /**
     * Determine whether the user can view any attendance.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the specific attendance.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isEmployee() && $user->employee && $user->employee->id === $attendance->employee_id;
    }

    /**
     * Determine whether the user can view/download the attendance photo.
     */
    public function viewPhoto(User $user, Attendance $attendance): bool
    {
        return $this->view($user, $attendance);
    }
}
