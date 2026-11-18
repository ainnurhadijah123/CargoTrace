<?php

namespace App\Services\External;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Service to fetch live weather conditions for port/checkpoint coordinates.
 * Integrates with Open-Meteo API using Laravel HTTP Client.
 * Adheres to UAS requirements: error handling, timeout, caching, and fallback.
 */
class WeatherService
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.weather.url', env('WEATHER_API_URL', 'https://api.open-meteo.com/v1/forecast'));
        $this->timeout = (int) env('WEATHER_API_TIMEOUT', 4);
    }

    /**
     * Get weather data for coordinates.
     *
     * @param float $latitude
     * @param float $longitude
     * @return array
     */
    public function getWeather(float $latitude, float $longitude): array
    {
        $cacheKey = "weather_{$latitude}_{$longitude}";

        return Cache::remember($cacheKey, 1800, function () use ($latitude, $longitude) {
            try {
                $response = Http::timeout($this->timeout)
                    ->get($this->baseUrl, [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'current_weather' => 'true',
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $current = $data['current_weather'] ?? [];

                    $weatherCode = $current['weathercode'] ?? 0;
                    $description = $this->interpretWeatherCode($weatherCode);

                    return [
                        'success' => true,
                        'source' => 'Live Open-Meteo API',
                        'temperature' => $current['temperature'] ?? 28,
                        'windspeed' => $current['windspeed'] ?? 10,
                        'weather_code' => $weatherCode,
                        'condition' => $description['condition'],
                        'icon' => $description['icon'],
                        'is_day' => $current['is_day'] ?? 1,
                        'time' => $current['time'] ?? now()->toIso8601String(),
                    ];
                }

                Log::warning("Weather API returned non-200: " . $response->status());
            } catch (Throwable $e) {
                Log::notice("Weather API unreachable or timed out: " . $e->getMessage());
            }

            // Graceful fallback when API times out or network is restricted
            return $this->fallbackWeather($latitude);
        });
    }

    protected function interpretWeatherCode(int $code): array
    {
        return match (true) {
            $code === 0 => ['condition' => 'Clear Sky', 'icon' => '☀️'],
            in_array($code, [1, 2, 3], true) => ['condition' => 'Partly Cloudy', 'icon' => '⛅'],
            in_array($code, [45, 48], true) => ['condition' => 'Foggy / Hazy', 'icon' => '🌫️'],
            in_array($code, [51, 53, 55, 61, 63, 65], true) => ['condition' => 'Rain Showers', 'icon' => '🌧️'],
            in_array($code, [71, 73, 75], true) => ['condition' => 'Snow', 'icon' => '❄️'],
            in_array($code, [95, 96, 99], true) => ['condition' => 'Thunderstorm', 'icon' => '⛈️'],
            default => ['condition' => 'Moderate Breeze', 'icon' => '🌤️'],
        };
    }

    protected function fallbackWeather(float $latitude): array
    {
        // Tropical vs Temperate estimation
        $isTropical = abs($latitude) < 23.5;
        $temp = $isTropical ? 29.5 : 18.0;

        return [
            'success' => true,
            'source' => 'Simulated / Resilient Fallback',
            'temperature' => $temp,
            'windspeed' => 12.5,
            'weather_code' => 1,
            'condition' => 'Fair Weather (Normal Conditions)',
            'icon' => '🌤️',
            'is_day' => 1,
            'time' => now()->toIso8601String(),
        ];
    }
}
