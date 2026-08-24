<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Flag apakah absensi ini terdeteksi mencurigakan (fake GPS)
            $table->boolean('is_suspicious')->default(false)->after('overall_status')->index();
            // JSON array dari alasan kecurigaan yang terdeteksi
            $table->json('suspicious_reasons')->nullable()->after('is_suspicious');
            // Raw GPS samples yang dikirim browser (JSON array of {lat, lng, accuracy, timestamp})
            $table->json('gps_samples')->nullable()->after('suspicious_reasons');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['is_suspicious', 'suspicious_reasons', 'gps_samples']);
        });
    }
};
