<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produk;

class UpdateProductNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:update-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing products with generated names from jenis, tipe, and motif';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to update product names...');

        // Get all products with their relationships
        $products = Produk::with(['jenisRoster', 'tipeRoster', 'motif'])->get();

        $updated = 0;
        $skipped = 0;

        foreach ($products as $product) {
            $oldName = $product->NamaProduk;
            $newName = $product->generateNamaProduk();

            if ($oldName !== $newName) {
                $product->NamaProduk = $newName;
                $product->save();

                $this->line("Updated: {$product->IdRoster} - '{$oldName}' → '{$newName}'");
                $updated++;
            } else {
                $this->line("Skipped: {$product->IdRoster} - '{$newName}' (already up to date)");
                $skipped++;
            }
        }

        $this->info("Update complete! Updated: {$updated}, Skipped: {$skipped}");

        return Command::SUCCESS;
    }
}
