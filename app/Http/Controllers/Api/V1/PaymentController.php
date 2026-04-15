<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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
                'request' => $request->all(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handleNotification(Request $request)
    {
        $payload = $request->all();
        Log::info('Midtrans notification:', $payload);

        $orderId = $payload['order_id'];
        $transactionStatus = $payload['transaction_status'];
        $fraudStatus = $payload['fraud_status'];

        // Handle the notification based on transaction status
        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                // TODO: Handle challenge payment
            } else if ($fraudStatus == 'accept') {
                // TODO: Handle successful payment
            }
        } else if ($transactionStatus == 'settlement') {
            // TODO: Handle settlement payment
        } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            // TODO: Handle failed payment
        } else if ($transactionStatus == 'pending') {
            // TODO: Handle pending payment
        }

        return response()->json(['status' => 'success']);
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
