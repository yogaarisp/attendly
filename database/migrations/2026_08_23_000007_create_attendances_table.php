<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->date('attendance_date')->index();

            // Check-in data
            $table->timestamp('check_in_at')->nullable();
            $table->string('check_in_photo', 255)->nullable();
            $table->decimal('check_in_latitude', 10, 8)->nullable();
            $table->decimal('check_in_longitude', 11, 8)->nullable();
            $table->decimal('check_in_accuracy', 8, 2)->nullable();
            $table->decimal('check_in_distance', 10, 2)->nullable();

            // Check-out data
            $table->timestamp('check_out_at')->nullable();
            $table->string('check_out_photo', 255)->nullable();
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();
            $table->decimal('check_out_accuracy', 8, 2)->nullable();
            $table->decimal('check_out_distance', 10, 2)->nullable();

            // Status
            $table->enum('check_in_status', ['on_time', 'late', 'rejected'])->nullable();
            $table->enum('check_out_status', ['normal', 'early_leave', 'rejected'])->nullable();
            $table->enum('overall_status', ['present', 'late', 'incomplete', 'outside_area', 'rejected'])
                ->default('incomplete')->index();

            $table->text('notes')->nullable();
            $table->timestamps();

            // One attendance record per employee per day
            $table->unique(['employee_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
