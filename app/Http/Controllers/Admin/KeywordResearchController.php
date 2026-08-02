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
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2|max:255',
        ]);

        $baseQuery = strtolower(trim((string) $request->input('query')));

        // Check cache in keyword_research_logs for last 14 days
        $cachedLog = KeywordResearchLog::where('base_query', $baseQuery)
            ->where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subDays(14))
            ->with('metrics')
            ->first();

        if ($cachedLog) {
            return response()->json([
                'success' => true,
                'source' => 'cache',
                'data' => $cachedLog->metrics,
            ]);
        }

        // Cache miss: initiate query log
        $log = KeywordResearchLog::create([
            'base_query' => $baseQuery,
            'status' => 'processing',
        ]);

        try {
            // Get Google suggestions
            $suggestions = $this->googleService->getSuggestions($baseQuery);

            if (empty($suggestions)) {
                // At least analyze the base query itself if no suggestions
                $suggestions = [$baseQuery];
            }

            // Get DataForSeo/Mock metrics
            $metricsData = $this->seoService->getMetrics($suggestions);

            // Save metrics to DB using transaction
            DB::transaction(function () use ($log, $metricsData) {
                foreach ($metricsData as $item) {
                    KeywordMetric::create([
                        'keyword_research_log_id' => $log->id,
                        'keyword' => $item['keyword'],
                        'search_volume' => $item['search_volume'],
                        'cpc' => $item['cpc'],
                        'competition' => $item['competition'],
                        'monthly_trend' => $item['monthly_trend'],
                    ]);
                }

                $log->status = 'completed';
                $log->save();
            });

            // Reload metrics from DB to ensure structure consistency
            $metrics = $log->metrics()->get();

            return response()->json([
                'success' => true,
                'source' => 'api',
                'data' => $metrics,
            ]);

        } catch (\Exception $e) {
            $log->status = 'failed';
            $log->save();

            return response()->json([
                'success' => false,
                'message' => 'Keyword research failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
