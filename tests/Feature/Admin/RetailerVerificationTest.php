<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RetailerVerificationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_customer_registration_succeeds_without_foto_toko(): void
    {
        $response = $this->post('/register', [
            'name' => 'General Customer',
            'email' => 'customer-test@example.test',
            'nomor_telepon' => '081234567891',
            'tipe_user' => 'end_customer',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/tokodashboard');

        $user = User::where('email', 'customer-test@example.test')->firstOrFail();
        $this->assertSame('end_customer', $user->tipe_user);
        $this->assertSame('approved', $user->status_verifikasi);
        $this->assertNull($user->foto_toko);
    }

    public function test_retailer_registration_requires_foto_toko(): void
    {
        $response = $this->post('/register', [
            'name' => 'Retailer Customer',
            'email' => 'retailer-test@example.test',
            'nomor_telepon' => '081234567892',
            'tipe_user' => 'retailer',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['foto_toko']);
        $this->assertDatabaseMissing('users', ['email' => 'retailer-test@example.test']);
    }

    public function test_retailer_registration_succeeds_with_foto_toko_and_sets_pending(): void
    {
        Storage::fake('public');

        $response = $this->post('/register', [
            'name' => 'Retailer Valid',
            'email' => 'retailer-valid@example.test',
            'nomor_telepon' => '081234567893',
            'tipe_user' => 'retailer',
            'foto_toko' => UploadedFile::fake()->createWithContent(
                'store.jpg',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO7+Q1kAAAAASUVORK5CYII=')
            ),
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/tokodashboard');

        $user = User::where('email', 'retailer-valid@example.test')->firstOrFail();
        $this->assertSame('retailer', $user->tipe_user);
        $this->assertSame('pending', $user->status_verifikasi);
        $this->assertNotEmpty($user->foto_toko);
        $this->assertTrue(Storage::disk('public')->exists($user->foto_toko));
    }

    public function test_user_model_guards_tipe_user_and_status_verifikasi_from_mass_assignment(): void
    {
        $user = User::create([
            'f_name' => 'Guard Tester',
            'email' => 'guard-tester@example.test',
            'nomor_telepon' => '081234567894',
            'username' => 'guardtester',
            'password' => 'password',
            'user' => 'User',
            'tipe_user' => 'retailer',
            'status_verifikasi' => 'approved',
        ]);

        $user->refresh();

        // Assert mass assignment fields are set to defaults rather than mass-assigned values
        $this->assertSame('end_customer', $user->tipe_user);
        $this->assertSame('pending', $user->status_verifikasi);
    }

    public function test_ceo_can_approve_retailer_and_transition_orders(): void
    {
        $admin = $this->createAdminUser();
        $retailer = $this->createMasrosterUser([
            'tipe_user' => 'retailer',
            'status_verifikasi' => 'pending',
        ]);

        // Create a draft transaction for the retailer
        $transaction = Transaksi::create([
            'IdTransaksi' => 'TX9999',
            'id_admin' => 0,
            'id_customer' => $retailer->id,
            'Bayar' => 0,
            'GrandTotal' => 150000,
            'tglTransaksi' => now(),
            'StatusPembayaran' => 'Belum Lunas',
            'workflow_status' => 'Draft',
        ]);

        $response = $this->actingAs($admin)->post("/admin/users/{$retailer->id}/approve-retailer");

        $response->assertRedirect(route('allusers'));
        $response->assertSessionHas('message');

        $retailer->refresh();
        $this->assertSame('approved', $retailer->status_verifikasi);

        $transaction->refresh();
        $this->assertSame('Menunggu Pembayaran', $transaction->workflow_status);
    }

    public function test_ceo_can_reject_retailer_and_recalculate_orders_to_default_price(): void
    {
        $admin = $this->createAdminUser();
        $retailer = $this->createMasrosterUser([
            'tipe_user' => 'retailer',
            'status_verifikasi' => 'pending',
        ]);

        // Setup product catalog and sizes
        $product = $this->createMasrosterProduct([
            'sku' => 'MAS999',
            'stock' => 100,
        ]);
        $size = DB::table('size')->first();
        if (!$size) {
            DB::table('size')->insert([
                'nama' => 'Standard',
                'panjang' => 20,
                'lebar' => 20,
            ]);
            $size = DB::table('size')->first();
        }

        // Set retail default pricing (id_user = 0) and standard product_size price
        DB::table('detail_harga')->insert([
            'id_roster' => $product->sku,
            'produk_id' => $product->id,
            'id_user' => 0,
            'id_ukuran' => $size->id_ukuran,
            'harga' => 50000, // default retail price
        ]);

        DB::table('produk_size')->insert([
            'IdRoster' => $product->sku,
            'produk_id' => $product->id,
            'id_ukuran' => $size->id_ukuran,
            'harga' => 60000, // variant baseline price if no default
        ]);

        // Create a draft transaction for the retailer with higher negotiable pricing
        $transaction = Transaksi::create([
            'IdTransaksi' => 'TX8888',
            'id_admin' => 0,
            'id_customer' => $retailer->id,
            'Bayar' => 0,
            'GrandTotal' => 300000, // custom price 100000 * 3
            'tglTransaksi' => now(),
            'StatusPembayaran' => 'Belum Lunas',
            'workflow_status' => 'Draft',
            'ongkir' => 15000,
        ]);

        DetailTransaksi::create([
            'IdTransaksi' => $transaction->IdTransaksi,
            'IdRoster' => $product->sku,
            'produk_id' => $product->id,
            'id_ukuran' => $size->id_ukuran,
            'harga_satuan' => 100000,
            'QtyProduk' => 3,
            'SubTotal' => 300000,
        ]);

        $response = $this->actingAs($admin)->post("/admin/users/{$retailer->id}/reject-retailer");

        $response->assertRedirect(route('allusers'));
        $response->assertSessionHas('message');

        $retailer->refresh();
        $this->assertSame('end_customer', $retailer->tipe_user);
        $this->assertSame('rejected', $retailer->status_verifikasi);

        $transaction->refresh();
        // Check that GrandTotal was recalculated: (50000 * 3) + 15000 (ongkir) = 165000
        $this->assertEquals(165000, $transaction->GrandTotal);

        $detail = DetailTransaksi::where('IdTransaksi', $transaction->IdTransaksi)->firstOrFail();
        $this->assertEquals(50000, $detail->harga_satuan);
        $this->assertEquals(150000, $detail->SubTotal);
    }
}
