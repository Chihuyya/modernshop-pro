<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('kategori')) {
            Schema::create('kategori', function (Blueprint $table) {
                $table->id('id_kategori');
                $table->string('nama_kategori', 100);
            });
        }

        if (! Schema::hasTable('produk')) {
            Schema::create('produk', function (Blueprint $table) {
                $table->id('id_produk');
                $table->foreignId('id_kategori')->nullable()->constrained('kategori', 'id_kategori')->cascadeOnDelete();
                $table->string('nama_produk', 150);
                $table->decimal('harga', 10, 2);
                $table->integer('stok');
                $table->string('url_gambar')->nullable();
            });
        } elseif (! Schema::hasColumn('produk', 'url_gambar')) {
            Schema::table('produk', function (Blueprint $table) {
                $table->string('url_gambar')->nullable()->after('stok');
            });
        }

        if (! Schema::hasTable('pelanggan')) {
            Schema::create('pelanggan', function (Blueprint $table) {
                $table->id('id_pelanggan');
                $table->string('nama_pelanggan', 100);
                $table->string('email', 100)->unique();
                $table->string('telepon', 15)->nullable();
            });
        }

        if (! Schema::hasTable('pesanan')) {
            Schema::create('pesanan', function (Blueprint $table) {
                $table->id('id_pesanan');
                $table->foreignId('id_pelanggan')->nullable()->constrained('pelanggan', 'id_pelanggan')->cascadeOnDelete();
                $table->date('tanggal_pesanan');
                $table->string('status')->default('Selesai');
                $table->decimal('total_harga', 12, 2);
            });
        } elseif (! Schema::hasColumn('pesanan', 'status')) {
            Schema::table('pesanan', function (Blueprint $table) {
                $table->string('status')->default('Selesai')->after('tanggal_pesanan');
            });
        }

        if (! Schema::hasTable('detail_pesanan')) {
            Schema::create('detail_pesanan', function (Blueprint $table) {
                $table->id('id_detail');
                $table->foreignId('id_pesanan')->nullable()->constrained('pesanan', 'id_pesanan')->cascadeOnDelete();
                $table->foreignId('id_produk')->nullable()->constrained('produk', 'id_produk')->cascadeOnDelete();
                $table->integer('jumlah');
                $table->integer('harga_satuan');
                $table->decimal('subtotal', 12, 2);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('produk', 'url_gambar')) {
            Schema::table('produk', function (Blueprint $table) {
                $table->dropColumn('url_gambar');
            });
        }

        if (Schema::hasColumn('pesanan', 'status')) {
            Schema::table('pesanan', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
