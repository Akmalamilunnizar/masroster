<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = Auth::user()->addresses;
        return view('toko.details', compact('addresses'));
    }

    public function store(Request $request)
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
            Auth::user()->addresses()->update(['is_default' => false]);
        }

        $address = Auth::user()->addresses()->create($request->all());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Address saved successfully',
                'address' => $address
            ]);
        }

        return redirect()->route('shipping')->with('success', 'Address saved successfully');
    }

    public function setDefault(Address $address)
    {
        // Ensure the address belongs to the authenticated user
        if ($address->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Unset any existing default
        Auth::user()->addresses()->update(['is_default' => false]);

        // Set this address as default
        $address->update(['is_default' => true]);

        session(['selected_address_id' => $address->id]);
        return response()->json([
            'success' => true,
            'message' => 'Default address updated'
        ]);
    }

    public function destroy(Address $address)
    {
        // Ensure the address belongs to the authenticated user
        if ($address->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully'
        ]);
    }

    public function update(Request $request, Address $address)
    {
        $request->validate([
            'label' => 'required',
            'recipient_name' => 'required',
            'phone_number' => 'required',
            'city' => 'required',
            'postal_code' => 'required',
            'full_address' => 'required',
        ]);
        $address->update($request->all());
        return response()->json(['success' => true]);
    }

    public function setSelectedAddress(Request $request)
    {
        $request->validate(['address_id' => 'required|integer|exists:addresses,id']);
        session(['selected_address_id' => $request->address_id]);
        return response()->json(['success' => true]);
    }
} 