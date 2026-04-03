<?php

namespace Tests\Feature\Admin;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    public function test_admin_can_create_update_and_delete_product(): void
    {
        Storage::fake('public');

        $admin = $this->createAdminUser([
            'email' => 'product-admin@example.test',
            'password' => 'password',
        ]);

        $payload = [
            'sizes' => [1],
            'harga_per_size' => [63000],
            'IdJenisBarang' => 1,
            'id_tipe' => 1,
            'id_motif' => 1,
            'stock' => 50,
            'Img' => UploadedFile::fake()->createWithContent('produk.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO7+Q1kAAAAASUVORK5CYII=')),
            'deskripsi' => 'Produk test CRUD',
        ];

        $this->actingAs($admin)
            ->post('/admin/store-produk', $payload)
            ->assertRedirect(route('allproduk'));

        $product = \App\Models\Produk::where('deskripsi', 'Produk test CRUD')->firstOrFail();

        $this->assertDatabaseHas('produk', [
            'IdRoster' => $product->IdRoster,
            'stock' => 50,
            'id_jenis' => 1,
        ]);

        $this->assertDatabaseHas('produk_size', [
            'IdRoster' => $product->IdRoster,
            'id_ukuran' => 1,
            'harga' => 63000,
        ]);

        $this->actingAs($admin)
            ->put('/admin/all-produk/' . $product->IdRoster . '/update', [
                'sizes' => [1],
                'harga_per_size' => [70000],
                'IdJenisBarang' => 2,
                'id_tipe' => 2,
                'id_motif' => 2,
                'stock' => 75,
                'deskripsi' => 'Produk test CRUD updated',
            ])
            ->assertRedirect(route('allproduk'));

        $this->assertDatabaseHas('produk', [
            'IdRoster' => $product->IdRoster,
            'stock' => 75,
            'id_jenis' => 2,
            'deskripsi' => 'Produk test CRUD updated',
        ]);

        $this->assertDatabaseHas('produk_size', [
            'IdRoster' => $product->IdRoster,
            'id_ukuran' => 1,
            'harga' => 70000,
        ]);

        $this->actingAs($admin)
            ->delete('/admin/all-produk/' . $product->IdRoster)
            ->assertRedirect(route('allproduk'));

        $this->assertDatabaseMissing('produk', [
            'IdRoster' => $product->IdRoster,
        ]);
    }
}