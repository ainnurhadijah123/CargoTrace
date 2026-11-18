<?php

namespace App\Services\External;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Service to geocode location queries to geographic coordinates (lat/lng).
 * Integrates with OpenStreetMap Nominatim Geocoding API.
 */
class GeocodingService
{
    protected string $baseUrl;
    protected int $timeout;

    // Fallback dictionary of common ports/hubs
    protected array $fallbackLocations = [
        'singapore' => ['lat' => 1.2644, 'lng' => 103.8403, 'country' => 'Singapore'],
        'jakarta' => ['lat' => -6.1018, 'lng' => 106.8837, 'country' => 'Indonesia'],
        'medan' => ['lat' => 3.7854, 'lng' => 98.6836, 'country' => 'Indonesia'],
        'rotterdam' => ['lat' => 51.9054, 'lng' => 4.4666, 'country' => 'Netherlands'],
        'hamburg' => ['lat' => 53.5353, 'lng' => 9.9872, 'country' => 'Germany'],
        'los angeles' => ['lat' => 33.7432, 'lng' => -118.2673, 'country' => 'United States'],
        'shanghai' => ['lat' => 31.2304, 'lng' => 121.4737, 'country' => 'China'],
        'sydney' => ['lat' => -33.8688, 'lng' => 151.2093, 'country' => 'Australia'],
    ];

    public function __construct()
    {
        $this->baseUrl = config('services.geocoding.url', env('GEOCODING_API_URL', 'https://nominatim.openstreetmap.org/search'));
        $this->timeout = (int) env('GEOCODING_API_TIMEOUT', 4);
    }

    /**
     * Search coordinates for a place name or city.
     *
     * @param string $query
     * @return array|null
     */
    public function search(string $query): ?array
    {
        $normalized = strtolower(trim($query));
        $cacheKey = "geo_" . md5($normalized);

        return Cache::remember($cacheKey, 86400, function () use ($normalized, $query) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'CargoTrace-Platform/1.0 (academic-uas-web2)',
                ])->timeout($this->timeout)->get($this->baseUrl, [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 1,
                ]);

                if ($response->successful() && !empty($response->json())) {
                    $item = $response->json()[0];
                    return [
                        'latitude' => (float) $item['lat'],
                        'longitude' => (float) $item['lon'],
                        'display_name' => $item['display_name'],
                        'source' => 'Nominatim OSM',
                    ];
                }
            } catch (Throwable $e) {
                Log::notice("Geocoding API unavailable: " . $e->getMessage());
            }

            // Fallback lookup
            foreach ($this->fallbackLocations as $key => $coords) {
                if (str_contains($normalized, $key)) {
                    return [
                        'latitude' => $coords['lat'],
                        'longitude' => $coords['lng'],
                        'display_name' => ucwords($key) . ", " . $coords['country'],
                        'source' => 'Local Geo Dictionary Fallback',
                    ];
                }
            }

            return null;
        });
    }
}
