<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use Midtrans\Config;
// use Midtrans\Snap;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Temporarily disable Midtrans configuration for testing
        /*
        // Configure Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Log configuration for debugging
        Log::info('Midtrans Configuration:', [
            'server_key_length' => strlen(Config::$serverKey),
            'is_production' => Config::$isProduction,
            'merchant_id' => config('midtrans.merchant_id'),
            'client_key' => config('midtrans.client_key'),
            'snap_url' => config('midtrans.snap_url')
        ]);
        */
    }

    public function createSnapToken(Request $request)
    {
        // Temporarily disabled for testing
        return response()->json(['error' => 'Payment system temporarily disabled'], 503);
    }
                    'code' => $e->getCode(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
            // Store order ID in session for later use
            session(['midtrans_order_id' => $orderId]);
            
            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            Log::error('Midtrans error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user' => auth()->user() ? auth()->user()->username : 'not authenticated',
                'request' => $request->all()
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
