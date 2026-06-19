<script setup>
import { ref, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    transactions: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const selectedTransaction = ref(null);
const showDetailModal = ref(false);

function rupiah(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value || 0);
}

// Menjalankan pencarian & filter ke backend
function applyFilters() {
    router.get(route('transactions.index'), {
        search: search.value,
        status: status.value
    }, {
        preserveState: true,
        replace: true
    });
}

function clearFilters() {
    search.value = '';
    status.value = '';
    router.get(route('transactions.index'), {}, {
        preserveState: true,
        replace: true
    });
}

// Buka Modal Rincian Belanja
function viewDetail(trx) {
    selectedTransaction.value = trx;
    showDetailModal.value = true;
}

// Update Status Pesanan
function updateStatus(trx) {
    const statusOpts = ["Pending", "Diproses", "Dikirim", "Selesai", "Dibatalkan"];
    let promptText = `Ubah status pesanan #${trx.id_pesanan}?\n\nPilihan yang tersedia:\n- Pending\n- Diproses\n- Dikirim\n- Selesai\n- Dibatalkan\n\nKetik status baru:`;
    const newStatus = prompt(promptText, trx.status);
    
    if (newStatus !== null && statusOpts.includes(newStatus)) {
        router.put(route('transactions.status', trx.id_pesanan), {
            status: newStatus
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Perbarui data transaksi terpilih jika modal sedang terbuka
                if (selectedTransaction.value && selectedTransaction.value.id_pesanan === trx.id_pesanan) {
                    selectedTransaction.value.status = newStatus;
                }
            }
        });
    } else if (newStatus !== null) {
        alert("Status tidak valid! Harus sesuai dengan pilihan yang tersedia (Perhatikan huruf kapital di awal).");
    }
}
</script>

<template>
    <Head title="Kelola Transaksi" />
    <AdminLayout>
        <section class="space-y-6 pb-12">
            <div class="flex justify-between items-end flex-wrap gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-on-surface tracking-tight">Kelola Transaksi</h2>
                    <p class="text-sm text-on-surface-variant mt-1">Kelola data pesanan masuk, pantau status pengiriman, dan periksa detail pembelian pelanggan.</p>
                </div>
            </div>

            <!-- Kontrol Filter & Pencarian -->
            <div class="bg-surface-container-lowest p-5 rounded-2xl border border-outline-variant shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-1 flex-wrap items-center gap-3">
                    <!-- Pencarian -->
                    <div class="relative min-w-[280px] flex-1 md:flex-initial">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                        <input
                            v-model="search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Cari ID Pesanan / Nama Pelanggan..."
                            class="w-full bg-surface-container-low border border-outline-variant text-on-surface rounded-xl pl-11 pr-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                        />
                    </div>

                    <!-- Filter Status -->
                    <div class="relative min-w-[160px]">
                        <select
                            v-model="status"
                            @change="applyFilters"
                            class="w-full bg-surface-container-low border border-outline-variant text-on-surface rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all appearance-none cursor-pointer"
                        >
                            <option value="">Semua Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Diproses">Diproses</option>
                            <option value="Dikirim">Dikirim</option>
                            <option value="Selesai">Selesai</option>
                            <option value="Dibatalkan">Dibatalkan</option>
                        </select>
                    </div>

                    <!-- Tombol Cari -->
                    <button
                        @click="applyFilters"
                        class="px-5 py-2.5 bg-primary text-on-primary rounded-xl text-sm font-semibold hover:bg-primary/95 transition-all shadow-sm"
                    >
                        Terapkan
                    </button>

                    <!-- Reset Filter -->
                    <button
                        v-if="search || status"
                        @click="clearFilters"
                        class="px-4 py-2.5 text-error hover:bg-error-container/20 rounded-xl text-sm font-medium transition-all"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <!-- Tabel Transaksi -->
            <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-on-surface-variant border-b border-outline-variant">
                                <th class="pb-3 font-semibold">ID</th>
                                <th class="pb-3 font-semibold">Tanggal</th>
                                <th class="pb-3 font-semibold">Pelanggan</th>
                                <th class="pb-3 font-semibold">Status (Aksi)</th>
                                <th class="pb-3 font-semibold text-right">Total Harga</th>
                                <th class="pb-3 font-semibold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="trx in transactions.data" :key="trx.id_pesanan" class="border-b border-outline-variant last:border-0 hover:bg-surface-variant/30 transition-colors">
                                <td class="py-4 font-mono font-semibold">#{{ trx.id_pesanan }}</td>
                                <td class="py-4">{{ new Date(trx.tanggal_pesanan).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</td>
                                <td class="py-4">
                                    <div class="font-bold text-on-surface">{{ trx.pelanggan ? trx.pelanggan.nama_pelanggan : 'Umum' }}</div>
                                    <div class="text-xs text-on-surface-variant">{{ trx.pelanggan ? trx.pelanggan.email : '-' }}</div>
                                </td>
                                <td class="py-4">
                                    <button @click="updateStatus(trx)" class="px-3 py-1 rounded-full text-xs font-bold shadow-sm transition-transform hover:scale-105 inline-flex items-center gap-1.5"
                                        :class="{
                                            'bg-emerald-100 text-emerald-800 border border-emerald-200': trx.status === 'Selesai',
                                            'bg-amber-100 text-amber-800 border border-amber-200': trx.status === 'Pending',
                                            'bg-blue-100 text-blue-800 border border-blue-200': trx.status === 'Diproses',
                                            'bg-purple-100 text-purple-800 border border-purple-200': trx.status === 'Dikirim',
                                            'bg-error-container text-on-error-container border border-error': trx.status === 'Dibatalkan'
                                        }">
                                        {{ trx.status }} 
                                        <span class="material-symbols-outlined text-[11px] align-middle">edit</span>
                                    </button>
                                </td>
                                <td class="py-4 text-right font-bold text-primary">
                                    {{ rupiah(trx.total_harga) }}
                                </td>
                                <td class="py-4 text-center">
                                    <button
                                        @click="viewDetail(trx)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-surface-container-high hover:bg-surface-container-highest text-on-surface rounded-lg text-xs font-semibold transition-all"
                                    >
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                        Detail
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="transactions.data.length === 0">
                                <td colspan="6" class="py-12 text-center text-on-surface-variant">
                                    Tidak ditemukan data transaksi yang cocok dengan filter pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                <div v-if="transactions.total > transactions.per_page" class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-xs text-on-surface-variant font-medium">
                        Menampilkan {{ transactions.from || 0 }} - {{ transactions.to || 0 }} dari {{ transactions.total || 0 }} transaksi
                    </div>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in transactions.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            class="px-3 py-1.5 rounded-lg text-xs transition-colors"
                            :class="{
                                'bg-primary text-on-primary font-bold': link.active,
                                'text-on-surface hover:bg-surface-variant/80': !link.active && link.url,
                                'text-on-surface-variant/30 cursor-not-allowed': !link.url
                            }"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Modal Detail Transaksi -->
        <div v-if="showDetailModal && selectedTransaction" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showDetailModal = false"></div>
            
            <!-- Modal Body -->
            <div class="bg-surface-container-lowest border border-outline-variant shadow-2xl rounded-2xl w-full max-w-2xl max-h-[85vh] flex flex-col z-10 overflow-hidden transform transition-all">
                <!-- Header -->
                <div class="p-6 border-b border-outline-variant flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-on-surface">Invoice #{{ selectedTransaction.id_pesanan }}</h3>
                        <p class="text-xs text-on-surface-variant mt-1">
                            Tanggal: {{ new Date(selectedTransaction.tanggal_pesanan).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                        </p>
                    </div>
                    <button @click="showDetailModal = false" class="w-8 h-8 rounded-full hover:bg-surface-variant flex items-center justify-center text-on-surface-variant">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Info Pelanggan & Status -->
                <div class="p-6 bg-surface-container-low grid grid-cols-2 gap-6 text-sm">
                    <div>
                        <h4 class="font-semibold text-on-surface-variant mb-2 uppercase text-[10px] tracking-wider">Informasi Pelanggan</h4>
                        <div class="space-y-1">
                            <p class="font-bold text-on-surface">{{ selectedTransaction.pelanggan ? selectedTransaction.pelanggan.nama_pelanggan : 'Umum' }}</p>
                            <p class="text-xs text-on-surface-variant">{{ selectedTransaction.pelanggan ? selectedTransaction.pelanggan.email : '-' }}</p>
                            <p class="text-xs text-on-surface-variant">{{ selectedTransaction.pelanggan?.telepon || 'No Telepon: -' }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <h4 class="font-semibold text-on-surface-variant mb-2 uppercase text-[10px] tracking-wider">Status Transaksi</h4>
                        <button @click="updateStatus(selectedTransaction)" class="px-3 py-1 rounded-full text-xs font-bold shadow-sm inline-flex items-center gap-1.5 hover:scale-105 transition-transform"
                            :class="{
                                'bg-emerald-100 text-emerald-800 border border-emerald-200': selectedTransaction.status === 'Selesai',
                                'bg-amber-100 text-amber-800 border border-amber-200': selectedTransaction.status === 'Pending',
                                'bg-blue-100 text-blue-800 border border-blue-200': selectedTransaction.status === 'Diproses',
                                'bg-purple-100 text-purple-800 border border-purple-200': selectedTransaction.status === 'Dikirim',
                                'bg-error-container text-on-error-container border border-error': selectedTransaction.status === 'Dibatalkan'
                            }">
                            {{ selectedTransaction.status }}
                            <span class="material-symbols-outlined text-[11px]">edit</span>
                        </button>
                    </div>
                </div>

                <!-- Daftar Barang Belanjaan -->
                <div class="flex-1 overflow-y-auto p-6">
                    <h4 class="font-semibold text-on-surface-variant mb-4 uppercase text-[10px] tracking-wider">Rincian Barang</h4>
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-on-surface-variant border-b border-outline-variant">
                                <th class="pb-2 font-semibold">Produk</th>
                                <th class="pb-2 font-semibold text-center">Qty</th>
                                <th class="pb-2 font-semibold text-right">Harga Satuan</th>
                                <th class="pb-2 font-semibold text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in selectedTransaction.detail_pesanan" :key="item.id_detail" class="border-b border-outline-variant/50 last:border-0">
                                <td class="py-3">
                                    <p class="font-semibold text-on-surface">{{ item.produk ? item.produk.nama_produk : 'Produk Tidak Ditemukan' }}</p>
                                    <p class="text-xs text-on-surface-variant">{{ item.produk?.kategori?.nama_kategori || '' }}</p>
                                </td>
                                <td class="py-3 text-center text-on-surface font-medium">{{ item.jumlah }}x</td>
                                <td class="py-3 text-right text-on-surface-variant">{{ rupiah(item.harga_satuan) }}</td>
                                <td class="py-3 text-right font-bold text-on-surface">{{ rupiah(item.subtotal) }}</td>
                            </tr>
                            <tr v-if="!selectedTransaction.detail_pesanan || selectedTransaction.detail_pesanan.length === 0">
                                <td colspan="4" class="py-8 text-center text-on-surface-variant text-xs">
                                    Tidak ada detail produk dalam transaksi ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Summary -->
                <div class="p-6 border-t border-outline-variant bg-surface-container-low flex justify-between items-center">
                    <span class="font-bold text-on-surface-variant">Grand Total</span>
                    <span class="text-lg font-bold text-primary">{{ rupiah(selectedTransaction.total_harga) }}</span>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
