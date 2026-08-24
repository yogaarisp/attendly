<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSetting extends Model
{
    protected $fillable = [
        'branch_id',
        'work_start_time',
        'work_end_time',
        'late_tolerance_minutes',
        'minimum_gps_accuracy',
        'attendance_enabled',
    ];

    protected function casts(): array
    {
        return [
            'late_tolerance_minutes' => 'integer',
            'minimum_gps_accuracy' => 'integer',
            'attendance_enabled' => 'boolean',
        ];
    }

    /**
     * Get the branch this setting belongs to.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
