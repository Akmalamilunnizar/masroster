<?php

namespace Tests\Feature\Payment;

use App\Models\User;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Enums\WorkflowStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class DynamicSnapTokenAndHMACWebhookTest extends TestCase
{
    use DatabaseTransactions;

    private User $customer;
    private Produk $product;
    private string $transactionId = 'TX8888';

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = $this->createCustomerUser([
            'f_name' => 'Jit',
            'username' => 'jit_customer',
            'email' => 'jit_customer@example.com',
            'nomor_telepon' => '081234567895',
        ]);

        $this->product = $this->createMasrosterProduct([
            'IdRoster' => 'JIT1',
            'sku' => 'JIT1',
            'NamaProduk' => 'JIT Product',
            'stock' => 100,
        ]);

        config([
            'midtrans.server_key' => 'midtrans-test-key-12345',
            'midtrans.is_production' => false,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_dynamic_snap_token_retrieves_from_persisted_database_transaction(): void
    {
        // 1. Persist transaction and transaction details in DB
        $transaction = Transaksi::create([
            'IdTransaksi' => $this->transactionId,
            'id_admin' => 0,
            'id_customer' => $this->customer->id,
            'Bayar' => 0,
            'GrandTotal' => 125000,
            'tglTransaksi' => now(),
            'StatusPembayaran' => 'Belum Lunas',
            'workflow_status' => WorkflowStatus::MENUNGGU_PEMBAYARAN->value,
            'ongkir' => 25000,
        ]);

        DetailTransaksi::create([
            'IdTransaksi' => $transaction->IdTransaksi,
            'IdRoster' => $this->product->sku,
            'produk_id' => $this->product->id,
            'id_ukuran' => 1,
            'harga_satuan' => 50000,
            'QtyProduk' => 2,
            'SubTotal' => 100000,
        ]);

        // 2. Mock Snap API call and assert it receives the DB details instead of session cart
        $snap = Mockery::mock('alias:Midtrans\\Snap');
        $snap->shouldReceive('getSnapToken')
            ->once()
            ->withArgs(function (array $params): bool {
                if (($params['transaction_details']['order_id'] ?? null) !== 'TX8888') {
                    return false;
                }

                if (($params['transaction_details']['gross_amount'] ?? null) !== 125000) {
                    return false;
                }

                // Check item details includes shipping
                if (count($params['item_details'] ?? []) !== 2) {
                    return false;
                }

                return true;
            })
            ->andReturn('jit-test-token');

        // 3. Trigger POST request passing transaction_id without cart session
        $response = $this->actingAs($this->customer)->postJson('/payment/create-snap-token', [
            'transaction_id' => $this->transactionId,
        ]);

        $response->assertOk()->assertJson(['snap_token' => 'jit-test-token']);
    }

    public function test_webhook_hmac_sha512_verification_succeeds_with_valid_signature(): void
    {
        $transaction = new Transaksi([
            'IdTransaksi' => $this->transactionId,
            'id_admin' => 0,
            'id_customer' => $this->customer->id,
            'Bayar' => 0,
            'GrandTotal' => 100000,
            'tglTransaksi' => now(),
            'StatusPembayaran' => 'Belum Lunas',
        ]);
        $transaction->workflow_status = WorkflowStatus::MENUNGGU_PEMBAYARAN->value;
        $transaction->save();

        $orderId = $this->transactionId;
        $statusCode = '200';
        $grossAmount = '100000';
        $serverKey = 'midtrans-test-key-12345';
        $validSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $response = $this->postJson('/payment/notification', [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $validSignature,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
        ]);

        $response->assertOk();

        $transaction->refresh();
        $this->assertSame('Lunas', $transaction->StatusPembayaran);
        $this->assertEquals(100000, $transaction->Bayar);
        $this->assertSame(WorkflowStatus::PAID->value, $transaction->workflow_status);
    }

    public function test_webhook_hmac_sha512_verification_fails_with_invalid_signature(): void
    {
        $transaction = new Transaksi([
            'IdTransaksi' => $this->transactionId,
            'id_admin' => 0,
            'id_customer' => $this->customer->id,
            'Bayar' => 0,
            'GrandTotal' => 100000,
            'tglTransaksi' => now(),
            'StatusPembayaran' => 'Belum Lunas',
        ]);
        $transaction->workflow_status = WorkflowStatus::MENUNGGU_PEMBAYARAN->value;
        $transaction->save();

        $response = $this->postJson('/payment/notification', [
            'order_id' => $this->transactionId,
            'status_code' => '200',
            'gross_amount' => '100000',
            'signature_key' => 'invalid-signature-key-value',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
        ]);

        $response->assertStatus(403);

        $transaction->refresh();
        $this->assertSame('Belum Lunas', $transaction->StatusPembayaran);
        $this->assertSame(WorkflowStatus::MENUNGGU_PEMBAYARAN->value, $transaction->workflow_status);
    }
}
