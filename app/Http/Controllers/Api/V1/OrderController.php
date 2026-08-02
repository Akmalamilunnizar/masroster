<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DetailHarga;
use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Enums\WorkflowStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function confirmOrder(Request $request)
    {
        DB::beginTransaction();
        try {
            // Check if user is authenticated
            if (! Auth::check()) {
                Log::warning('User not authenticated');

                return response()->json([
                    'error' => 'Silakan login terlebih dahulu!',
                ], 401);
            }

            // Get authenticated user
            $user = Auth::user();
            if (! $user) {
                Log::error('User object is null after Auth::check()');

                return response()->json([
                    'error' => 'Terjadi kesalahan autentikasi',
                ], 401);
            }

            // Add debugging (log keys only to avoid sensitive data in logs)
            Log::info('Confirm Order Request Received', [
                'user' => $user->username,
                'request_keys' => $request->keys(),
            ]);

            // Get cart data from session
            $cart = session('cart');
            $cartCount = is_array($cart) ? count($cart) : ($cart ? collect($cart)->count() : 0);
            Log::info('Cart summary', ['count' => $cartCount]);

            if (! $cart || empty($cart)) {
                Log::warning('Cart is empty');

                return response()->json([
                    'error' => 'Keranjang kosong!',
                ], 400);
            }

            // Get or create customer
            $customer = Customer::firstOrCreate(
                ['id' => $user->id],
                [
                    'NamaCust' => $user->f_name,
                    'NoTelp' => $user->nomor_telepon ?? '',
                    'Email' => $user->email,
                    'Alamat' => $user->alamat ?? '',
                ]
            );
            Log::info('Customer created/retrieved', ['id' => $customer->id, 'name' => $customer->NamaCust ?? null]);

            // Generate a collision-resistant transaction ID within the existing 6-character schema.
            $transactionId = $this->generateTransactionId();
            Log::info('Generated transaction ID:', ['id' => $transactionId]);

            // Get selected address, but only if it belongs to the authenticated user.
            $selectedAddressId = session('selected_address_id'); // or from request
            $address = null;
            if ($selectedAddressId) {
                $address = \App\Models\Address::where('id', $selectedAddressId)
                    ->where('user_id', $user->id)
                    ->first();
            }

            if (! $address) {
                $address = \App\Models\Address::where('user_id', $user->id)
                    ->where('is_default', true)
                    ->first();
            }

            $addressId = $address ? $address->id : null;

            // Calculate total from the authoritative cart and shipping session data.
            $total = 0;
            $shippingCost = session('shipping_cost', 0);
            foreach ($cart as $item) {
                $quantity = (int) ($item['quantity'] ?? 0);
                $price = $this->resolveCartLinePrice($item, $user->id, $addressId);

                $total += $price * $quantity;
            }
            $total += $shippingCost;
            Log::info('Calculated total:', ['total' => $total]);

            // Determine workflow_status based on retailer verification & location-scoped pricing status
            $workflowStatus = WorkflowStatus::MENUNGGU_PEMBAYARAN->value;
            if ($user->tipe_user === 'retailer' && $user->status_verifikasi === 'pending') {
                $hasAllLocationPrices = true;
                foreach ($cart as $item) {
                    $productIdentifier = (string) ($item['id'] ?? '');
                    $sizeId = isset($item['ukuran']) ? (int) $item['ukuran'] : null;

                    $product = Produk::query()
                        ->where('IdRoster', $productIdentifier)
                        ->orWhere('sku', $productIdentifier)
                        ->first();

                    if ($product && $sizeId !== null && $addressId !== null) {
                        $priceForeignKey = Schema::hasColumn('detail_harga', 'produk_id') ? 'produk_id' : 'id_roster';
                        $priceIdentifier = $priceForeignKey === 'produk_id' ? $product->getKey() : $productIdentifier;

                        $exists = DetailHarga::where('id_user', $user->id)
                            ->where($priceForeignKey, $priceIdentifier)
                            ->where('id_ukuran', $sizeId)
                            ->where('address_id', $addressId)
                            ->exists();

                        if (! $exists) {
                            $hasAllLocationPrices = false;
                            break;
                        }
                    } else {
                        $hasAllLocationPrices = false;
                        break;
                    }
                }

                if (! $hasAllLocationPrices) {
                    $workflowStatus = WorkflowStatus::DRAFT->value;
                }
            }

            // Create transaction
            $transaction = new Transaksi;
            $transaction->IdTransaksi = $transactionId;
            $transaction->id_admin = 0;
            $transaction->id_customer = $user->id;
            $transaction->address_id = $addressId;
            $transaction->shipping_method = session('shipping_method');
            $transaction->delivery_method = session('delivery_method');
            $transaction->shipping_type = session('shipping_type');
            $transaction->ongkir = (int) $shippingCost;
            $transaction->notes = session('order_notes', null); // Add order notes

            $transaction->Bayar = 0;
            $transaction->StatusPembayaran = 'Belum Lunas';
            $transaction->workflow_status = $workflowStatus;

            $transaction->GrandTotal = $total;
            $transaction->tglTransaksi = now();
            $transaction->tglUpdate = now();
            $transaction->StatusPesanan = 'Menunggu Konfirmasi';
            $transaction->save();

            Log::info('Transaction created', ['id' => $transaction->IdTransaksi, 'grand_total' => $transaction->GrandTotal]);

            // Create transaction details
            foreach ($cart as $id => $details) {
                $linePrice = $this->resolveCartLinePrice($details, $user->id, $addressId);
                $quantity = (int) ($details['quantity'] ?? 0);

                $productIdentifier = (string) ($details['id'] ?? '');
                $product = Produk::query()
                    ->where('IdRoster', $productIdentifier)
                    ->orWhere('sku', $productIdentifier)
                    ->first();

                $detailData = [
                    'IdTransaksi' => $transactionId,
                    'IdRoster' => $details['id'],
                    'produk_id' => $product ? $product->id : null,
                    'id_ukuran' => isset($details['ukuran']) ? (int) $details['ukuran'] : null,
                    'harga_satuan' => $linePrice,
                    'QtyProduk' => $quantity,
                    'SubTotal' => $linePrice * $quantity,
                    'data_type' => $quantity > 100 ? 'Borongan' : 'Eceran',
                ];
                DetailTransaksi::create($detailData);
                Log::info('Transaction detail created', [
                    'id_roster' => $detailData['IdRoster'] ?? null,
                    'qty' => $detailData['QtyProduk'] ?? null,
                    'subtotal' => $detailData['SubTotal'] ?? null,
                ]);
            }

            // Clear cart and payment flags
            session()->forget(['cart', 'midtrans_paid', 'payment_method', 'shipping_cost', 'shipping_method', 'shipping_type', 'selected_address_id', 'order_notes']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dikonfirmasi!',
                'transaction_id' => $transactionId,
                'redirect' => route('tokodashboard'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in confirmOrder: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user' => Auth::user() ? Auth::user()->username : 'not authenticated',
                'request_keys' => $request->keys(),
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    private function resolveCartLinePrice(array $details, int $userId, ?int $addressId = null): int
    {
        $productIdentifier = (string) ($details['id'] ?? '');
        $sizeId = isset($details['ukuran']) ? (int) $details['ukuran'] : null;

        if ($productIdentifier === '' || $sizeId === null) {
            return (int) round($details['harga'] ?? 0);
        }

        $product = Produk::query()
            ->where('IdRoster', $productIdentifier)
            ->orWhere('sku', $productIdentifier)
            ->first();

        if (! $product) {
            return (int) round($details['harga'] ?? 0);
        }

        $priceForeignKey = Schema::hasColumn('detail_harga', 'produk_id') ? 'produk_id' : 'id_roster';
        $priceIdentifier = $priceForeignKey === 'produk_id' ? $product->getKey() : $productIdentifier;

        // 1. User specific + location specific (id_user = $userId, address_id = $addressId)
        if ($addressId !== null) {
            $price = DB::table('detail_harga')
                ->where($priceForeignKey, $priceIdentifier)
                ->where('id_user', $userId)
                ->where('id_ukuran', $sizeId)
                ->where('address_id', $addressId)
                ->value('harga');
            if ($price !== null) {
                return (int) $price;
            }
        }

        // 2. User specific + national default (id_user = $userId, address_id = null)
        $price = DB::table('detail_harga')
            ->where($priceForeignKey, $priceIdentifier)
            ->where('id_user', $userId)
            ->where('id_ukuran', $sizeId)
            ->whereNull('address_id')
            ->value('harga');
        if ($price !== null) {
            return (int) $price;
        }

        // 3. General default + location specific (id_user = 0, address_id = $addressId)
        if ($addressId !== null) {
            $price = DB::table('detail_harga')
                ->where($priceForeignKey, $priceIdentifier)
                ->where('id_user', 0)
                ->where('id_ukuran', $sizeId)
                ->where('address_id', $addressId)
                ->value('harga');
            if ($price !== null) {
                return (int) $price;
            }
        }

        // 4. General default + national default (id_user = 0, address_id = null)
        $price = DB::table('detail_harga')
            ->where($priceForeignKey, $priceIdentifier)
            ->where('id_user', 0)
            ->where('id_ukuran', $sizeId)
            ->whereNull('address_id')
            ->value('harga');
        if ($price !== null) {
            return (int) $price;
        }

        // Fallback to produk_size standard price
        foreach ([
            'produk_id' => $product->getKey(),
            'IdRoster' => $productIdentifier,
        ] as $pivotForeignKey => $pivotIdentifier) {
            if (! Schema::hasTable('produk_size') || ! Schema::hasColumn('produk_size', $pivotForeignKey)) {
                continue;
            }

            $pivotPrice = DB::table('produk_size')
                ->where($pivotForeignKey, $pivotIdentifier)
                ->where('id_ukuran', $sizeId)
                ->value('harga');

            if ($pivotPrice !== null) {
                return (int) $pivotPrice;
            }
        }

        return (int) round($details['harga'] ?? 0);
    }

    public function review()
    {
        $cart = session('cart', []);
        $orderNotes = session('order_notes', '');
        $shippingCost = session('shipping_cost', 0);

        // Get selected address from session or default
        $selectedAddressId = session('selected_address_id');
        $selectedAddress = null;
        if ($selectedAddressId) {
            $selectedAddress = \App\Models\Address::where('id', $selectedAddressId)
                ->where('user_id', auth()->id())
                ->first();
        }
        if (! $selectedAddress) {
            $selectedAddress = \App\Models\Address::where('user_id', auth()->id())
                ->where('is_default', true)
                ->first();
        }

        // Calculate subtotal and grand total
        $subtotal = 0;
        $addressId = $selectedAddress ? $selectedAddress->id : null;
        foreach ($cart as $item) {
            $price = $this->resolveCartLinePrice($item, auth()->id(), $addressId);
            $subtotal += $price * $item['quantity'];
        }
        $grandTotal = $subtotal + $shippingCost;

        return view('toko.review', compact('cart', 'orderNotes', 'selectedAddress', 'shippingCost', 'subtotal', 'grandTotal'));
    }

    private function generateTransactionId(): string
    {
        do {
            $transactionId = 'TR'.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (Transaksi::where('IdTransaksi', $transactionId)->exists());

        return $transactionId;
    }
}
