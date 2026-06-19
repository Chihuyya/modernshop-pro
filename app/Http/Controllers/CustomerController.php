<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Menampilkan daftar pelanggan dan total belanja mereka.
     */
    public function index()
    {
        $customers = Pelanggan::withSum('pesanan as total_belanja', 'total_harga')
            ->withCount('pesanan as jumlah_pesanan')
            ->orderByDesc('total_belanja')
            ->get()
            ->map(function ($customer) {
                return [
                    'id_pelanggan' => $customer->id_pelanggan,
                    'nama_pelanggan' => $customer->nama_pelanggan,
                    'email' => $customer->email,
                    'telepon' => $customer->telepon,
                    'total_belanja' => (float) ($customer->total_belanja ?? 0),
                    'jumlah_pesanan' => $customer->jumlah_pesanan,
                ];
            });

        return Inertia::render('Customers/Index', [
            'customers' => $customers
        ]);
    }
}
