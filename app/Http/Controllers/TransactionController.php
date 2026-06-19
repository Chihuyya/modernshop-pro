<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
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
