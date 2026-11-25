<?php

namespace App\Services\Presensi;

use App\Models\PengaturanLembaga;

class GpsValidationService
{
    /**
     * Hitung jarak dalam meter menggunakan rumus Haversine.
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meter

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }

    /**
     * Validasi koordinat GPS pengguna terhadap radius lembaga.
     *
     * @return array{valid: bool, distance: int|null, message: string|null}
     */
    public function validateRadius(?float $latitude, ?float $longitude): array
    {
        $lembaga = PengaturanLembaga::first();

        if (!$lembaga || !$lembaga->latitude || !$lembaga->longitude) {
            return ['valid' => true, 'distance' => null, 'message' => null];
        }

        if ($latitude === null || $longitude === null) {
            return [
                'valid' => false,
                'distance' => null,
                'message' => 'Lokasi GPS wajib diaktifkan untuk melakukan presensi.',
            ];
        }

        $distance = (int) round($this->calculateDistance(
            (float) $lembaga->latitude,
            (float) $lembaga->longitude,
            $latitude,
            $longitude
        ));

        $radiusMax = (int) ($lembaga->radius_meter ?? 100);

        if ($distance > $radiusMax) {
            return [
                'valid' => false,
                'distance' => $distance,
                'message' => "Anda berada di luar jangkauan radius sekolah ({$distance}m dari batas maksimal {$radiusMax}m).",
            ];
        }

        return ['valid' => true, 'distance' => $distance, 'message' => null];
    }
}
