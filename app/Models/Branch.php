<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Branch extends Model
{
    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'latitude',
        'longitude',
        'radius_meter',
        'timezone',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'radius_meter' => 'integer',
        ];
    }

    /**
     * Get attendance settings for this branch.
     */
    public function attendanceSetting(): HasOne
    {
        return $this->hasOne(AttendanceSetting::class);
    }

    /**
     * Get working days for this branch.
     */
    public function workingDays(): HasMany
    {
        return $this->hasMany(WorkingDay::class);
    }

    /**
     * Get employees assigned to this branch.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get attendances recorded at this branch.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Scope to only active branches.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
