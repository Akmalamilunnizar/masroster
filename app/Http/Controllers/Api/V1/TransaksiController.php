<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // Keep if you use User model directly for other methods
use App\Models\Transaksi;
use Illuminate\Support\Facades\Validator; // Keep if you use validation in other methods
use Illuminate\Support\Facades\DB; // Keep if you use raw DB queries in other methods
use Illuminate\Support\Facades\Log; // For debugging
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon; // Keep if you use Carbon for date manipulations
// Excel export will use HTML table streamed as .xls to avoid external type deps

class TransaksiController extends Controller
{
    /**
     * Menampilkan daftar transaksi dengan fitur filter bulan/tahun dan pencarian.
     * Menggunakan eager loading untuk relasi 'detail'.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');
        $search = $request->input('search');
        $status_pesanan = $request->input('status_pesanan'); // Add status_pesanan filter

        // Mulai query Transaksi dengan eager loading 'detail' dan 'customer'
        $query = Transaksi::with(['detail', 'customer']);

        // Filter jika ada bulan
        if ($bulan) {
            $query->whereMonth('tglTransaksi', $bulan);
        }

        // Filter jika ada tahun
        if ($tahun) {
            $query->whereYear('tglTransaksi', $tahun);
        }

        // Filter jika ada status pesanan
        if ($status_pesanan) {
            $query->where('StatusPesanan', $status_pesanan);
        }

        // Filter jika ada pencarian
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('IdTransaksi', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($qCustomer) use ($search) {
                      $qCustomer->where('f_name', 'like', "%{$search}%");
                  });
            });
        }

        // Urutkan dan ambil data
        $transaksi = $query->orderBy('tglTransaksi', 'desc')->get();

        // Kirim data ke view
        return view('admin.allTransaksi', compact('transaksi', 'bulan', 'tahun', 'search', 'status_pesanan'));
    }

    /**
     * Metode untuk menerima orderan transaksi.
     * Menggunakan POST request.
     *
     * @param string $id ID dari transaksi yang akan diterima.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function terimaOrderan($id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return redirect()->route('alltransaksi')->with('error', 'Transaksi tidak ditemukan.');
        }

        // Ubah status pesanan menjadi 'Diterima'
        $transaksi->StatusPesanan = 'Diterima';
        $transaksi->save();

        return redirect()->route('alltransaksi')->with('message', 'Orderan berhasil diterima!');
    }

    /**
     * Metode untuk menolak orderan transaksi.
     * Menggunakan POST request.
     *
     * @param string $id ID dari transaksi yang akan ditolak.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function tolakOrderan($id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return redirect()->route('alltransaksi')->with('error', 'Transaksi tidak ditemukan.');
        }

        // Ubah status pesanan menjadi 'Ditolak'
        $transaksi->StatusPesanan = 'Ditolak';
        $transaksi->save();

        return redirect()->route('alltransaksi')->with('message', 'Orderan berhasil ditolak!');
    }

    /**
     * Mengekspor data transaksi ke PDF dengan filter bulan/tahun.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request)
    {
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');

        $query = Transaksi::query()->with(['detail', 'customer']); // Eager load detail dan customer untuk PDF

        if ($bulan) {
            $query->whereMonth('tglTransaksi', $bulan); // Pastikan kolom tanggal transaksi yang benar
        }

        if ($tahun) {
            $query->whereYear('tglTransaksi', $tahun); // Pastikan kolom tanggal transaksi yang benar
        }

        $transaksis = $query->orderBy('tglTransaksi', 'asc')->get();

        $pdf = Pdf::loadView('admin.transaksi_pdf', compact('transaksis', 'bulan', 'tahun'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('laporan-transaksi.pdf');
    }

    /**
     * Export LSTM dataset as CSV
     */
    public function exportLstm()
    {
        $transactions = Transaksi::with(['detailTransaksi.produk', 'detailTransaksi.size'])
            ->orderBy('tglTransaksi', 'asc')
            ->get();

        $filename = 'dataset_lstm.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, ['tanggal', 'produk', 'jumlah_terjual', 'stok_awal', 'stok_akhir']);
            
            foreach ($transactions as $transaksi) {
                foreach ($transaksi->detailTransaksi as $detail) {
                    $row = [
                        \Carbon\Carbon::parse($transaksi->tglTransaksi)->format('Y-m-d'),
                        $detail->produk ? $detail->produk->jenisRoster->JenisBarang ?? 'N/A' : 'N/A',
                        $detail->QtyProduk ?? 0,
                        $this->getStokAwal($detail->IdRoster ?? null),
                        $this->getStokAkhir($detail->IdRoster ?? null)
                    ];
                    fputcsv($file, $row);
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Prophet dataset as CSV
     */
    public function exportProphet()
    {
        $transactions = Transaksi::with(['detailTransaksi.produk', 'detailTransaksi.size'])
            ->orderBy('tglTransaksi', 'asc')
            ->get();

        $filename = 'dataset_prophet.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, ['ds', 'y', 'hari_libur', 'promo', 'kategori_produk']);
            
            foreach ($transactions as $transaksi) {
                foreach ($transaksi->detailTransaksi as $detail) {
                    $row = [
                        \Carbon\Carbon::parse($transaksi->tglTransaksi)->format('Y-m-d'), // ds field
                        $detail->QtyProduk ?? 0, // y field (jumlah_terjual)
                        $this->isHariLibur(\Carbon\Carbon::parse($transaksi->tglTransaksi)) ? 1 : 0,
                        $this->hasPromo($transaksi) ? 1 : 0,
                        $detail->produk ? $detail->produk->jenisRoster->JenisBarang ?? 'N/A' : 'N/A'
                    ];
                    fputcsv($file, $row);
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getStokAwal($productId)
    {
        // TODO: Implement stock calculation logic
        // For now, return a default value
        return 100;
    }

    private function getStokAkhir($productId)
    {
        // TODO: Implement stock calculation logic
        // For now, return a default value
        return 80;
    }

    private function isHariLibur($date)
    {
        // TODO: Implement holiday detection logic
        // For now, check if it's weekend
        return $date->isWeekend();
    }

    private function hasPromo($transaksi)
    {
        // TODO: Implement promo detection logic
        // For now, return false
        return 0;
    }

    public function showTransaction($id)
    {
        $transaksi = Transaksi::with(['customer', 'admin', 'detailTransaksi.produk', 'detailTransaksi.size'])
            ->where('IdTransaksi', $id)
            ->firstOrFail();

        return view('admin.transaction_details', compact('transaksi'));
    }

    public function ViewOrder($id)
    {
        $orders = Transaksi::with(['customer', 'admin', 'detailTransaksi.produk', 'detailTransaksi.size'])
            ->where('IdTransaksi', $id)
            ->firstOrFail();

        return view('admin.vieworder', compact('orders'));
    }

    /**
     * Update invoice number for a transaction
     *
     * @param  \Illuminate\Http\Request  $request
     * @param string $id ID of the transaction
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateInvoice(Request $request, $id)
    {
        $request->validate([
            'invoice_number' => 'required|string|max:50'
        ]);

        $transaksi = Transaksi::findOrFail($id);
        $transaksi->invoice_number = $request->invoice_number;
        $transaksi->save();

        return redirect()->back()->with('message', 'Invoice number berhasil diperbarui!');
    }

    /**
     * Show form to create new manual transaction
     */
    public function create()
    {
        $users = User::where('user', '!=', 'Admin')->get();
        $products = \App\Models\Produk::with(['sizes', 'jenisRoster', 'tipeRoster', 'motif'])->get();
        
        // Generate new transaction ID (max 8 characters)
        $lastTransaksi = Transaksi::orderBy('IdTransaksi', 'desc')->first();
        if ($lastTransaksi) {
            // Extract the numeric part and increment
            $numericPart = (int) substr($lastTransaksi->IdTransaksi, 2) + 1;
            $newId = 'TX' . str_pad($numericPart, 6, '0', STR_PAD_LEFT);
        } else {
            $newId = 'TX000001';
        }

        return view('admin.transaksi.create', compact('users', 'products', 'newId'));
    }

    /**
     * Store new manual transaction
     */
    public function store(Request $request)
    {
        // Debug: Log the incoming request data
        Log::info('Transaction store request received:', $request->all());
        
        // Clean up formatted values before validation
        $requestData = $request->all();
        
        // Convert formatted Bayar to raw number
        if (isset($requestData['Bayar'])) {
            $requestData['Bayar'] = (int) str_replace(['.', ','], '', $requestData['Bayar']);
        }
        
        // Convert formatted GrandTotal to raw number
        if (isset($requestData['GrandTotal'])) {
            $requestData['GrandTotal'] = (int) str_replace(['.', ','], '', $requestData['GrandTotal']);
        }
        
        // Convert formatted ongkir to raw number
        if (isset($requestData['ongkir'])) {
            $requestData['ongkir'] = (int) str_replace(['.', ','], '', $requestData['ongkir']);
        }
        
        // Update the request with cleaned values
        $request->merge($requestData);
        
        Log::info('Cleaned request data:', $request->all());
        
        try {
            $validated = $request->validate([
                'IdTransaksi' => 'required|unique:transaksi,IdTransaksi',
                'id_customer' => 'required|exists:users,id',
                'address_id' => 'required|exists:addresses,id',
                'Bayar' => 'required|numeric|min:0',
                'GrandTotal' => 'required|numeric|min:0',
                'StatusPembayaran' => 'required|in:Lunas,Hutang,Transfer',
                'StatusPesanan' => 'required|in:Pending,Diterima,Ditolak,MENUNGGU KONFIRMASI',
                'shipping_method' => 'required|string',
                'delivery_method' => 'required|in:Pickup,Delivery',
                'shipping_type' => 'required|in:Free Ongkir,Ongkir',
                'ongkir' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|exists:produk,IdRoster',
                'products.*.size_id' => 'required|exists:size,id_ukuran',
                'products.*.qty' => 'required|integer|min:1',
                'products.*.price' => 'required|numeric|min:0',
            ]);
            
            Log::info('Validation passed:', $validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }

        DB::beginTransaction();
        try {
            // Debug: Log the request data
            Log::info('Transaction creation request:', $request->all());
            
            // Create transaction
            $transaksi = Transaksi::create([
                'IdTransaksi' => $request->IdTransaksi,
                'id_customer' => $request->id_customer,
                'id_admin' => 1, // Set default admin ID - adjust as needed
                'address_id' => $request->address_id,
                'Bayar' => $request->Bayar,
                'GrandTotal' => $request->GrandTotal,
                'tglTransaksi' => now(),
                'StatusPembayaran' => $request->StatusPembayaran,
                'StatusPesanan' => $request->StatusPesanan,
                'shipping_method' => $request->shipping_method,
                'delivery_method' => $request->delivery_method,
                'shipping_type' => $request->shipping_type,
                'ongkir' => $request->ongkir,
                'notes' => $request->notes,
            ]);
            
            Log::info('Transaction created successfully:', $transaksi->toArray());

            // Create detail transactions
            foreach ($request->products as $product) {
                Log::info('Creating detail transaction:', $product);
                \App\Models\DetailTransaksi::create([
                    'IdTransaksi' => $transaksi->IdTransaksi,
                    'IdRoster' => $product['product_id'],
                    'id_ukuran' => $product['size_id'],
                    'QtyProduk' => $product['qty'],
                    'SubTotal' => $product['price'] * $product['qty'],
                ]);

                // Auto-generate/update detail_harga for this customer, roster, and size
                try {
                    $hargaSatuan = (int) $product['price'];
                    DB::table('detail_harga')->updateOrInsert(
                        [
                            'id_roster' => $product['product_id'],
                            'id_user' => $request->id_customer,
                            'id_ukuran' => $product['size_id'],
                        ],
                        [
                            'harga' => $hargaSatuan,
                        ]
                    );
                    Log::info('Detail harga upserted', [
                        'id_roster' => $product['product_id'],
                        'id_user' => $request->id_customer,
                        'id_ukuran' => $product['size_id'],
                        'harga' => $hargaSatuan,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to upsert detail_harga', [
                        'error' => $e->getMessage(),
                        'product' => $product,
                        'id_customer' => $request->id_customer,
                    ]);
                    throw $e;
                }
            }

            DB::commit();
            return redirect()->route('alltransaksi')->with('message', 'Transaksi berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Transaction creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show form to edit transaction
     */
    public function edit($id)
    {
        $transaksi = Transaksi::with(['detailTransaksi.produk', 'detailTransaksi.size', 'customer', 'address'])->findOrFail($id);
        $users = User::where('user', '!=', 'Admin')->get();
        $products = \App\Models\Produk::with(['sizes', 'jenisRoster', 'tipeRoster', 'motif'])->get();
        
        // Load addresses for the current customer
        $customerAddresses = [];
        if ($transaksi->customer) {
            $customerAddresses = \App\Models\Address::where('user_id', $transaksi->customer->id)->get();
        }

        return view('admin.transaksi.edit', compact('transaksi', 'users', 'products', 'customerAddresses'));
    }

    /**
     * Update transaction
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_customer' => 'required|exists:users,id',
            'address_id' => 'required|exists:addresses,id',
            'Bayar' => 'required|numeric|min:0',
            'GrandTotal' => 'required|numeric|min:0',
            'StatusPembayaran' => 'required|in:Lunas,Hutang,Transfer',
            'StatusPesanan' => 'required|in:Pending,Diterima,Ditolak,MENUNGGU KONFIRMASI',
            'shipping_method' => 'required|string',
            'delivery_method' => 'required|in:Pickup,Delivery',
            'shipping_type' => 'nullable|string',
            'ongkir' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:produk,IdRoster',
            'products.*.size_id' => 'required|exists:size,id_ukuran',
            'products.*.qty' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $transaksi = Transaksi::findOrFail($id);
            
            // Update transaction
            $transaksi->update([
                'id_customer' => $request->id_customer,
                'address_id' => $request->address_id,
                'Bayar' => $request->Bayar,
                'GrandTotal' => $request->GrandTotal,
                'StatusPembayaran' => $request->StatusPembayaran,
                'StatusPesanan' => $request->StatusPesanan,
                'shipping_method' => $request->shipping_method,
                'delivery_method' => $request->delivery_method,
                'shipping_type' => $request->shipping_type,
                'ongkir' => $request->ongkir,
                'notes' => $request->notes,
                'tglUpdate' => now(),
            ]);

            // Delete existing detail transactions
            \App\Models\DetailTransaksi::where('IdTransaksi', $id)->delete();

            // Create new detail transactions
            foreach ($request->products as $product) {
                \App\Models\DetailTransaksi::create([
                    'IdTransaksi' => $transaksi->IdTransaksi,
                    'IdRoster' => $product['product_id'],
                    'id_ukuran' => $product['size_id'],
                    'QtyProduk' => $product['qty'],
                    'SubTotal' => $product['price'] * $product['qty'],
                ]);

                // Auto-generate/update detail_harga for this customer, roster, and size (on update)
                try {
                    $hargaSatuan = (int) $product['price'];
                    DB::table('detail_harga')->updateOrInsert(
                        [
                            'id_roster' => $product['product_id'],
                            'id_user' => $request->id_customer,
                            'id_ukuran' => $product['size_id'],
                        ],
                        [
                            'harga' => $hargaSatuan,
                        ]
                    );
                    Log::info('Detail harga upserted (update)', [
                        'id_roster' => $product['product_id'],
                        'id_user' => $request->id_customer,
                        'id_ukuran' => $product['size_id'],
                        'harga' => $hargaSatuan,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to upsert detail_harga on update', [
                        'error' => $e->getMessage(),
                        'product' => $product,
                        'id_customer' => $request->id_customer,
                    ]);
                    throw $e;
                }
            }

            DB::commit();
            return redirect()->route('alltransaksi')->with('message', 'Transaksi berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete transaction
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            // Delete detail transactions first
            \App\Models\DetailTransaksi::where('IdTransaksi', $id)->delete();
            
            // Delete main transaction
            Transaksi::findOrFail($id)->delete();

            DB::commit();
            return redirect()->route('alltransaksi')->with('message', 'Transaksi berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Catatan: Jika ada metode lain seperti ManageTransaksi, AddTransaksi, StoreTransaksi,
    // EditTransaksi, UpdateTransaksi, DeleteTransaksi, tambahkan di sini sesuai kebutuhan.
}