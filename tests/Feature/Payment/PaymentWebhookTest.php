<?php

namespace Tests\Feature\Payment;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    public function test_payment_notification_rejects_invalid_signature(): void
    {
        Config::set('midtrans.server_key', 'test-server-key');

        $transaction = $this->createMasrosterTransaction([
            'IdTransaksi' => 'TX300001',
            'GrandTotal' => 126000,
            'Bayar' => 0,
            'StatusPembayaran' => 'Belum Lunas',
            'StatusPesanan' => 'Menunggu Konfirmasi',
        ]);

        $response = $this->postJson('/payment/notification', [
            'order_id' => $transaction->IdTransaksi,
            'status_code' => '200',
            'gross_amount' => '126000',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'signature_key' => 'invalid-signature',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('transaksi', [
            'IdTransaksi' => $transaction->IdTransaksi,
            'StatusPembayaran' => 'Belum Lunas',
            'workflow_status' => 'Draft',
        ]);
    }

    public function test_payment_notification_marks_transaction_paid_with_valid_signature(): void
    {
        Config::set('midtrans.server_key', 'test-server-key');

        $transaction = $this->createMasrosterTransaction([
            'IdTransaksi' => 'TX300002',
            'GrandTotal' => 126000,
            'Bayar' => 0,
            'StatusPembayaran' => 'Belum Lunas',
            'StatusPesanan' => 'Menunggu Konfirmasi',
        ]);

        $signatureKey = hash('sha512', $transaction->IdTransaksi . '200' . '126000' . 'test-server-key');

        $response = $this->postJson('/payment/notification', [
            'order_id' => $transaction->IdTransaksi,
            'status_code' => '200',
            'gross_amount' => '126000',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'signature_key' => $signatureKey,
        ]);

        $response->assertOk()->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('transaksi', [
            'IdTransaksi' => $transaction->IdTransaksi,
            'StatusPembayaran' => 'Lunas',
            'Bayar' => 126000,
            'workflow_status' => 'Paid',
        ]);
    }

    public function test_payment_notification_rejects_tampered_gross_amount(): void
    {
        Config::set('midtrans.server_key', 'test-server-key');

        $transaction = $this->createMasrosterTransaction([
            'IdTransaksi' => 'TX300003',
            'GrandTotal' => 126000,
            'Bayar' => 0,
            'StatusPembayaran' => 'Belum Lunas',
            'StatusPesanan' => 'Menunggu Konfirmasi',
        ]);

        $signatureKey = hash('sha512', $transaction->IdTransaksi . '200' . '999999' . 'test-server-key');

        $response = $this->postJson('/payment/notification', [
            'order_id' => $transaction->IdTransaksi,
            'status_code' => '200',
            'gross_amount' => '999999',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'signature_key' => $signatureKey,
        ]);

        $response->assertStatus(422)->assertJsonPath('error', 'Gross amount mismatch.');

        $this->assertDatabaseHas('transaksi', [
            'IdTransaksi' => $transaction->IdTransaksi,
            'StatusPembayaran' => 'Belum Lunas',
            'workflow_status' => 'Draft',
        ]);
    }
}
