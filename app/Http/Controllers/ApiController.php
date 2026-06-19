<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    /**
     * Endpoint API 1: Login Admin
     */
    public function login(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        if (empty($username) || empty($password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username dan Password wajib diisi bray!'
            ], 400);
        }

        $user = User::where('username', $username)
            ->orWhere('email', $username)
            ->first();

        // Cek hash Laravel ATAU plaintext (backwards compatibility)
        if ($user && (Hash::check($password, $user->password) || $password === $user->password)) {
            $randomToken = bin2hex(random_bytes(16));
            
            // Simpan token di cache selama 2 jam
            Cache::put('api_token_' . $randomToken, true, 7200);

            return response()->json([
                'status' => 'success',
                'message' => 'Login Berhasil bray!',
                'token' => $randomToken
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Username atau Password salah bray!'
        ], 401);
    }

    /**
     * Endpoint API 2: CRUD Produk
     */
    public function listProduk(Request $request, $id = null)
    {
        if ($id) {
            $produk = Produk::find($id);
            if (!$produk) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Produk tidak ditemukan!'
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'data' => $produk
            ]);
        }

        $produkList = Produk::orderBy('id_produk', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $produkList
        ]);
    }

    public function createProduk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'nama_produk' => 'required|string|max:150',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'url_gambar' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $produk = Produk::create([
            'id_kategori' => $request->input('id_kategori'),
            'nama_produk' => $request->input('nama_produk'),
            'harga' => $request->input('harga'),
            'stok' => $request->input('stok'),
            'url_gambar' => $request->input('url_gambar')
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Produk baru berhasil ditambahkan!'
        ], 201);
    }

    public function updateProduk(Request $request, $id)
    {
        $produk = Produk::find($id);
        if (!$produk) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan!'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'nama_produk' => 'required|string|max:150',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'url_gambar' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $produk->update([
            'id_kategori' => $request->input('id_kategori'),
            'nama_produk' => $request->input('nama_produk'),
            'harga' => $request->input('harga'),
            'stok' => $request->input('stok'),
            'url_gambar' => $request->input('url_gambar')
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Detail produk berhasil diperbarui!'
        ]);
    }

    public function deleteProduk($id)
    {
        $produk = Produk::find($id);
        if (!$produk) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan!'
            ], 404);
        }

        $produk->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil dihapus dari database!'
        ]);
    }

    /**
     * Endpoint API 3: CRUD Transaksi (Pesanan)
     */
    public function listTransaksi(Request $request, $id = null)
    {
        if ($id) {
            $pesanan = Pesanan::with('pelanggan')->find($id);
            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi tidak ditemukan!'
                ], 404);
            }
            
            // Format format output agar mencakup nama_pelanggan
            $data = $pesanan->toArray();
            $data['nama_pelanggan'] = $pesanan->pelanggan ? $pesanan->pelanggan->nama_pelanggan : null;

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        }

        $pesananList = Pesanan::with('pelanggan')->orderBy('id_pesanan', 'desc')->get()->map(function ($pesanan) {
            $data = $pesanan->toArray();
            $data['nama_pelanggan'] = $pesanan->pelanggan ? $pesanan->pelanggan->nama_pelanggan : null;
            return $data;
        });

        return response()->json([
            'status' => 'success',
            'data' => $pesananList
        ]);
    }

    public function createTransaksi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
            'total_harga' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $pesanan = Pesanan::create([
            'id_pelanggan' => $request->input('id_pelanggan'),
            'tanggal_pesanan' => now()->toDateString(),
            'total_harga' => $request->input('total_harga') ?? 0,
            'status' => $request->input('status') ?? 'Pending'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi data baru berhasil disimpan!'
        ], 201);
    }

    public function updateTransaksiStatus(Request $request, $id)
    {
        $pesanan = Pesanan::find($id);
        if (!$pesanan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi tidak ditemukan!'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $pesanan->update([
            'status' => $request->input('status')
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status transaksi berhasil diupdate!'
        ]);
    }

    public function deleteTransaksi($id)
    {
        $pesanan = Pesanan::find($id);
        if (!$pesanan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi tidak ditemukan!'
            ], 404);
        }

        $pesanan->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data transaksi berhasil dihapus!'
        ]);
    }

    /**
     * Endpoint API 4: Detail Transaksi (Detail Pesanan)
     */
    public function createTransaksiDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_pesanan' => 'required|exists:pesanan,id_pesanan',
            'id_produk' => 'required|exists:produk,id_produk',
            'jumlah' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $id_pesanan = $request->input('id_pesanan');
        $id_produk = $request->input('id_produk');
        $jumlah = $request->input('jumlah');

        $produk = Produk::find($id_produk);
        $harga_satuan = $produk->harga;
        $subtotal = $harga_satuan * $jumlah;

        $detail = DetailPesanan::create([
            'id_pesanan' => $id_pesanan,
            'id_produk' => $id_produk,
            'jumlah' => $jumlah,
            'harga_satuan' => $harga_satuan,
            'subtotal' => $subtotal
        ]);

        // Rekalkulasi total_harga pesanan induk
        $pesanan = Pesanan::find($id_pesanan);
        $pesanan->update([
            'total_harga' => DetailPesanan::where('id_pesanan', $id_pesanan)->sum('subtotal')
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Detail transaksi baru berhasil ditambahkan!'
        ], 201);
    }

    public function deleteTransaksiDetail($id)
    {
        $detail = DetailPesanan::find($id);
        if (!$detail) {
            return response()->json([
                'status' => 'error',
                'message' => 'Detail transaksi tidak ditemukan!'
            ], 404);
        }

        $id_pesanan = $detail->id_pesanan;
        $detail->delete();

        // Rekalkulasi total_harga pesanan induk
        $pesanan = Pesanan::find($id_pesanan);
        $pesanan->update([
            'total_harga' => DetailPesanan::where('id_pesanan', $id_pesanan)->sum('subtotal') ?: 0
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Detail transaksi berhasil dihapus!'
        ]);
    }

    /**
     * Endpoint API 5: Statistik Real Count
     */
    public function stats(Request $request)
    {
        $jumlah_transaksi = Pesanan::count();
        $total_pendapatan = Pesanan::sum('total_harga') ?: 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'jumlah_transaksi' => $jumlah_transaksi,
                'total_pendapatan' => (float)$total_pendapatan
            ]
        ]);
    }
}
