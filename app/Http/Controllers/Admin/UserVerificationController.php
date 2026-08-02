<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Enums\WorkflowStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserVerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * List all pending retailers.
     */
    public function index()
    {
        $users = User::where('tipe_user', 'retailer')
            ->where('status_verifikasi', 'pending')
            ->get();

        return view('admin.verifications.index', compact('users'));
    }

    /**
     * Approve a pending retailer user.
     */
    public function approve($id)
    {
        $user = User::findOrFail($id);

        if ($user->tipe_user !== 'retailer') {
            return redirect()->back()->with('error', 'User bukan tipe retailer.');
        }

        $user->status_verifikasi = 'approved';
        $user->save();

        // Transition draft orders to 'Menunggu Pembayaran'
        Transaksi::where('id_customer', $user->id)
            ->where('workflow_status', WorkflowStatus::DRAFT->value)
            ->update([
                'workflow_status' => WorkflowStatus::MENUNGGU_PEMBAYARAN->value,
                'updated_at' => now(),
            ]);

        return redirect()->route('allusers')->with('message', 'Retailer berhasil disetujui!');
    }

    /**
     * Reject a pending retailer user.
     * Downgrade user type to end_customer, status_verifikasi = rejected,
     * and recalculate draft orders using default base pricing (id_user = 0).
     */
    public function reject($id)
    {
        $user = User::findOrFail($id);

        if ($user->tipe_user !== 'retailer') {
            return redirect()->back()->with('error', 'User bukan tipe retailer.');
        }

        // Downgrade to end_customer and mark as rejected
        $user->tipe_user = 'end_customer';
        $user->status_verifikasi = 'rejected';
        $user->save();

        // Recalculate draft orders
        $draftTransactions = Transaksi::where('id_customer', $user->id)
            ->where('workflow_status', WorkflowStatus::DRAFT->value)
            ->get();

        foreach ($draftTransactions as $transaksi) {
            $total = 0;
            $details = DetailTransaksi::where('IdTransaksi', $transaksi->IdTransaksi)->get();

            foreach ($details as $detail) {
                // Find default price
                $defaultPrice = $this->resolveDefaultPrice(
                    (int) $detail->produk_id,
                    $detail->IdRoster,
                    (int) $detail->id_ukuran,
                    $transaksi->address_id
                );

                $detail->harga_satuan = $defaultPrice;
                $detail->SubTotal = $defaultPrice * $detail->QtyProduk;
                $detail->save();

                $total += $detail->SubTotal;
            }

            // Update transaction grand total
            $transaksi->GrandTotal = $total + $transaksi->ongkir;
            $transaksi->updated_at = now();
            $transaksi->save();
        }

        return redirect()->route('allusers')->with('message', 'Retailer ditolak dan diturunkan statusnya!');
    }

    /**
     * Resolve default price for a product/size/address combination.
     */
    private function resolveDefaultPrice(int $produkId, ?string $sku, int $sizeId, ?int $addressId): int
    {
        // 1. Check detail_harga for id_user = 0 with address_id
        if ($addressId !== null) {
            $price = DB::table('detail_harga')
                ->where('id_ukuran', $sizeId)
                ->where('id_user', 0)
                ->where('address_id', $addressId)
                ->where(function ($q) use ($produkId, $sku) {
                    $q->where('produk_id', $produkId);
                    if ($sku) {
                        $q->orWhere('id_roster', $sku);
                    }
                })
                ->value('harga');
            if ($price !== null) {
                return (int) $price;
            }
        }

        // 2. Check detail_harga for id_user = 0 and address_id IS NULL (national default)
        $price = DB::table('detail_harga')
            ->where('id_ukuran', $sizeId)
            ->where('id_user', 0)
            ->whereNull('address_id')
            ->where(function ($q) use ($produkId, $sku) {
                $q->where('produk_id', $produkId);
                if ($sku) {
                    $q->orWhere('id_roster', $sku);
                }
            })
            ->value('harga');
        if ($price !== null) {
            return (int) $price;
        }

        // 3. Fallback to produk_size standard price
        $price = DB::table('produk_size')
            ->where('id_ukuran', $sizeId)
            ->where(function ($q) use ($produkId, $sku) {
                $q->where('produk_id', $produkId);
                if ($sku) {
                    $q->orWhere('IdRoster', $sku);
                }
            })
            ->value('harga');
        if ($price !== null) {
            return (int) $price;
        }

        return 0;
    }
}
