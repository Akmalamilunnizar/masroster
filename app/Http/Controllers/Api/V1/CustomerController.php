<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Customer; // Pastikan model Customer Anda diimpor
use App\Models\Transaksi; // Impor model Transaksi jika Anda ingin mengambil riwayat transaksi pelanggan
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Diperlukan untuk middleware 'auth'

class CustomerController extends Controller
{
    public function __construct()
    {
        // Menambahkan middleware 'auth' untuk memastikan hanya pengguna terautentikasi yang dapat mengakses
        $this->middleware('auth');
    }

    public function Index()
    {
        $customer = User::with('defaultAddress')->get();
        return view("admin.allcustomer", compact('customer'));
    }

    // Metode baru untuk menampilkan detail pelanggan
    public function customerDetails($id)
    {
        // Mengambil detail pelanggan berdasarkan ID
        // Asumsi 'Customer' adalah model yang menyimpan detail pelanggan (f_name, alamat, dll.)
        $customer = Customer::find($id);

        if (!$customer) {
            // Tangani jika pelanggan tidak ditemukan, misalnya redirect atau tampilkan 404
            return redirect()->back()->with('error', 'Pelanggan tidak ditemukan.');
            // abort(404); // Atau bisa juga menggunakan abort(404)
        }

        // Anda juga bisa mengambil transaksi terkait pelanggan ini jika diperlukan
        // Misalnya, jika ada relasi hasMany di model Customer ke model Transaksi
        // Pastikan Anda memiliki relasi 'transaksis' di model Customer jika ingin menggunakan ini
        /*
        // Contoh relasi di model Customer.php:
        // public function transaksis()
        // {
        //     return $this->hasMany(Transaksi::class, 'id_customer_di_transaksi', 'id'); // Sesuaikan foreign key jika berbeda
        // }
        // $customerTransactions = $customer->transaksis;
        */

        return view('admin.customerdetails', compact('customer'));
    }

    // Jika Anda memiliki metode deleteCustomer, Anda bisa menambahkannya di sini
    public function deleteCustomer($id)
    {
        // Logika untuk menghapus customer
        $customer = Customer::find($id);
        if ($customer) {
            $customer->delete();
            return redirect()->back()->with('message', 'Pelanggan berhasil dihapus!');
        }
        return redirect()->back()->with('error', 'Pelanggan tidak ditemukan!');
    }
    public function show($id)
    {
        // Get the customer (User)
        $customer = User::findOrFail($id);

        // Get all transaksi for this customer, eager load the address
        $transaksis = \App\Models\Transaksi::with('address')
            ->where('id', $id)
            ->get();

        // Get unique addresses used in transaksi
        $addresses = $transaksis->pluck('address')->unique('id')->values();

        return view('admin.customerdetails', compact('customer', 'addresses', 'transaksis'));
    }

}
