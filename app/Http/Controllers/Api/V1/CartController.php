<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Menambahkan item ke keranjang
    public function add(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|exists:produk,IdRoster',
                'ukuran' => 'required|exists:size,id_ukuran',
                'ukuran_label' => 'nullable|string|max:100',
                'quantity' => 'nullable|integer|min:1',
                'harga' => 'nullable|numeric|min:0',
                'nama' => 'nullable|string|max:200',
                'img' => 'nullable|string|max:255',
                'subtotal' => 'nullable|numeric|min:0',
            ]);

            $cart = session()->get('cart', []);

            $productId = (string) $validated['id'];
            $ukuran = (int) $validated['ukuran'];
            $ukuran_label = $validated['ukuran_label'] ?? 'Ukuran Standar';

            // Make a unique key for product+ukuran
            $cartKey = $productId.'|'.$ukuran;

            $quantity = isset($validated['quantity']) ? (int) $validated['quantity'] : 1;

            // Prefer authoritative price from DB (produk_size pivot) to prevent client tampering.
            $price = 0;
            try {
                $productModel = \App\Models\Produk::where('IdRoster', $productId)->first();
                if ($productModel) {
                    $sizeRow = $productModel->sizes()->where('id_ukuran', $ukuran)->first();
                    if ($sizeRow && isset($sizeRow->pivot->harga)) {
                        $price = (int) $sizeRow->pivot->harga;
                    }
                }
            } catch (\Throwable $e) {
                // ignore and try direct DB lookup below
            }

            // If relation lookup didn't yield a price, attempt direct pivot table lookup
            if ($price === 0) {
                try {
                    $pivot = \DB::table('produk_size')
                        ->where('IdRoster', $productId)
                        ->where('id_ukuran', $ukuran)
                        ->first();
                    if ($pivot && isset($pivot->harga)) {
                        $price = (int) $pivot->harga;
                    }
                } catch (\Throwable $e) {
                    // ignore and fall back to client price below
                }
            }

            // If DB had no authoritative price, fall back to client-provided price (best-effort)
            if ($price === 0 && isset($validated['harga'])) {
                $price = (int) round($validated['harga']);
            }

            // Calculate subtotal server-side to avoid client tampering
            $subtotal = $price * $quantity;

            if (isset($cart[$cartKey])) {
                $cart[$cartKey]['quantity'] += $quantity;
                $cart[$cartKey]['subtotal'] = $cart[$cartKey]['harga'] * $cart[$cartKey]['quantity'];
            } else {
                $cart[$cartKey] = [
                    'id' => $productId,
                    'quantity' => $quantity,
                    'nama' => $validated['nama'] ?? null,
                    'harga' => $price,
                    'img' => $validated['img'] ?? null,
                    'ukuran' => $ukuran,
                    'ukuran_label' => $ukuran_label,
                    'subtotal' => $subtotal,
                ];
            }

            session()->put('cart', $cart);

            return response()->json([
                'success' => true,
                'cartCount' => array_sum(array_column($cart, 'quantity')),
                'message' => 'Produk berhasil ditambahkan ke keranjang',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Menampilkan halaman keranjang
    public function index()
    {
        $cart = session('cart', []);

        // dd(session('cart'));
        return view('toko.cart', compact('cart'));

    }

    // Menghapus item dari keranjang
    public function remove($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        return redirect()->back()->with('success', 'Produk dihapus dari keranjang.');
    }

    // Mengurangi jumlah item (quantity -1)
    public function decrease(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
        ]);

        $id = $request->input('id');

        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['quantity']--;

            if ($cart[$id]['quantity'] <= 0) {
                unset($cart[$id]);
            }

            session()->put('cart', $cart);
        }

        return response()->json([
            'success' => true,
            'cartCount' => array_sum(array_column($cart, 'quantity')),
        ]);
    }

    public function update(Request $request, $id)
    {
        $cart = session()->get('cart');
        if (isset($cart[$id])) {
            if ($request->type == 'increase') {
                $cart[$id]['quantity'] += 1;
            } elseif ($request->type == 'decrease') {
                $cart[$id]['quantity'] -= 1;
                if ($cart[$id]['quantity'] <= 0) {
                    unset($cart[$id]);
                }
            } elseif ($request->type == 'set' && $request->has('quantity')) {
                $cart[$id]['quantity'] = max(1, (int) $request->quantity);
            }
            session()->put('cart', $cart);
        }

        return response()->json(['success' => true]);
    }

    public function details(Request $request)
    {
        // Save notes to session if it's a POST request
        if ($request->isMethod('post') && $request->has('notes')) {
            $notes = $request->validate([
                'notes' => 'nullable|string|max:2000',
            ]);
            session(['order_notes' => $notes['notes'] ?? null]);
        }

        $addresses = \App\Models\Address::where('user_id', auth()->id())->get();
        $user = auth()->user();
        $userPhone = $user ? $user->nomor_telepon : '';

        return view('toko.details', compact('addresses', 'userPhone'));
    }

    public function saveAddress(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'full_address' => 'required|string',
            'is_default' => 'boolean',
        ]);

        // If this is set as default, unset any existing default
        if ($request->is_default) {
            Address::where('user_id', Auth::id())
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        // Create new address
        $address = Address::create([
            'user_id' => Auth::id(),
            'label' => $request->label,
            'recipient_name' => $request->recipient_name,
            'phone_number' => $request->phone_number,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
            'full_address' => $request->full_address,
            'is_default' => $request->is_default ?? false,
        ]);

        return redirect()->route('shipping')->with('success', 'Alamat berhasil disimpan');
    }

    public function saveShipping(Request $request)
    {
        $validated = $request->validate([
            'method' => 'required|string|in:Online,Offline',
            'type' => 'nullable|string|in:Pickup,Delivery',
            'cost' => 'required|numeric|min:0',
            'address_id' => 'nullable|exists:addresses,id',
        ]);

        session(['shipping_method' => $validated['method']]);
        session(['shipping_type' => $validated['type'] ?? null]);
        session(['shipping_cost' => (int) round($validated['cost'])]);

        if (! empty($validated['address_id'])) {
            session(['selected_address_id' => (int) $validated['address_id']]);
        }

        return response()->json(['success' => true]);
    }

    public function shipping()
    {
        $cart = session('cart');
        if (! $cart || count($cart) === 0) {
            return redirect()->route('tokodashboard')->with('error', 'Keranjang kosong. Silakan pilih produk terlebih dahulu.');
        }

        // Get the selected address ID from session
        $selectedAddressId = session('selected_address_id');

        // Get the address details
        $selectedAddress = null;
        if ($selectedAddressId) {
            $selectedAddress = Address::find($selectedAddressId);
        }

        // If no address is selected, get the default address
        if (! $selectedAddress) {
            $selectedAddress = Address::where('user_id', auth()->id())
                ->where('is_default', true)
                ->first();
        }

        \Log::info('selected_address_id in session: '.session('selected_address_id'));

        return view('toko.shipping', compact('cart', 'selectedAddress'));
    }
}
