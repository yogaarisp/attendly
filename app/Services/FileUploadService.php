<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Store an attendance selfie photo (either UploadedFile or Base64 Data URL).
     *
     * @throws \InvalidArgumentException
     */
    public function storeAttendancePhoto(UploadedFile|string $photoData): string
    {
        $datePath = date('Y/m/d');
        $uuid = Str::uuid()->toString();
        $filename = "{$uuid}.jpg";
        $relativePath = "attendance/{$datePath}/{$filename}";

        if ($photoData instanceof UploadedFile) {
            // Validate MIME type
            $mime = $photoData->getMimeType();
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
                throw new \InvalidArgumentException('Format foto tidak valid. Gunakan JPEG, PNG, atau WebP.');
            }

            if ($photoData->getSize() > 5 * 1024 * 1024) {
                throw new \InvalidArgumentException('Ukuran foto terlalu besar. Maksimal 5MB.');
            }

            $contents = file_get_contents($photoData->getRealPath());
        } elseif (is_string($photoData)) {
            // Base64 data URL
            if (preg_match('/^data:image\/(jpeg|png|webp);base64,/', $photoData, $matches)) {
                $base64 = substr($photoData, strpos($photoData, ',') + 1);
                $decoded = base64_decode($base64, true);

                if ($decoded === false) {
                    throw new \InvalidArgumentException('Data foto selfie rusak (invalid base64).');
                }

                if (strlen($decoded) > 5 * 1024 * 1024) {
                    throw new \InvalidArgumentException('Ukuran foto selfie melebihi batas 5MB.');
                }

                $contents = $decoded;
            } else {
                throw new \InvalidArgumentException('Format gambar base64 tidak valid.');
            }
        } else {
            throw new \InvalidArgumentException('Data foto tidak ditemukan.');
        }

        // Store to local private disk
        Storage::disk('local')->put($relativePath, $contents);

        return $relativePath;
    }

    /**
     * Retrieve photo binary data if user is authorized.
     */
    public function getPhotoStream(string $relativePath): ?string
    {
        if (!Storage::disk('local')->exists($relativePath)) {
            return null;
        }

        return Storage::disk('local')->get($relativePath);
    }
}
