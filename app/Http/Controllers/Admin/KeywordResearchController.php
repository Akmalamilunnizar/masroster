<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KeywordResearchLog;
use App\Models\KeywordMetric;
use App\Services\GoogleAutocompleteService;
use App\Services\DataForSeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KeywordResearchController extends Controller
{
    protected GoogleAutocompleteService $googleService;
    protected DataForSeoService $seoService;

    public function __construct(GoogleAutocompleteService $googleService, DataForSeoService $seoService)
    {
        $this->middleware(['auth', 'role:admin']);
        $this->googleService = $googleService;
        $this->seoService = $seoService;
    }

    /**
     * Display the keyword research tool home page.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        return view('admin.keywords.index');
    }

    /**
     * Perform AJAX search / keyword research.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request, GoogleAutocompleteService $autocomplete, DataForSeoService $metricsService)
    {
        $request->validate([
            'query' => 'required|string',
            'fetch_metrics' => 'boolean' // New optional flag
        ]);
        
        $baseQuery = strtolower(trim($request->input('query')));
        $fetchMetrics = $request->input('fetch_metrics', false);

        // Check cache in keyword_research_logs for last 14 days
        $cachedLog = KeywordResearchLog::where('base_query', $baseQuery)
            ->where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subDays(14))
            ->first();

        if ($cachedLog) {
            $query = KeywordMetric::where('keyword_research_log_id', $cachedLog->id);
            if ($fetchMetrics) {
                $query->orderBy('search_volume', 'desc');
            } else {
                $query->orderBy('id', 'asc');
            }

            return response()->json([
                'success' => true,
                'source' => 'cache',
                'with_metrics' => $fetchMetrics,
                'data' => $query->get()
            ]);
        }

        $log = KeywordResearchLog::create([
            'base_query' => $baseQuery,
            'status' => 'processing'
        ]);

        // 1. Get Free Keywords (Always runs)
        $expandedKeywords = $autocomplete->expandKeywords($baseQuery);
        
        $metricsData = [];

        // 2. Fetch Exact Numbers ONLY if requested
        if ($fetchMetrics && count($expandedKeywords) > 0) {
            // Limit to top 100 to protect your API budget
            $targetKeywords = array_slice($expandedKeywords, 0, 100);
            $metricsData = $metricsService->getMetrics($targetKeywords);
        }

        // 3. Save to Database
        foreach ($expandedKeywords as $index => $keyword) {
            // Check if we pulled real metrics for this specific keyword
            $metric = collect($metricsData)->firstWhere('keyword', $keyword);

            KeywordMetric::create([
                'keyword_research_log_id' => $log->id,
                'keyword' => $keyword,
                'search_volume' => $metric['search_volume'] ?? null, // Null if metrics are off
                'cpc' => $metric['cpc'] ?? null,
                'competition' => $metric['competition'] ?? null,
            ]);
        }

        $log->update(['status' => 'completed']);

        // 4. Return sorted data (Sort by ID/Google Rank if no volume, otherwise by Volume)
        $query = KeywordMetric::where('keyword_research_log_id', $log->id);
        
        if ($fetchMetrics) {
            $query->orderBy('search_volume', 'desc');
        } else {
            $query->orderBy('id', 'asc');
        }

        return response()->json([
            'success' => true,
            'source' => 'api',
            'with_metrics' => $fetchMetrics,
            'data' => $query->get()
        ]);
    }

    /**
     * Provide real-time autocomplete suggestions with metric volumes.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function autocomplete(Request $request)
    {
        $query = $request->query('query');
        if (empty($query) || strlen(trim($query)) < 2) {
            return response()->json([]);
        }

        try {
            // Fetch suggestions from Google Autocomplete Service
            $suggestions = $this->googleService->getSuggestions($query);

            if (empty($suggestions)) {
                return response()->json([]);
            }

            // Cap at 10 items for dropdown performance
            $suggestions = array_slice($suggestions, 0, 10);

            // Fetch metrics from DataForSeo/Mock service
            $metrics = $this->seoService->getMetrics($suggestions);

            return response()->json($metrics);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }
}
