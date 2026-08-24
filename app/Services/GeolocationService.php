<?php

namespace App\Services;

use App\Models\Branch;

class GeolocationService
{
    public const EARTH_RADIUS_METERS = 6371000;

    /**
     * Maximum realistic human travel speed (m/s).
     * ~120 km/h — sudah sangat longgar untuk pengemudi kendaraan bermotor.
     */
    public const MAX_TRAVEL_SPEED_MPS = 33.33;

    /**
     * Calculate Haversine distance between two coordinates in meters.
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo   = deg2rad($lat2);
        $lonTo   = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return round($angle * self::EARTH_RADIUS_METERS, 2);
    }

    /**
     * Validate employee location against branch radius and GPS accuracy.
     *
     * @return array{valid: bool, distance: float, error?: string}
     */
    public function validateLocation(Branch $branch, float $latitude, float $longitude, float $accuracy): array
    {
        $setting    = $branch->attendanceSetting;
        $maxAccuracy = $setting ? $setting->minimum_gps_accuracy : 100;

        // 1. Check GPS accuracy threshold
        if ($accuracy > $maxAccuracy) {
            return [
                'valid'    => false,
                'distance' => 0,
                'error'    => "Akurasi GPS Anda ({$accuracy}m) melebihi batas maksimal ({$maxAccuracy}m). Mohon aktifkan mode GPS akurasi tinggi atau cari tempat terbuka.",
            ];
        }

        // 2. Calculate distance using Haversine
        $distance = $this->calculateDistance(
            $latitude,
            $longitude,
            (float) $branch->latitude,
            (float) $branch->longitude
        );

        // 3. Check allowed radius
        $allowedRadius = (int) $branch->radius_meter;
        if ($distance > $allowedRadius) {
            return [
                'valid'    => false,
                'distance' => $distance,
                'error'    => "Anda berada di luar area kantor ({$distance} meter dari kantor). Maksimal radius yang diizinkan adalah {$allowedRadius} meter.",
            ];
        }

        return [
            'valid'    => true,
            'distance' => $distance,
        ];
    }

    /**
     * Analisis multi-sample GPS untuk mendeteksi fake GPS.
     *
     * Menerima array sample dari browser:
     *   [['lat' => float, 'lng' => float, 'accuracy' => float, 'timestamp' => int (ms)], ...]
     *
     * @param  array  $samples   Minimal 1, idealnya 3 sample
     * @return array{suspicious: bool, reasons: string[]}
     */
    public function analyzeSamples(array $samples): array
    {
        $reasons = [];

        if (empty($samples)) {
            return ['suspicious' => false, 'reasons' => []];
        }

        $accuracies  = array_column($samples, 'accuracy');
        $latitudes   = array_column($samples, 'lat');
        $longitudes  = array_column($samples, 'lng');

        // ── Deteksi 1: Akurasi terlalu sempurna ──────────────────────────────
        // GPS asli nyaris tidak pernah memberikan akurasi < 3m di luar ruangan.
        // Fake GPS apps sering mengembalikan tepat 0, 1, atau 2 meter.
        foreach ($accuracies as $acc) {
            if ($acc < 3.0) {
                $reasons[] = "Akurasi GPS terlalu sempurna ({$acc}m) — GPS asli jarang di bawah 3m.";
                break;
            }
        }

        // ── Deteksi 2: Semua sample identik (koordinat beku / static mock) ──
        // Fake GPS yang di-pin ke satu titik akan selalu mengembalikan
        // koordinat yang persis sama atau sangat dekat (< 0.1 meter jarak).
        if (count($samples) >= 2) {
            $allSameLat = count(array_unique(array_map(fn($s) => round($s['lat'], 7), $samples))) === 1;
            $allSameLng = count(array_unique(array_map(fn($s) => round($s['lng'], 7), $samples))) === 1;

            if ($allSameLat && $allSameLng) {
                $reasons[] = 'Koordinat GPS identik di semua sample — kemungkinan lokasi dipalsukan (static mock).';
            }
        }

        // ── Deteksi 3: Variance akurasi nol (semua sample akurasi sama persis) ──
        // GPS nyata selalu sedikit berfluktuasi antar pembacaan.
        if (count($samples) >= 2) {
            $uniqueAccuracies = array_unique(array_map(fn($a) => round($a, 1), $accuracies));
            if (count($uniqueAccuracies) === 1) {
                $reasons[] = 'Nilai akurasi GPS tidak berubah sama sekali antar sample — perangkat nyata selalu sedikit berfluktuasi.';
            }
        }

        // ── Deteksi 4: Lompatan kecepatan antar sample tidak masuk akal ──────
        // Jika ada 2+ sample dengan timestamp, hitung kecepatan perpindahan.
        // Kecepatan > 120 km/h antar dua pembacaan GPS dalam hitungan detik
        // adalah sinyal kuat fake GPS (koordinat "lompat").
        if (count($samples) >= 2) {
            for ($i = 1; $i < count($samples); $i++) {
                $prev = $samples[$i - 1];
                $curr = $samples[$i];

                $timeDeltaMs = ($curr['timestamp'] ?? 0) - ($prev['timestamp'] ?? 0);
                if ($timeDeltaMs <= 0) {
                    continue;
                }

                $timeDeltaSec = $timeDeltaMs / 1000;
                $dist = $this->calculateDistance(
                    (float) $prev['lat'], (float) $prev['lng'],
                    (float) $curr['lat'], (float) $curr['lng']
                );
                $speed = $dist / $timeDeltaSec; // m/s

                if ($speed > self::MAX_TRAVEL_SPEED_MPS) {
                    $kmh = round($speed * 3.6, 1);
                    $reasons[] = "Perpindahan GPS antar sample terlalu cepat ({$kmh} km/h) — tidak mungkin secara fisik.";
                    break;
                }
            }
        }

        return [
            'suspicious' => !empty($reasons),
            'reasons'    => $reasons,
        ];
    }

    /**
     * Periksa apakah kecepatan perpindahan antara dua absensi masuk akal.
     * Digunakan untuk cross-check antar hari / absensi sebelumnya.
     *
     * @param  float   $prevLat       Latitude absensi sebelumnya
     * @param  float   $prevLng       Longitude absensi sebelumnya
     * @param  int     $prevTimestamp Unix timestamp absensi sebelumnya
     * @param  float   $currLat       Latitude absensi sekarang
     * @param  float   $currLng       Longitude absensi sekarang
     * @param  int     $currTimestamp Unix timestamp absensi sekarang
     * @return array{suspicious: bool, reason?: string}
     */
    public function checkSpeedPlausibility(
        float $prevLat,
        float $prevLng,
        int   $prevTimestamp,
        float $currLat,
        float $currLng,
        int   $currTimestamp
    ): array {
        $timeDeltaSec = $currTimestamp - $prevTimestamp;

        if ($timeDeltaSec <= 0) {
            return ['suspicious' => false];
        }

        $distance = $this->calculateDistance($prevLat, $prevLng, $currLat, $currLng);
        $speed    = $distance / $timeDeltaSec; // m/s

        if ($speed > self::MAX_TRAVEL_SPEED_MPS) {
            $kmh = round($speed * 3.6, 1);
            return [
                'suspicious' => true,
                'reason'     => "Perpindahan lokasi dari absensi sebelumnya tidak masuk akal ({$kmh} km/h — {$distance}m dalam {$timeDeltaSec} detik).",
            ];
        }

        return ['suspicious' => false];
    }
}
