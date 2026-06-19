<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()
            ->where('username', 'admin')
            ->orWhere('email', 'admin@modernshop.test')
            ->firstOrNew();

        $admin->fill([
            'name' => 'Administrator PRO',
            'username' => 'admin',
            'email' => 'admin@modernshop.test',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now(),
        ])->save();

        $categories = [
            1 => 'Peralatan Elektronik',
            2 => 'Pakaian Pria',
            3 => 'Pakaian Wanita',
            4 => 'Makanan',
            5 => 'Minuman',
            6 => 'Buku',
            7 => 'Mainan Anak',
            8 => 'Olahraga',
            9 => 'Otomotif',
        ];

        foreach ($categories as $id => $name) {
            Kategori::query()->updateOrCreate(['id_kategori' => $id], ['nama_kategori' => $name]);
        }

        $products = [
            [1, 1, 'Sepatu Compass Update', 400000, 10],
            [2, 1, 'Smartphone Samsung', 4000000, 30],
            [3, 2, 'Kemeja Flanel', 150000, 50],
            [4, 3, 'Dress Bunga', 200000, 40],
            [5, 4, 'Keripik Kentang', 15000, 100],
            [6, 5, 'Kopi Susu', 20000, 80],
            [7, 6, 'Novel Fiksi', 85000, 25],
            [8, 7, 'Lego Set', 500000, 10],
            [9, 8, 'Sepatu Futsal', 300000, 20],
            [11, 1, 'Sepatu Compass', 350000, 15],
        ];

        foreach ($products as [$id, $categoryId, $name, $price, $stock]) {
            Produk::query()->updateOrCreate(
                ['id_produk' => $id],
                ['id_kategori' => $categoryId, 'nama_produk' => $name, 'harga' => $price, 'stok' => $stock]
            );
        }

        $customers = [
            [1, 'Budi Santoso', 'budi@email.com', '08999999999'],
            [2, 'Siti Aminah', 'siti@email.com', '081234567891'],
            [3, 'Andi Wijaya', 'andi@email.com', '081234567892'],
            [4, 'Rina Melati', 'rina@email.com', '081234567893'],
            [5, 'Joko Susilo', 'joko@email.com', '081234567894'],
            [6, 'Maya Sari', 'maya@email.com', '081234567895'],
            [7, 'Dedi Pratama', 'dedi@email.com', '081234567896'],
            [8, 'Nita Rahma', 'nita@email.com', '081234567897'],
            [9, 'Agus Setiawan', 'agus@email.com', '081234567898'],
        ];

        foreach ($customers as [$id, $name, $email, $phone]) {
            Pelanggan::query()->updateOrCreate(
                ['id_pelanggan' => $id],
                ['nama_pelanggan' => $name, 'email' => $email, 'telepon' => $phone]
            );
        }

        $orders = [
            [1, 1, '2023-10-01', 24000000],
            [2, 2, '2023-10-02', 300000],
            [3, 3, '2023-10-03', 4000000],
            [4, 4, '2023-10-04', 400000],
            [5, 5, '2023-10-05', 100000],
            [6, 6, '2023-10-06', 85000],
            [7, 7, '2023-10-07', 500000],
            [8, 8, '2023-10-08', 300000],
            [9, 9, '2023-10-09', 15000],
            [14, 1, '2026-05-17', 0],
        ];

        foreach ($orders as [$id, $customerId, $date, $total]) {
            Pesanan::query()->updateOrCreate(
                ['id_pesanan' => $id],
                ['id_pelanggan' => $customerId, 'tanggal_pesanan' => $date, 'status' => 'Selesai', 'total_harga' => $total]
            );
        }
    }
}
