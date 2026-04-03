<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Address;

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
            $cart = session()->get('cart', []);

            $productId = $request->id;
            $ukuran = $request->ukuran;
            $ukuran_label = $request->ukuran_label ?? 'Ukuran Standar';

            if (!$productId || !$ukuran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk dan ukuran wajib dipilih.',
                ], 422);
            }

            // Make a unique key for product+ukuran
            $cartKey = $productId . '|' . $ukuran;

            $quantity = $request->quantity ?? 1;
            $subtotal = $request->subtotal ?? ($request->harga * $quantity);

            if (isset($cart[$cartKey])) {
                // Jika produk+ukuran sudah ada, tambahkan jumlah
                $cart[$cartKey]['quantity'] += $quantity;
                $cart[$cartKey]['subtotal'] = $cart[$cartKey]['harga'] * $cart[$cartKey]['quantity'];
            } else {
                // Jika produk+ukuran belum ada, tambahkan baru
                $cart[$cartKey] = [
                    "id" => $request->id,
                    "quantity" => $quantity,
                    "nama" => $request->nama,
                    "harga" => $request->harga,
                    "img" => $request->img,
                    "ukuran" => $ukuran,
                    "ukuran_label" => $ukuran_label,
                    "subtotal" => $subtotal,
                ];
            }

            session()->put('cart', $cart);

            return response()->json([
                'success' => true,
                'cartCount' => array_sum(array_column($cart, 'quantity')),
                'message' => 'Produk berhasil ditambahkan ke keranjang'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
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
                $cart[$id]['quantity'] = max(1, (int)$request->quantity);
            }
            session()->put('cart', $cart);
        }
        return response()->json(['success' => true]);
    }

    public function details(Request $request)
    {
        // Save notes to session if it's a POST request
        if ($request->isMethod('post') && $request->has('notes')) {
            session(['order_notes' => $request->notes]);
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
            'is_default' => 'boolean'
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
            'is_default' => $request->is_default ?? false
        ]);

        return redirect()->route('shipping')->with('success', 'Alamat berhasil disimpan');
    }

    public function saveShipping(Request $request)
    {
        $shippingData = $request->all();
        session(['shipping_method' => $shippingData['method']]);
        session(['shipping_type' => $shippingData['type'] ?? null]);
        session(['shipping_cost' => $shippingData['cost']]);

        $selectedAddressId = $request->address_id;
        if ($request->has('address_id')) {
            session(['selected_address_id' => $selectedAddressId]);
        }

        return response()->json(['success' => true]);
    }

    public function shipping()
    {
        $cart = session('cart');
        if (!$cart || count($cart) === 0) {
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
        if (!$selectedAddress) {
            $selectedAddress = Address::where('user_id', auth()->id())
                                    ->where('is_default', true)
                                    ->first();
        }

        \Log::info('selected_address_id in session: ' . session('selected_address_id'));

        return view('toko.shipping', compact('cart', 'selectedAddress'));
    }

}
