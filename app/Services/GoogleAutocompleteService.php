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

        try {
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
        } catch (\Exception $e) {
            $responses = [];
        }

        $keywords = [];
        foreach ($responses as $response) {
            if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
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
    public function expandKeywords(string $seedQuery, string $lang = 'id'): array
    {
        // Remove the underscore wildcard and trim extra spaces
        $seedQuery = trim(str_replace('_', '', $seedQuery));
        if (empty($seedQuery)) {
            return [];
        }
        
        // Modifiers: Letters a-z, numbers 0-9
        $modifiers = array_merge([''], range('a', 'z'), range('0', '9'));
        
        $queries = [];
        foreach ($modifiers as $modifier) {
            $queries[] = trim("{$seedQuery} {$modifier}");
        }

        try {
            $responses = Http::pool(function ($pool) use ($queries, $lang) {
                $requests = [];
                foreach ($queries as $query) {
                    $requests[] = $pool->as($query)->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
                    ])->get('https://suggestqueries.google.com/complete/search', [
                        'client' => 'chrome',
                        'q' => $query,
                        'hl' => $lang,
                    ]);
                }
                return $requests;
            });
        } catch (\Exception $e) {
            $responses = [];
        }

        $suggestions = [];
        foreach ($responses as $response) {
            if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                $data = $response->json();
                
                // Google returns array format: ["search term", ["suggestion1", "suggestion2"]]
                if (isset($data[1]) && is_array($data[1])) {
                    foreach ($data[1] as $item) {
                        // Only keep suggestions that actually contain our seed keyword
                        if (str_contains(strtolower($item), strtolower($seedQuery))) {
                            $suggestions[] = strtolower(trim((string) $item));
                        }
                    }
                }
            }
        }

        // Return unique, re-indexed array
        return array_values(array_unique($suggestions));
    }
}
