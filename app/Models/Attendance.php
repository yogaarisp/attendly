<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'branch_id',
        'attendance_date',
        'check_in_at',
        'check_out_at',
        'check_in_photo',
        'check_out_photo',
        'check_in_latitude',
        'check_in_longitude',
        'check_in_accuracy',
        'check_in_distance',
        'check_out_latitude',
        'check_out_longitude',
        'check_out_accuracy',
        'check_out_distance',
        'check_in_status',
        'check_out_status',
        'overall_status',
        'notes',
        'is_suspicious',
        'suspicious_reasons',
        'gps_samples',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date'     => 'date',
            'check_in_at'         => 'datetime',
            'check_out_at'        => 'datetime',
            'check_in_latitude'   => 'decimal:8',
            'check_in_longitude'  => 'decimal:8',
            'check_in_accuracy'   => 'decimal:2',
            'check_in_distance'   => 'decimal:2',
            'check_out_latitude'  => 'decimal:8',
            'check_out_longitude' => 'decimal:8',
            'check_out_accuracy'  => 'decimal:2',
            'check_out_distance'  => 'decimal:2',
            'is_suspicious'       => 'boolean',
            'suspicious_reasons'  => 'array',
            'gps_samples'         => 'array',
        ];
    }

    /**
     * Get the employee who owns this attendance.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the branch for this attendance.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Check if check-in has been done.
     */
    public function hasCheckedIn(): bool
    {
        return $this->check_in_at !== null;
    }

    /**
     * Check if check-out has been done.
     */
    public function hasCheckedOut(): bool
    {
        return $this->check_out_at !== null;
    }

    /**
     * Get formatted check-in status label.
     */
    public function getCheckInStatusLabelAttribute(): string
    {
        return match ($this->check_in_status) {
            'on_time' => 'Tepat Waktu',
            'late' => 'Terlambat',
            'rejected' => 'Ditolak',
            default => '-',
        };
    }

    /**
     * Get formatted overall status label.
     */
    public function getOverallStatusLabelAttribute(): string
    {
        return match ($this->overall_status) {
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'incomplete' => 'Belum Selesai',
            'outside_area' => 'Di Luar Area',
            'rejected' => 'Ditolak',
            default => '-',
        };
    }
}
