<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleAutocompleteService
{
    /**
     * Get autocomplete suggestions from Google.
     * Appends modifiers (a-z, 0-9) to the base query.
     *
     * @param string $query
     * @return array
     */
    public function getSuggestions(string $query): array
    {
        $query = trim($query);
        if (empty($query)) {
            return [];
        }

        $queries = [$query];
        // Generate queries with modifiers
        foreach (range('a', 'z') as $char) {
            $queries[] = $query . ' ' . $char;
        }
        foreach (range('0', '9') as $num) {
            $queries[] = $query . ' ' . $num;
        }

        // Hit Suggest API concurrently using Http::pool
        $responses = Http::pool(function ($pool) use ($queries) {
            $requests = [];
            foreach ($queries as $q) {
                $requests[] = $pool->as($q)->get('https://suggestqueries.google.com/complete/search', [
                    'client' => 'chrome',
                    'hl' => 'id',
                    'q' => $q,
                ]);
            }
            return $requests;
        });

        $keywords = [];
        foreach ($responses as $response) {
            if ($response->successful()) {
                $data = $response->json();
                // Google Autocomplete returns: [query, [suggestions], [descriptions], ...]
                if (isset($data[1]) && is_array($data[1])) {
                    foreach ($data[1] as $suggestion) {
                        $keywords[] = strtolower(trim((string) $suggestion));
                    }
                }
            }
        }

        return array_values(array_unique($keywords));
    }
}
