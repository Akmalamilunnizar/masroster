<?php

namespace App\Services;

class DataForSeoService
{
    protected ?string $apiLogin;
    protected ?string $apiPassword;

    public function __construct()
    {
        $this->apiLogin = config('services.dataforseo.login') ?? env('DATAFORSEO_LOGIN');
        $this->apiPassword = config('services.dataforseo.password') ?? env('DATAFORSEO_PASSWORD');
    }

    /**
     * Get keyword metrics from DataForSeo or generate mock data if credentials are not configured.
     *
     * @param array $keywords
     * @return array
     */
    public function getMetrics(array $keywords): array
    {
        // Check if real credentials are set. If so, call real API; otherwise return mock data.
        if (! empty($this->apiLogin) && ! empty($this->apiPassword)) {
            return $this->fetchFromRealApi($keywords);
        }

        return $this->generateMockMetrics($keywords);
    }

    /**
     * Fetch keyword metrics from DataForSeo API.
     *
     * @param array $keywords
     * @return array
     */
    protected function fetchFromRealApi(array $keywords): array
    {
        // Placeholder for future real implementation
        // e.g., Http::withBasicAuth($this->apiLogin, $this->apiPassword)->post(...)
        return $this->generateMockMetrics($keywords);
    }

    /**
     * Generate mock metrics for development and testing.
     *
     * @param array $keywords
     * @return array
     */
    protected function generateMockMetrics(array $keywords): array
    {
        $metrics = [];
        $competitions = ['low', 'medium', 'high'];

        foreach ($keywords as $keyword) {
            // Generate deterministic mock data based on keyword length/chars
            $seed = crc32($keyword);
            srand($seed);

            $searchVolume = rand(10, 1000) * 10;
            $cpc = rand(1000, 50000) / 100; // in local currency (e.g., IDR or decimal USD)
            $competition = $competitions[rand(0, 2)];

            // Generate monthly trend
            $monthlyTrend = [];
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            foreach ($months as $month) {
                $monthlyTrend[] = [
                    'month' => $month,
                    'volume' => (int) ($searchVolume * (rand(80, 120) / 100)),
                ];
            }

            $metrics[] = [
                'keyword' => $keyword,
                'search_volume' => $searchVolume,
                'cpc' => $cpc,
                'competition' => $competition,
                'monthly_trend' => $monthlyTrend,
            ];
        }

        // Reset random seed
        srand();

        return $metrics;
    }
}
