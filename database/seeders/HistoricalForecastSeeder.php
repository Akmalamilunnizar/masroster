<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HistoricalForecastSeeder extends Seeder
{
    private const ONGKIR = 50000;

    private array $products = [
        'MAS001' => ['nama' => 'Roster Mukura Classical Brown', 'id_ukuran' => 1,  'harga' => 63000],
        'MAS002' => ['nama' => 'Bovenlis Jendela Beton',        'id_ukuran' => 10, 'harga' => 50000],
        'MAS003' => ['nama' => 'Roster Biasa 3D',               'id_ukuran' => 1,  'harga' => 6000],
        'MAS004' => ['nama' => 'Bovenlis Jendela Beton',        'id_ukuran' => 7,  'harga' => 35000],
    ];

    public function run(): void
    {
        mt_srand(20260403); // deterministic but realistic randomness

        $hasCustomUkuran = Schema::hasColumn('detail_transaksi', 'CustomUkuran');
        $hasDesignFile   = Schema::hasColumn('detail_transaksi', 'design_file');

        $transaksiRows = [];
        $detailRows = [];

        $seq = 1;
        $start = Carbon::create(2024, 1, 1)->startOfMonth();
        $end   = Carbon::create(2026, 3, 31)->endOfMonth();

        for ($month = $start->copy(); $month->lte($end); $month->addMonth()) {
            $targetTx = $this->monthlyTransactionTarget($month);

            for ($i = 0; $i < $targetTx; $i++) {
                $tgl = $month->copy()
                    ->day(mt_rand(1, $month->daysInMonth))
                    ->hour(mt_rand(8, 16))
                    ->minute(mt_rand(0, 59))
                    ->second(mt_rand(0, 59));

                // detail_transaksi.IdTransaksi is varchar(8), so keep IDs fixed to 8 chars.
                $idTransaksi = 'TR' . str_pad((string)$seq, 6, '0', STR_PAD_LEFT);
                $seq++;

                $details = $this->buildTransactionDetails($month);

                $subtotalTotal = array_sum(array_column($details, 'SubTotal'));
                $grandTotal = $subtotalTotal + self::ONGKIR;

                $transaksiRows[] = [
                    'IdTransaksi'      => $idTransaksi,
                    'id_admin'         => 1,
                    'id_customer'      => 4,
                    'address_id'       => 2,
                    'Bayar'            => $grandTotal,
                    'GrandTotal'       => $grandTotal,
                    'tglTransaksi'     => $tgl->format('Y-m-d H:i:s'),
                    'StatusPembayaran' => 'Lunas',
                    'StatusPesanan'    => 'Diterima',
                    'tglUpdate'        => $tgl->format('Y-m-d H:i:s'),
                    'shipping_method'  => 'Online',
                    'delivery_method'  => 'Delivery',
                    'shipping_type'    => 'Ongkir',
                    'ongkir'           => self::ONGKIR,
                    'notes'            => null,
                    'created_at'       => $tgl->format('Y-m-d H:i:s'),
                    'updated_at'       => $tgl->format('Y-m-d H:i:s'),
                ];

                foreach ($details as $d) {
                    $row = [
                        'IdTransaksi' => $idTransaksi,
                        'IdRoster'    => $d['IdRoster'],
                        'id_ukuran'   => $d['id_ukuran'],
                        'QtyProduk'   => $d['QtyProduk'],
                        'SubTotal'    => $d['SubTotal'],
                        'data_type'   => $d['data_type'],
                    ];

                    if ($hasCustomUkuran) {
                        $row['CustomUkuran'] = null;
                    }
                    if ($hasDesignFile) {
                        $row['design_file'] = null;
                    }

                    $detailRows[] = $row;
                }
            }
        }

        DB::transaction(function () use ($transaksiRows, $detailRows) {
            foreach (array_chunk($transaksiRows, 500) as $chunk) {
                DB::table('transaksi')->insert($chunk);
            }
            foreach (array_chunk($detailRows, 1000) as $chunk) {
                DB::table('detail_transaksi')->insert($chunk);
            }
        });
    }

    private function monthlyTransactionTarget(Carbon $month): int
    {
        $base = 6;

        // Seasonality kemarau (Juli-Agustus): demand naik
        if (in_array((int)$month->month, [7, 8], true)) {
            $base += 3;
        }

        // Seasonality menjelang/sekitar Ramadhan-Lebaran (aproksimasi per tahun)
        $ym = $month->format('Y-m');
        $ramadanLebaranWindows = [
            '2024-03', '2024-04',
            '2025-02', '2025-03', '2025-04',
            '2026-02', '2026-03',
        ];
        if (in_array($ym, $ramadanLebaranWindows, true)) {
            $base += 4;
        }

        // noise kecil bulanan
        $base += mt_rand(0, 2);

        return $base;
    }

    private function buildTransactionDetails(Carbon $month): array
    {
        $details = [];
        $isSeasonPeak = $this->isSeasonPeak($month);

        // 1) MAS001 stabil (eceran konstan)
        $qtyMas001 = mt_rand(18, 26) + ($isSeasonPeak ? mt_rand(2, 5) : 0);
        $details[] = $this->makeDetail('MAS001', $qtyMas001, 'Eceran');

        // 2) MAS003 proporsional (eceran menengah)
        if (mt_rand(1, 100) <= 70) {
            $qtyMas003 = mt_rand(12, 45) + ($isSeasonPeak ? mt_rand(3, 10) : 0);
            $details[] = $this->makeDetail('MAS003', $qtyMas003, 'Eceran');
        }

        // 3) MAS004 proporsional (eceran ringan-menengah)
        if (mt_rand(1, 100) <= 45) {
            $qtyMas004 = mt_rand(6, 20) + ($isSeasonPeak ? mt_rand(2, 6) : 0);
            $details[] = $this->makeDetail('MAS004', $qtyMas004, 'Eceran');
        }

        // 4) MAS002 borongan (jarang, qty > 100)
        $boronganChance = $isSeasonPeak ? 20 : 12;
        if (mt_rand(1, 100) <= $boronganChance) {
            $qtyMas002 = mt_rand(110, 240); // wajib borongan
            $details[] = $this->makeDetail('MAS002', $qtyMas002, 'Borongan');
        }

        return $details;
    }

    private function isSeasonPeak(Carbon $month): bool
    {
        if (in_array((int)$month->month, [7, 8], true)) {
            return true;
        }

        $ym = $month->format('Y-m');
        return in_array($ym, [
            '2024-03', '2024-04',
            '2025-02', '2025-03', '2025-04',
            '2026-02', '2026-03',
        ], true);
    }

    private function makeDetail(string $idRoster, int $qty, string $dataType): array
    {
        $p = $this->products[$idRoster];
        $subTotal = $qty * $p['harga'];

        return [
            'IdRoster'  => $idRoster,
            'id_ukuran' => $p['id_ukuran'],
            'QtyProduk' => $qty,
            'SubTotal'  => $subTotal,
            'data_type' => $dataType,
        ];
    }
}