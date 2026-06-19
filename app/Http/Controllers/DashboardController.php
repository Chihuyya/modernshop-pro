<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\DetailPesanan;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Peringatan Stok Menipis (stok <= 5)
        $lowStockProducts = Produk::where('stok', '<=', 5)
            ->orderBy('stok', 'asc')
            ->limit(10)
            ->get();

        // 2. Status Transaksi
        $transactionStatus = Pesanan::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // 3. Transaksi Terbaru
        $recentTransactions = Pesanan::with('pelanggan')
            ->orderBy('tanggal_pesanan', 'desc')
            ->orderBy('id_pesanan', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($pesanan) {
                return [
                    'id_pesanan' => $pesanan->id_pesanan,
                    'tanggal_pesanan' => $pesanan->tanggal_pesanan->toDateString(),
                    'status' => $pesanan->status,
                    'total_harga' => (float) $pesanan->total_harga,
                    'nama_pelanggan' => $pesanan->pelanggan ? $pesanan->pelanggan->nama_pelanggan : 'Guest',
                ];
            });

        // 4. Produk Terlaris
        $topProducts = DetailPesanan::selectRaw('id_produk, sum(jumlah) as total_terjual')
            ->with('produk')
            ->groupBy('id_produk')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get()
            ->map(function ($detail) {
                return [
                    'id_produk' => $detail->id_produk,
                    'nama_produk' => $detail->produk ? $detail->produk->nama_produk : 'Produk Dihapus',
                    'total_terjual' => (int) $detail->total_terjual,
                    'url_gambar' => $detail->produk ? $detail->produk->url_gambar : null,
                ];
            });

        // 5. Tren Pendapatan (7 Hari Terakhir)
        $sevenDaysAgo = now()->subDays(6)->toDateString();
        
        // Group by extracted date
        $revenueTrend = Pesanan::selectRaw('DATE(tanggal_pesanan) as date, sum(total_harga) as total')
            ->where('tanggal_pesanan', '>=', $sevenDaysAgo)
            ->groupBy(DB::raw('DATE(tanggal_pesanan)'))
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('total', 'date');

        // Isi hari yang kosong dengan 0
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateStr = now()->subDays($i)->toDateString();
            $chartData[] = [
                'date' => $dateStr,
                'total' => (float) ($revenueTrend[$dateStr] ?? 0)
            ];
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'jumlah_transaksi' => Pesanan::count(),
                'total_pendapatan' => (float) Pesanan::sum('total_harga'),
            ],
            'lowStockProducts' => $lowStockProducts,
            'transactionStatus' => $transactionStatus,
            'recentTransactions' => $recentTransactions,
            'topProducts' => $topProducts,
            'revenueTrend' => $chartData,
        ]);
    }
}
