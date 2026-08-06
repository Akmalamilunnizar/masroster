<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\KeywordResearchLog;
use App\Models\KeywordMetric;
use App\Services\GoogleAutocompleteService;
use App\Services\DataForSeoService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Mockery;

class KeywordResearchTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Clean up logs to start fresh
        KeywordResearchLog::query()->delete();
    }

    public function test_guest_is_redirected_from_keyword_research(): void
    {
        $response = $this->get('/admin/keywords');
        $response->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_keyword_research(): void
    {
        $user = $this->createCustomerUser();
        $response = $this->actingAs($user)->get('/admin/keywords');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_keyword_research_page(): void
    {
        $admin = $this->createAdminUser();
        $response = $this->actingAs($admin)->get('/admin/keywords');
        $response->assertStatus(200);
        $response->assertViewIs('admin.keywords.index');
    }

    public function test_ajax_search_performs_fresh_lookup_and_caches_metrics(): void
    {
        $admin = $this->createAdminUser();

        // Mock GoogleAutocompleteService
        $googleMock = Mockery::mock(GoogleAutocompleteService::class);
        $googleMock->shouldReceive('expandKeywords')
            ->once()
            ->with('roster beton')
            ->andReturn(['roster beton minimalis', 'roster beton murah']);
        $this->app->instance(GoogleAutocompleteService::class, $googleMock);

        // Mock DataForSeoService
        $seoMock = Mockery::mock(DataForSeoService::class);
        $seoMock->shouldReceive('getMetrics')
            ->once()
            ->with(['roster beton minimalis', 'roster beton murah'])
            ->andReturn([
                [
                    'keyword' => 'roster beton minimalis',
                    'search_volume' => 500,
                    'cpc' => 0.45,
                    'competition' => 'high',
                    'monthly_trend' => []
                ],
                [
                    'keyword' => 'roster beton murah',
                    'search_volume' => 1200,
                    'cpc' => 0.20,
                    'competition' => 'medium',
                    'monthly_trend' => []
                ]
            ]);
        $this->app->instance(DataForSeoService::class, $seoMock);

        // Perform first request (cache miss)
        $response1 = $this->actingAs($admin)->postJson('/admin/keywords/search', [
            'query' => 'roster beton',
            'fetch_metrics' => true,
        ]);

        $response1->assertStatus(200);
        $response1->assertJsonPath('success', true);
        $response1->assertJsonPath('source', 'api');
        $response1->assertJsonCount(2, 'data');

        $this->assertDatabaseHas('keyword_research_logs', [
            'base_query' => 'roster beton',
            'status' => 'completed'
        ]);

        // Perform second request (cache hit)
        $response2 = $this->actingAs($admin)->postJson('/admin/keywords/search', [
            'query' => 'roster beton',
            'fetch_metrics' => true,
        ]);

        $response2->assertStatus(200);
        $response2->assertJsonPath('success', true);
        $response2->assertJsonPath('source', 'cache');
        $response2->assertJsonCount(2, 'data');
    }

    public function test_google_autocomplete_handles_connection_failures_gracefully(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://suggestqueries.google.com/*' => function ($request) {
                throw new \GuzzleHttp\Exception\ConnectException(
                    "Connection failed",
                    $request->toPsrRequest()
                );
            }
        ]);

        try {
            $service = new GoogleAutocompleteService();
            $suggestions = $service->getSuggestions('roster');

            $this->assertIsArray($suggestions);
            $this->assertEmpty($suggestions);
        } catch (\Throwable $e) {
            echo $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
            throw $e;
        }
    }

    public function test_guest_is_redirected_from_autocomplete(): void
    {
        $response = $this->get('/admin/keywords/autocomplete?query=roster');
        $response->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_autocomplete(): void
    {
        $user = $this->createCustomerUser();
        $response = $this->actingAs($user)->get('/admin/keywords/autocomplete?query=roster');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_autocomplete_and_fetch_suggestions(): void
    {
        $admin = $this->createAdminUser();

        // Mock GoogleAutocompleteService
        $googleMock = Mockery::mock(GoogleAutocompleteService::class);
        $googleMock->shouldReceive('getSuggestions')
            ->once()
            ->with('roster beton')
            ->andReturn(['roster beton 3d', 'roster beton minimalis']);
        $this->app->instance(GoogleAutocompleteService::class, $googleMock);

        // Mock DataForSeoService
        $seoMock = Mockery::mock(DataForSeoService::class);
        $seoMock->shouldReceive('getMetrics')
            ->once()
            ->with(['roster beton 3d', 'roster beton minimalis'])
            ->andReturn([
                [
                    'keyword' => 'roster beton 3d',
                    'search_volume' => 2000,
                    'cpc' => 0.15,
                    'competition' => 'low',
                    'monthly_trend' => []
                ],
                [
                    'keyword' => 'roster beton minimalis',
                    'search_volume' => 5000,
                    'cpc' => 0.35,
                    'competition' => 'high',
                    'monthly_trend' => []
                ]
            ]);
        $this->app->instance(DataForSeoService::class, $seoMock);

        $response = $this->actingAs($admin)->getJson('/admin/keywords/autocomplete?query=roster+beton');
        
        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonFragment([
            'keyword' => 'roster beton 3d',
            'search_volume' => 2000,
            'cpc' => 0.15,
            'competition' => 'low',
        ]);
    }
}

