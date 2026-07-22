<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\Log;
use Stevebauman\Location\Facades\Location;

class GeoLocationService
{
    /**
     * Lookup geo data for an IP address.
     *
     * Returns a normalised array or null on failure.
     *
     * @return array{country: string|null, country_code: string|null, region: string|null, city: string|null, latitude: float|null, longitude: float|null}|null
     */
    public function lookup(string $ipAddress): ?array
    {
        try {
            $position = Location::get($ipAddress);

            if (! $position) {
                return $this->nullResult();
            }

            return [
                'country'      => $position->countryName    ?? null,
                'country_code' => $position->countryCode    ?? null,
                'region'       => $position->regionName     ?? null,
                'city'         => $position->cityName       ?? null,
                'latitude'     => isset($position->latitude)  ? (float) $position->latitude  : null,
                'longitude'    => isset($position->longitude) ? (float) $position->longitude : null,
            ];
        } catch (\Throwable $e) {
            Log::warning('GeoLocationService lookup failed', [
                'ip'    => $ipAddress,
                'error' => $e->getMessage(),
            ]);

            return $this->nullResult();
        }
    }

    private function nullResult(): array
    {
        return [
            'country'      => null,
            'country_code' => null,
            'region'       => null,
            'city'         => null,
            'latitude'     => null,
            'longitude'    => null,
        ];
    }
}
