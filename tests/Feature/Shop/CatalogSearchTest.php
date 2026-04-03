<?php

namespace Tests\Feature\Shop;

use Illuminate\Support\Collection;
use Tests\TestCase;

class CatalogSearchTest extends TestCase
{
    public function test_tokodashboard_search_filters_products_by_name(): void
    {
        $matched = $this->createMasrosterProduct([
            'IdRoster' => 'MASS001',
            'NamaProduk' => 'Roster Angin Premium',
        ]);

        $this->createMasrosterProduct([
            'IdRoster' => 'MASS002',
            'NamaProduk' => 'Roster Hujan Basic',
        ]);

        $response = $this->get('/tokodashboard?search=Angin');

        $response->assertOk();
        $response->assertViewHas('produk', function ($produk) use ($matched): bool {
            if (!$produk instanceof Collection) {
                return false;
            }

            return $produk->count() === 1 && $produk->first()->IdRoster === $matched->IdRoster;
        });
    }
}
