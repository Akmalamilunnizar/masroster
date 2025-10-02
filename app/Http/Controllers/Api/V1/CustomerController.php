<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function Index()
    {
        $customer = User::with('defaultAddress')->get();
        return view("admin.allcustomer", compact('customer'));
    }

    public function customerDetails($id)
    {
        // Get the customer (User)
        $customer = User::findOrFail($id);

        // Get all transaksi for this customer
        $transaksis = Transaksi::where('id_customer', $id)
            ->orderBy('tglTransaksi', 'desc')
            ->get();

        // Get customer's addresses from the addresses table
        $addresses = $customer->addresses;

        return view('admin.customerdetails', compact('customer', 'addresses', 'transaksis'));
    }

    public function deleteCustomer($id)
    {
        $customer = User::find($id);
        if ($customer) {
            $customer->delete();
            return redirect()->back()->with('message', 'Customer berhasil dihapus!');
        }
        return redirect()->back()->with('error', 'Customer tidak ditemukan!');
    }

    public function getCustomerAddresses($id)
    {
        $customer = User::findOrFail($id);
        $addresses = $customer->addresses;
        
        return response()->json($addresses);
    }
}
