<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = (string) config('midtrans.server_key');
        Config::$isProduction = (bool) config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        Log::info('Midtrans configuration initialized', [
            'is_production' => Config::$isProduction,
            'server_key_present' => !empty(Config::$serverKey),
            'merchant_id_present' => !empty(config('midtrans.merchant_id')),
            'client_key_present' => !empty(config('midtrans.client_key')),
        ]);
    }

    public function createSnapToken(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['error' => 'Unauthorized.'], 401);
            }

            $cart = session('cart', []);
            $shippingCost = (int) session('shipping_cost', 0);

            if (empty($cart)) {
                return response()->json(['error' => 'Keranjang masih kosong.'], 422);
            }

            $orderId = session('midtrans_order_id');
            if (!$orderId) {
                $orderId = 'ORD-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);
                session(['midtrans_order_id' => $orderId]);
            }

            $itemDetails = [];
            $grossAmount = 0;

            foreach ($cart as $item) {
                $quantity = (int) ($item['quantity'] ?? 1);
                $price = (int) round($item['harga'] ?? 0);
                $subtotal = (int) round(($item['subtotal'] ?? ($price * $quantity)));

                $grossAmount += $subtotal;

                $itemDetails[] = [
                    'id' => (string) ($item['id'] ?? 'item-' . count($itemDetails)),
                    'price' => $price,
                    'quantity' => $quantity,
                    'name' => substr((string) ($item['nama'] ?? 'Produk'), 0, 50),
                ];
            }

            if ($shippingCost > 0) {
                $grossAmount += $shippingCost;
                $itemDetails[] = [
                    'id' => 'shipping',
                    'price' => $shippingCost,
                    'quantity' => 1,
                    'name' => 'Biaya Pengiriman',
                ];
            }

            $user = Auth::user();
            $customerName = $user->name
                ?? $user->username
                ?? session('customer_name')
                ?? 'Customer';

            $customerEmail = $user->email
                ?? session('customer_email')
                ?? 'customer@example.com';

            $customerPhone = $user->phone
                ?? $user->nomor_telepon
                ?? session('customer_phone')
                ?? null;

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $grossAmount,
                ],
                'item_details' => $itemDetails,
                'customer_details' => array_filter([
                    'first_name' => $customerName,
                    'email' => $customerEmail,
                    'phone' => $customerPhone,
                ]),
            ];

            $snapToken = Snap::getSnapToken($params);

            Log::info('Midtrans snap token created', [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
                'items' => count($itemDetails),
            ]);

            return response()->json(['snap_token' => $snapToken]);
        } catch (\Throwable $e) {
            Log::error('Midtrans error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user' => Auth::check() ? (Auth::user()->username ?? Auth::user()->name ?? 'authenticated') : 'not authenticated',
                'request_keys' => array_keys($request->all()),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handleNotification(Request $request)
    {
        // Only extract the fields we need from the webhook payload to avoid processing/storing unexpected keys
        $payload = $request->only([
            'order_id',
            'status_code',
            'gross_amount',
            'signature_key',
            'transaction_status',
            'fraud_status',
        ]);

        if (! $this->hasValidMidtransSignature($payload)) {
            Log::warning('Invalid Midtrans notification signature', [
                'order_id' => $payload['order_id'] ?? null,
                'status_code' => $payload['status_code'] ?? null,
            ]);

            return response()->json(['error' => 'Invalid notification signature.'], 403);
        }

        $orderId = (string) ($payload['order_id'] ?? '');
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');

        $transaction = Transaksi::where('IdTransaksi', $orderId)->first();
        if (! $transaction) {
            Log::warning('Midtrans notification for missing transaksi', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
            ]);

            return response()->json(['error' => 'Transaction not found.'], 404);
        }

        $reportedGrossAmount = (int) ($payload['gross_amount'] ?? 0);
        if ($reportedGrossAmount !== (int) $transaction->GrandTotal) {
            Log::warning('Midtrans notification gross amount mismatch', [
                'order_id' => $orderId,
                'reported_gross_amount' => $reportedGrossAmount,
                'expected_gross_amount' => (int) $transaction->GrandTotal,
            ]);

            return response()->json(['error' => 'Gross amount mismatch.'], 422);
        }

        $resolvedStatus = match ($transactionStatus) {
            'capture' => $fraudStatus === 'challenge' ? 'Belum Lunas' : 'Lunas',
            'settlement' => 'Lunas',
            'pending' => 'Belum Lunas',
            'cancel', 'deny', 'expire' => 'Belum Lunas',
            default => $transaction->StatusPembayaran ?? 'Belum Lunas',
        };

        $transaction->StatusPembayaran = $resolvedStatus;
        $transaction->tglUpdate = now();

        if ($resolvedStatus === 'Lunas') {
            $transaction->Bayar = $transaction->GrandTotal;
            $transaction->workflow_status = 'Paid';
        } else {
            $transaction->workflow_status = 'Draft';
        }

        $transaction->save();

        Log::info('Midtrans notification processed', [
            'order_id' => $orderId,
            'transaction_status' => $transactionStatus,
            'fraud_status' => $fraudStatus,
            'resolved_payment_status' => $resolvedStatus,
        ]);

        return response()->json(['status' => 'success']);
    }

    private function hasValidMidtransSignature(array $payload): bool
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');

        if ($orderId === '' || $statusCode === '' || $grossAmount === '' || $signatureKey === '') {
            return false;
        }

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . (string) config('midtrans.server_key'));

        return hash_equals($expectedSignature, $signatureKey);
    }

    public function paymentSuccess()
    {
        return redirect()->route('review')->with('message', 'Pembayaran berhasil diproses.');
    }

    public function paymentError()
    {
        return redirect()->route('payment')->with('error', 'Pembayaran gagal diproses. Silakan coba lagi.');
    }

    public function payment()
    {
        $cart = session('cart', []);
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['harga'] * $item['quantity'];
        }
        $shippingCost = session('shipping_cost', 0);
        $grandTotal = $subtotal + $shippingCost;
        return view('toko.payment', compact('cart', 'subtotal', 'shippingCost', 'grandTotal'));
    }
}
