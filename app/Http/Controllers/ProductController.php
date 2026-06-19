<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        return Inertia::render('Products/Index', [
            'products' => Produk::with('kategori')->latest('id_produk')->get(),
            'categories' => Kategori::orderBy('nama_kategori')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Produk::create($this->validatedData($request));

        return back()->with('success', 'Produk baru berhasil ditambahkan.');
    }

    public function update(Request $request, Produk $product)
    {
        $product->update($this->validatedData($request));

        return back()->with('success', 'Detail produk berhasil diperbarui.');
    }

    public function destroy(Produk $product)
    {
        $product->delete();

        return back()->with('success', 'Produk berhasil dihapus.');
    }

    public function restock(Request $request, Produk $product)
    {
        $request->validate([
            'tambahan_stok' => ['required', 'integer', 'min:1']
        ]);

        $product->increment('stok', $request->tambahan_stok);

        return back()->with('success', 'Stok produk ' . $product->nama_produk . ' berhasil ditambah ' . $request->tambahan_stok . ' unit.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'id_kategori' => ['required', 'exists:kategori,id_kategori'],
            'nama_produk' => ['required', 'string', 'max:150'],
            'harga' => ['required', 'numeric', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'url_gambar' => ['nullable', 'url', 'max:255'],
        ]);
    }
}
