<?php

namespace Tests\Feature;

use App\Models\DetailPesanan;
use App\Models\Kategori;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiMigrationTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $kategori;
    private $pelanggan;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup dummy data
        $this->user = User::create([
            'name' => 'Admin Test',
            'username' => 'admintest',
            'email' => 'admintest@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->kategori = Kategori::create([
            'nama_kategori' => 'Kategori Test'
        ]);

        $this->pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Pelanggan Test',
            'email' => 'pelanggan@example.com',
            'telepon' => '08123456789'
        ]);
    }

    /**
     * Test POST /api/login
     */
    public function test_login_success()
    {
        $response = $this->postJson('/api/login', [
            'username' => 'admintest',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'token'
            ]);

        $token = $response->json('token');
        $this->assertTrue(Cache::has('api_token_' . $token));
    }

    public function test_login_failure()
    {
        $response = $this->postJson('/api/login', [
            'username' => 'admintest',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Username atau Password salah bray!'
            ]);
    }

    /**
     * Test access with missing token
     */
    public function test_api_requires_token()
    {
        $response = $this->getJson('/api/produk');
        $response->assertStatus(401);
    }

    /**
     * Test access with invalid token
     */
    public function test_api_rejects_invalid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token_123'
        ])->getJson('/api/produk');

        $response->assertStatus(403);
    }

    /**
     * Test full CRUD flow for Produk
     */
    public function test_produk_crud_flow()
    {
        // Login to get token
        $loginResponse = $this->postJson('/api/login', [
            'username' => 'admintest',
            'password' => 'password123'
        ]);
        $token = $loginResponse->json('token');

        $headers = ['Authorization' => 'Bearer ' . $token];

        // 1. Read (empty or initial list)
        $response = $this->withHeaders($headers)->getJson('/api/produk');
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data']);

        // 2. Create
        $response = $this->withHeaders($headers)->postJson('/api/produk', [
            'id_kategori' => $this->kategori->id_kategori,
            'nama_produk' => 'Produk Test Baru',
            'harga' => 15000,
            'stok' => 50,
            'url_gambar' => 'http://example.com/test.jpg'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Produk baru berhasil ditambahkan!'
            ]);

        // Find the created product
        $produk = Produk::where('nama_produk', 'Produk Test Baru')->first();
        $this->assertNotNull($produk);

        // 3. Read single
        $response = $this->withHeaders($headers)->getJson('/api/produk/' . $produk->id_produk);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id_produk' => $produk->id_produk,
                    'nama_produk' => 'Produk Test Baru'
                ]
            ]);

        // 4. Update
        $response = $this->withHeaders($headers)->putJson('/api/produk/' . $produk->id_produk, [
            'id_kategori' => $this->kategori->id_kategori,
            'nama_produk' => 'Produk Test Terupdate',
            'harga' => 18000,
            'stok' => 45,
            'url_gambar' => 'http://example.com/test2.jpg'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Detail produk berhasil diperbarui!'
            ]);

        $produk->refresh();
        $this->assertEquals('Produk Test Terupdate', $produk->nama_produk);
        $this->assertEquals(18000, (float)$produk->harga);

        // 5. Delete
        $response = $this->withHeaders($headers)->deleteJson('/api/produk/' . $produk->id_produk);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Produk berhasil dihapus dari database!'
            ]);

        $this->assertNull(Produk::find($produk->id_produk));
    }

    /**
     * Test Transaksi CRUD and stats
     */
    public function test_transaksi_and_stats_flow()
    {
        // Login
        $loginResponse = $this->postJson('/api/login', [
            'username' => 'admintest',
            'password' => 'password123'
        ]);
        $token = $loginResponse->json('token');
        $headers = ['Authorization' => 'Bearer ' . $token];

        // Create a product to use in detail
        $produk = Produk::create([
            'id_kategori' => $this->kategori->id_kategori,
            'nama_produk' => 'Produk Detail Test',
            'harga' => 10000,
            'stok' => 10
        ]);

        // 1. Create Transaction (Pesanan)
        $response = $this->withHeaders($headers)->postJson('/api/transaksi', [
            'id_pelanggan' => $this->pelanggan->id_pelanggan,
            'status' => 'Pending'
        ]);

        $response->assertStatus(201);

        // Find transaction
        $pesanan = Pesanan::where('id_pelanggan', $this->pelanggan->id_pelanggan)
            ->where('status', 'Pending')
            ->orderBy('id_pesanan', 'desc')
            ->first();

        $this->assertNotNull($pesanan);
        $this->assertEquals(0, (float)$pesanan->total_harga);

        // 2. Add Detail Transaksi
        $response = $this->withHeaders($headers)->postJson('/api/transaksi_detail', [
            'id_pesanan' => $pesanan->id_pesanan,
            'id_produk' => $produk->id_produk,
            'jumlah' => 3
        ]);

        $response->assertStatus(201);

        // Verify total price recalculated
        $pesanan->refresh();
        $this->assertEquals(30000, (float)$pesanan->total_harga);

        // Verify detail item created
        $detail = DetailPesanan::where('id_pesanan', $pesanan->id_pesanan)->first();
        $this->assertNotNull($detail);
        $this->assertEquals(10000, (float)$detail->harga_satuan);
        $this->assertEquals(30000, (float)$detail->subtotal);

        // 3. Stats Check
        $response = $this->withHeaders($headers)->getJson('/api/stats');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'jumlah_transaksi',
                    'total_pendapatan'
                ]
            ]);

        // 4. Delete Detail Transaksi
        $response = $this->withHeaders($headers)->deleteJson('/api/transaksi_detail/' . $detail->id_detail);
        $response->assertStatus(200);

        // Verify total price recalculated to 0
        $pesanan->refresh();
        $this->assertEquals(0, (float)$pesanan->total_harga);

        // 5. Delete Transaksi
        $response = $this->withHeaders($headers)->deleteJson('/api/transaksi/' . $pesanan->id_pesanan);
        $response->assertStatus(200);

        $this->assertNull(Pesanan::find($pesanan->id_pesanan));
    }
}
