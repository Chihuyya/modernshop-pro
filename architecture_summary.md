# Deep Dive: Anatomi Kode & Cara Kerja ModernShop PRO

Dokumen ini membedah secara teknis (hingga ke level kode) bagaimana sistem toko online Anda bekerja. Di sini Anda akan menemukan lokasi spesifik (*file paths*) dan logika inti yang menggerakkan sistem Anda.

---

## 🛠️ Stack Teknologi (Tech Stack)

Sistem ini didesain menggunakan arsitektur **Hybrid SPA (Single Page Application)** modern yang memadukan keunggulan performa server PHP dengan antarmuka frontend yang responsif tanpa reload halaman.

### 1. Sisi Frontend (Client-Side)
*   **Framework Utama**: **Vue.js 3** (menggunakan *Composition API* dengan `<script setup>` untuk penulisan kode modern).
*   **Jembatan Komunikasi**: **Inertia.js** (berperan sebagai *glue* / penghubung. Inertia menghilangkan kebutuhan membuat API routing & state management terpisah di frontend, karena Vue langsung menerima data `props` dari Laravel Controller).
*   **Bundler & Compiler**: **Vite** (menyusun dan mengompres aset JavaScript dan CSS agar load-time menjadi instan).
*   **Styling & Tampilan**: **Tailwind CSS** (menyediakan kerangka styling utility-first untuk desain yang bersih dan responsif).
*   **Grafik & Visualisasi**: **Chart.js & Vue-Chartjs** (menggambar tren pendapatan real-time di Dashboard).

### 2. Sisi Backend (Server-Side)
*   **Framework Utama**: **Laravel 11 / 12** (PHP runtime modern, terstruktur, dan memiliki keamanan tinggi bawaan).
*   **Database Relasional**: **MySQL** (menyimpan data master Produk, Kategori, Pelanggan, Pesanan, dan Detail Pesanan).
*   **Sistem Autentikasi Ganda**:
    *   *Web Admin*: Menggunakan **Session & Cookie** bawaan (menggunakan scaffolding Laravel Breeze untuk proteksi dashboard).
    *   *Mobile/API*: Menggunakan **Custom API Token** (autentikasi bearer token mandiri yang dicocokkan langsung ke sistem *Cache* database).
*   **Deployment Configuration**: Dilengkapi file `vercel.json` dan optimasi direktori `/tmp` agar siap dijalankan secara serverless di **Vercel**.

---

## 1. Jantung API: Login & Pembuatan Token Dinamis

Ketika klien eksternal (Postman/Mobile App) ingin login untuk mendapatkan akses API, prosesnya ditangani oleh blok kode ini.

*   **Lokasi File Utama**: [app/Http/Controllers/ApiController.php](file:///c:/xampp/htdocs/db_toko_online/modernshop-pro/app/Http/Controllers/ApiController.php)
*   **Method**: `login(Request $request)`
*   **Cara Kerja Kode**:
    1.  **Validasi Database**: Sistem mengambil baris dari tabel `users` tempat `email` cocok dengan inputan.
    2.  **Cek Password**: Menggunakan `Hash::check($request->password, $user->password)`.
    3.  **Membuat Token**: Jika cocok, kode `Str::random(60)` dijalankan untuk menghasilkan string acak rahasia sepanjang 60 karakter.
    4.  **Penyimpanan Cache**: Kode yang mengunci token adalah `Cache::put('api_token_' . $token, $user->id, now()->addMinutes(60));`. Ini berarti token disimpan di dalam **Laravel Cache** (bukan di database MySQL) dan akan otomatis musnah setelah 60 menit. Sistem merespon dengan JSON berisi token tersebut.

## 2. Satpam API: Middleware Validasi Token

Setelah klien mendapat token, mereka menempelkannya di *Header HTTP* (`Authorization: Bearer <token>`). Siapa yang mengecek ini?

*   **Lokasi File Utama**: [app/Http/Middleware/ApiTokenAuthenticate.php](file:///c:/xampp/htdocs/db_toko_online/modernshop-pro/app/Http/Middleware/ApiTokenAuthenticate.php)
*   **Cara Kerja Kode**:
    1.  **Ekstraksi Token**: Menggunakan `$token = $request->bearerToken();` untuk mengambil string token dari *Header*.
    2.  **Validasi ke Cache**: Baris kode `Cache::has('api_token_' . $token)` bertugas mengecek apakah token ini ada di memori *server*.
    3.  **Pemblokiran**: Jika `Cache::has` bernilai *false*, *middleware* ini langsung melempar respon `401 Unauthorized` dan memblokir akses ke baris kode selanjutnya. Jika *true*, permintaan diteruskan ke Controller tujuan.

## 3. Otentikasi Admin Web (Session-Based)

Panel admin yang Anda akses di peramban web (*browser*) tidak menggunakan token API, melainkan **Cookie Session** bawaan Laravel Breeze.

*   **Lokasi File Utama**: [app/Http/Controllers/Auth/AuthenticatedSessionController.php](file:///c:/xampp/htdocs/db_toko_online/modernshop-pro/app/Http/Controllers/Auth/AuthenticatedSessionController.php)
*   **Cara Kerja Kode**: 
    1. Saat Anda mengetik *login* di web form, file `LoginRequest.php` memanggil `Auth::attempt(...)`.
    2. Laravel mengecek MySQL. Jika sukses, ia mengeksekusi `$request->session()->regenerate();` untuk membuat sesi aman terenkripsi yang ditempelkan di *browser* Anda sebagai *Cookie* (bernama `modernshop_session`). Cookie inilah yang menjaga Anda tetap *login*.

## 4. Pusat Data Web: Pengumpulan Statistik Dashboard

Saat Anda membuka halaman Dashboard, ada jembatan data yang menghubungkan MySQL dengan tampilan Vue Anda.

*   **Lokasi File Utama**: [app/Http/Controllers/DashboardController.php](file:///c:/xampp/htdocs/db_toko_online/modernshop-pro/app/Http/Controllers/DashboardController.php)
*   **Method**: `index()`
*   **Cara Kerja Kode**:
    1.  **Kueri Eloquent ORM**: Menggunakan perintah PHP untuk berbicara dengan MySQL. Contoh: `Produk::where('stok', '<=', 5)->get()` otomatis diterjemahkan menjadi kueri rahasia `SELECT * FROM produk WHERE stok <= 5`.
    2.  **Pengiriman via Inertia**: Baris `return Inertia::render('Dashboard', [ 'stats' => ... ])` membungkus semua hasil kueri tadi menjadi format JSON ringan dan mengirimkannya ke sisi *frontend* (Vue) melalui jaringan.

## 5. Antarmuka Interaktif & Vue Javascript (Frontend)

File ini adalah "layar tancap" yang bereaksi terhadap klik Anda dan menggambar grafik dari data Controller.

*   **Lokasi File Utama**: [resources/js/Pages/Dashboard.vue](file:///c:/xampp/htdocs/db_toko_online/modernshop-pro/resources/js/Pages/Dashboard.vue)
*   **Cara Kerja Kode**:
    1.  **Penerimaan Data (`defineProps`)**: Variabel `props` dideklarasikan untuk menangkap objek JSON yang dikirim oleh `DashboardController`.
    2.  **Logika Grafik (`chartData`)**: Logika Javascript menggunakan komponen `vue-chartjs` untuk memetakan data `props.revenueTrend` (tanggal ke sumbu X, total harga ke sumbu Y).
    3.  **Logika Aksi (`restockProduct`)**: Ketika tombol `+` ditekan, fungsi javascript `prompt` dipanggil. Hasil input angka dikirimkan *balik* ke server dengan `router.patch(route('products.restock', id), { tambahan_stok: amount })`. 
    4.  **Inertia Router**: Fungsi `router.patch` ini bekerja seperti AJAX; ia berbisik ke server dan memperbarui *state* layar tanpa membuat *browser* menjadi kedap-kedip me-*refresh* tab.

## 6. Model Database (Penghubung Tabel)

Laravel menggunakan "Model" untuk merepresentasikan setiap tabel MySQL.

*   **Lokasi Kumpulan File**: [app/Models/](file:///c:/xampp/htdocs/db_toko_online/modernshop-pro/app/Models) (Contoh: `Pesanan.php`, `Produk.php`)
*   **Cara Kerja Kode**: 
    File-file ini menentukan bagaimana tabel saling terkait. Misalnya, di `Pesanan.php` terdapat fungsi `pelanggan()`. Di dalamnya tertulis `$this->belongsTo(Pelanggan::class, 'id_pelanggan')`. Kode pendek inilah yang memungkinkan Controller menarik data pesanan beserta nama pelanggan hanya dengan satu instruksi `Pesanan::with('pelanggan')`.

## 7. Kelola Transaksi (Pencarian, Filter & Detail Belanja)

Untuk melengkapi panel admin, sistem dilengkapi dengan manajemen transaksi terpusat yang menghubungkan data pelanggan dengan item belanjaannya.

*   **Lokasi File Utama**: 
    *   Routing: [routes/web.php](file:///c:/xampp/htdocs/db_toko_online/modernshop-pro/routes/web.php)
    *   Controller: [app/Http/Controllers/TransactionController.php](file:///c:/xampp/htdocs/db_toko_online/modernshop-pro/app/Http/Controllers/TransactionController.php)
    *   Frontend View: [resources/js/Pages/Transactions/Index.vue](file:///c:/xampp/htdocs/db_toko_online/modernshop-pro/resources/js/Pages/Transactions/Index.vue)
*   **Cara Kerja Kode**:
    1.  **Pengambilan Data Berelasi**: `TransactionController` menggunakan Eloquent `Pesanan::with(['pelanggan', 'detailPesanan.produk'])` untuk menarik data transaksi beserta detail produk yang dibeli dan identitas pembeli dalam satu kueri database.
    2.  **Pencarian & Filter Terintegrasi**: Controller menyaring data berdasarkan parameter query string `search` (ID pesanan / nama pelanggan) dan `status` (menyesuaikan status transaksi).
    3.  **Halaman Berpaginasi (Pagination)**: Menggunakan method `paginate(10)` untuk membatasi jumlah data per halaman demi menjaga performa loading database.
    4.  **Modal Detail Invoice (Frontend)**: Komponen Vue menangkap seluruh relasi `detail_pesanan` dan menampilkannya dalam bentuk modal struk belanja/invoice terperinci saat tombol "Detail" diklik oleh admin.

---

**Kesimpulan:**
Seluruh sistem terikat dalam alur kerja MVC (*Model-View-Controller*) klasik yang dipermodern dengan SPAs (*Single Page Applications*). **Routing** mengarahkan lalu lintas internet, **Middleware** bertindak sebagai pos satpam pemeriksa token/cookie, **Controller** sebagai otak pemroses data MySQL, dan **Vue + Inertia** sebagai layar canggih tanpa *refresh*.
