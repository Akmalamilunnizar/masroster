<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AddressController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $addresses = $user->addresses;

        return view('toko.details', compact('addresses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'full_address' => 'required|string',
            'is_default' => 'boolean',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        abort_unless($user, 401);

        // If this is set as default, unset any existing default
        if (! empty($validated['is_default'])) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Address saved successfully',
                'address' => $address,
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

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Unset any existing default
        $user->addresses()->update(['is_default' => false]);

        // Set this address as default
        $address->update(['is_default' => true]);

        session(['selected_address_id' => $address->id]);

        return response()->json([
            'success' => true,
            'message' => 'Default address updated',
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
            'message' => 'Address deleted successfully',
        ]);
    }

    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'label' => 'required',
            'recipient_name' => 'required',
            'phone_number' => 'required',
            'city' => 'required',
            'postal_code' => 'required',
            'full_address' => 'required',
        ]);
        $address->update($validated);

        return response()->json(['success' => true]);
    }

    public function setSelectedAddress(Request $request)
    {
        $request->validate([
            'address_id' => [
                'required',
                'integer',
                Rule::exists('addresses', 'id')->where(fn ($query) => $query->where('user_id', Auth::id())),
            ],
        ]);

        $address = Address::findOrFail($request->address_id);

        session(['selected_address_id' => $address->id]);

        return response()->json(['success' => true]);
    }
}
