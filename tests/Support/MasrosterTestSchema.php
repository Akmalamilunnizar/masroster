<?php

namespace Tests\Support;

use App\Models\Address;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait MasrosterTestSchema
{
    protected function prepareMasrosterSchema(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table): void {
                $table->id();
                $table->string('f_name', 30);
                $table->string('email')->unique();
                $table->string('nomor_telepon', 20);
                $table->timestamp('email_verified_at')->nullable();
                $table->string('username', 20)->unique();
                $table->string('password');
                $table->string('user', 10);
                $table->rememberToken();
                $table->string('img', 255);
                $table->string('alamat')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->unique();
                $table->string('display_name')->nullable();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->unique();
                $table->string('display_name')->nullable();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('role_user')) {
            Schema::create('role_user', function (Blueprint $table): void {
                $table->unsignedBigInteger('role_id');
                $table->unsignedBigInteger('user_id');
                $table->string('user_type');
                $table->primary(['user_id', 'role_id', 'user_type']);
            });
        }

        if (!Schema::hasTable('permission_user')) {
            Schema::create('permission_user', function (Blueprint $table): void {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('user_id');
                $table->string('user_type');
                $table->primary(['user_id', 'permission_id', 'user_type']);
            });
        }

        if (!Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function (Blueprint $table): void {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');
                $table->primary(['permission_id', 'role_id']);
            });
        }

        if (!Schema::hasTable('addresses')) {
            Schema::create('addresses', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('label');
                $table->string('recipient_name');
                $table->string('phone_number');
                $table->string('city');
                $table->string('postal_code');
                $table->text('full_address');
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('size')) {
            Schema::create('size', function (Blueprint $table): void {
                $table->id('id_ukuran');
                $table->string('nama', 50);
                $table->integer('panjang');
                $table->integer('lebar');
            });
        }

        if (!Schema::hasTable('jenisbarang')) {
            Schema::create('jenisbarang', function (Blueprint $table): void {
                $table->id('IdJenisBarang');
                $table->string('JenisBarang', 50)->unique();
            });
        }

        if (!Schema::hasTable('tipe_roster')) {
            Schema::create('tipe_roster', function (Blueprint $table): void {
                $table->id('IdTipe');
                $table->string('namaTipe', 50);
            });
        }

        if (!Schema::hasTable('motif_roster')) {
            Schema::create('motif_roster', function (Blueprint $table): void {
                $table->id('IdMotif');
                $table->string('nama_motif', 50)->nullable();
            });
        }

        if (!Schema::hasTable('produk')) {
            Schema::create('produk', function (Blueprint $table): void {
                $table->string('IdRoster', 13)->primary();
                $table->string('NamaProduk')->nullable();
                $table->integer('id_jenis');
                $table->integer('id_tipe')->nullable();
                $table->integer('id_motif')->nullable();
                $table->integer('stock')->default(0);
                $table->string('Img')->nullable();
                $table->string('deskripsi', 1500);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->float('forecasted_demand')->nullable();
                $table->string('forecast_model', 20)->nullable();
                $table->integer('safety_stock')->default(70);
                $table->string('forecast_status')->default('safe');
                $table->timestamp('last_forecast_at')->nullable();
            });
        }

        if (!Schema::hasTable('transaksi')) {
            Schema::create('transaksi', function (Blueprint $table): void {
                $table->string('IdTransaksi', 10)->primary();
                $table->unsignedBigInteger('id_admin')->default(0);
                $table->unsignedBigInteger('id_customer');
                $table->unsignedBigInteger('address_id')->nullable();
                $table->integer('Bayar');
                $table->integer('GrandTotal');
                $table->dateTime('tglTransaksi');
                $table->string('StatusPembayaran', 20);
                $table->string('StatusPesanan', 20)->nullable();
                $table->string('invoice_number', 50)->nullable();
                $table->dateTime('tglUpdate')->nullable();
                $table->timestamps();
                $table->string('shipping_method')->nullable();
                $table->string('delivery_method')->nullable();
                $table->string('shipping_type')->nullable();
                $table->integer('ongkir')->default(0);
                $table->text('notes')->nullable();
            });
        }

        if (!Schema::hasTable('barangmasuk')) {
            Schema::create('barangmasuk', function (Blueprint $table): void {
                $table->string('IdMasuk', 6)->primary();
                $table->string('username', 20);
                $table->date('tglMasuk')->nullable();
            });
        }

        if (!Schema::hasTable('barangkeluar')) {
            Schema::create('barangkeluar', function (Blueprint $table): void {
                $table->string('IdKeluar', 6)->primary();
                $table->string('username', 20)->nullable();
                $table->date('tglKeluar')->nullable();
            });
        }

        if (!Schema::hasTable('detail_barangmasuk')) {
            Schema::create('detail_barangmasuk', function (Blueprint $table): void {
                $table->string('IdMasuk', 6)->nullable();
                $table->string('IdRoster', 13)->nullable();
                $table->integer('QtyMasuk')->nullable();
                $table->integer('HargaSatuan');
                $table->integer('SubTotal');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('detail_barangkeluar')) {
            Schema::create('detail_barangkeluar', function (Blueprint $table): void {
                $table->string('IdKeluar', 6)->nullable();
                $table->string('IdRoster', 13)->nullable();
                $table->integer('QtyKeluar')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('detail_harga')) {
            Schema::create('detail_harga', function (Blueprint $table): void {
                $table->string('id_roster', 13);
                $table->unsignedBigInteger('id_user');
                $table->integer('id_ukuran');
                $table->integer('harga');
            });
        }

        if (!Schema::hasTable('produk_size')) {
            Schema::create('produk_size', function (Blueprint $table): void {
                $table->string('IdRoster', 13);
                $table->integer('id_ukuran');
                $table->integer('harga');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('detail_transaksi')) {
            Schema::create('detail_transaksi', function (Blueprint $table): void {
                $table->string('IdTransaksi', 10)->nullable();
                $table->string('IdRoster', 13)->nullable();
                $table->integer('id_ukuran')->nullable();
                $table->integer('QtyProduk')->nullable();
                $table->integer('SubTotal')->nullable();
                $table->string('data_type', 20)->default('Eceran');
            });
        }

        $this->createInventoryTriggers();

        $this->seedReferenceData();
    }

    protected function resetMasrosterData(): void
    {
        foreach ([
            'permission_user',
            'permission_role',
            'role_user',
            'permissions',
            'roles',
            'detail_harga',
            'produk_size',
            'detail_barangkeluar',
            'detail_barangmasuk',
            'barangkeluar',
            'barangmasuk',
            'detail_transaksi',
            'transaksi',
            'addresses',
            'motif_roster',
            'tipe_roster',
            'jenisbarang',
            'produk',
            'size',
            'users',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        $this->seedReferenceData();
    }

    protected function seedReferenceData(): void
    {
        if (Schema::hasTable('roles') && DB::table('roles')->count() === 0) {
            DB::table('roles')->insert([
                ['id' => 1, 'name' => 'admin', 'display_name' => 'Admin', 'description' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 2, 'name' => 'user', 'display_name' => 'User', 'description' => 'User', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (Schema::hasTable('size') && DB::table('size')->count() === 0) {
            DB::table('size')->insert([
                ['id_ukuran' => 1, 'nama' => 'Standard', 'panjang' => 20, 'lebar' => 20],
            ]);
        }

        if (Schema::hasTable('jenisbarang') && DB::table('jenisbarang')->count() === 0) {
            DB::table('jenisbarang')->insert([
                ['IdJenisBarang' => 1, 'JenisBarang' => 'Roster'],
                ['IdJenisBarang' => 2, 'JenisBarang' => 'Bovenlis'],
            ]);
        }

        if (Schema::hasTable('tipe_roster') && DB::table('tipe_roster')->count() === 0) {
            DB::table('tipe_roster')->insert([
                ['IdTipe' => 1, 'namaTipe' => 'Mukura'],
                ['IdTipe' => 2, 'namaTipe' => 'Jendela'],
                ['IdTipe' => 3, 'namaTipe' => 'Biasa'],
            ]);
        }

        if (Schema::hasTable('motif_roster') && DB::table('motif_roster')->count() === 0) {
            DB::table('motif_roster')->insert([
                ['IdMotif' => 1, 'nama_motif' => 'Classical Brown'],
                ['IdMotif' => 2, 'nama_motif' => 'Modern Gray'],
            ]);
        }
    }

    protected function createInventoryTriggers(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS stokMasuk');
        DB::unprepared('DROP TRIGGER IF EXISTS stokKeluar');

        DB::unprepared('CREATE TRIGGER stokMasuk AFTER INSERT ON detail_barangmasuk BEGIN
            UPDATE produk SET stock = stock + NEW.QtyMasuk WHERE IdRoster = NEW.IdRoster;
        END;');

        DB::unprepared('CREATE TRIGGER stokKeluar AFTER INSERT ON detail_barangkeluar BEGIN
            UPDATE produk SET stock = stock - NEW.QtyKeluar WHERE IdRoster = NEW.IdRoster;
        END;');
    }

    protected function createMasrosterUser(array $attributes = [], string $role = 'User'): User
    {
        $name = $attributes['f_name'] ?? 'Test User';
        $defaults = [
            'f_name' => $name,
            'email' => fake()->unique()->safeEmail(),
            'nomor_telepon' => '081234567890',
            'email_verified_at' => now(),
            'username' => Str::slug($name) . '-' . fake()->unique()->numberBetween(100, 999),
            'password' => 'password',
            'user' => $role,
            'img' => 'default-avatar.png',
            'alamat' => 'Jl. Testing No. 1',
        ];

        $user = User::create(array_merge($defaults, $attributes));

        $this->attachRole($user, strtolower($role));

        return $user;
    }

    protected function createAdminUser(array $attributes = []): User
    {
        return $this->createMasrosterUser(array_merge([
            'f_name' => 'Admin Tester',
            'username' => 'admin-' . fake()->unique()->numberBetween(100, 999),
            'user' => 'Admin',
        ], $attributes), 'Admin');
    }

    protected function createCustomerUser(array $attributes = []): User
    {
        return $this->createMasrosterUser(array_merge([
            'f_name' => 'Customer Tester',
            'username' => 'customer-' . fake()->unique()->numberBetween(100, 999),
            'user' => 'User',
        ], $attributes), 'User');
    }

    protected function attachRole(User $user, string $roleName): void
    {
        $roleId = DB::table('roles')->where('name', $roleName)->value('id');

        DB::table('role_user')->updateOrInsert([
            'user_id' => $user->id,
            'role_id' => $roleId,
            'user_type' => User::class,
        ], []);
    }

    protected function createMasrosterAddress(User $user, array $attributes = []): Address
    {
        return Address::create(array_merge([
            'user_id' => $user->id,
            'label' => 'Rumah',
            'recipient_name' => $user->f_name,
            'phone_number' => $user->nomor_telepon,
            'city' => 'Malang',
            'postal_code' => '65111',
            'full_address' => 'Jl. Testing No. 1, Malang',
            'is_default' => true,
        ], $attributes));
    }

    protected function createMasrosterProduct(array $attributes = []): Produk
    {
        return Produk::create(array_merge([
            'IdRoster' => 'MAS' . fake()->unique()->numberBetween(100, 999),
            'NamaProduk' => 'Roster Test Product',
            'id_jenis' => 1,
            'id_tipe' => 1,
            'id_motif' => 1,
            'stock' => 100,
            'Img' => 'produk/test.png',
            'deskripsi' => 'Produk uji otomatis',
            'forecasted_demand' => null,
            'forecast_model' => null,
            'safety_stock' => 70,
            'forecast_status' => 'safe',
            'last_forecast_at' => null,
        ], $attributes));
    }

    protected function createMasrosterTransaction(array $attributes = []): Transaksi
    {
        return Transaksi::create(array_merge([
            'IdTransaksi' => 'TX' . fake()->unique()->numberBetween(1000, 9999),
            'id_admin' => 1,
            'id_customer' => 1,
            'address_id' => null,
            'Bayar' => 0,
            'GrandTotal' => 0,
            'tglTransaksi' => now(),
            'StatusPembayaran' => 'Belum Lunas',
            'StatusPesanan' => 'Menunggu Konfirmasi',
            'tglUpdate' => now(),
            'shipping_method' => 'Online',
            'delivery_method' => 'Delivery',
            'shipping_type' => 'Ongkir',
            'ongkir' => 0,
            'notes' => null,
        ], $attributes));
    }

    protected function createMasrosterDetailTransaction(array $attributes = []): void
    {
        DB::table('detail_transaksi')->insert(array_merge([
            'IdTransaksi' => 'TX0001',
            'IdRoster' => 'MAS001',
            'id_ukuran' => 1,
            'QtyProduk' => 1,
            'SubTotal' => 1,
            'data_type' => 'Eceran',
        ], $attributes));
    }
}