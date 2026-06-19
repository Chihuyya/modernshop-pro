<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    /**
     * Menampilkan daftar transaksi.
     */
    public function index(Request $request)
    {
        $query = Pesanan::with(['pelanggan', 'detailPesanan.produk']);

        // Filter Pencarian (Nama Pelanggan atau ID Pesanan)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id_pesanan', 'like', "%{$search}%")
                  ->orWhereHas('pelanggan', function ($qp) use ($search) {
                      $qp->where('nama_pelanggan', 'like', "%{$search}%");
                  });
            });
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderByDesc('id_pesanan')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $request->only(['search', 'status'])
        ]);
    }
    /**
     * Memperbarui status pesanan.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:Pending,Diproses,Dikirim,Selesai,Dibatalkan'
        ]);

        $pesanan = Pesanan::findOrFail($id);
        $pesanan->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Status pesanan #' . $id . ' berhasil diperbarui menjadi ' . $request->status . '.');
    }
}
