<script setup>
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js';
import { router } from '@inertiajs/vue3';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

const props = defineProps({
    stats: Object,
    lowStockProducts: Array,
    transactionStatus: Object,
    recentTransactions: Array,
    topProducts: Array,
    revenueTrend: Array,
});

function rupiah(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value || 0);
}

// Chart Data Setup
const chartData = computed(() => ({
    labels: props.revenueTrend.map(item => {
        const d = new Date(item.date);
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    }),
    datasets: [
        {
            label: 'Pendapatan Harian',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderColor: '#3b82f6',
            data: props.revenueTrend.map(item => item.total),
            fill: true,
            tension: 0.4,
            borderWidth: 2,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: '#3b82f6',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
        }
    ]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: function(context) {
                    return rupiah(context.parsed.y);
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: function(value) {
                    return rupiah(value);
                }
            }
        }
    }
};

const pesananSelesai = computed(() => props.transactionStatus['Selesai'] || 0);
const pesananPending = computed(() => props.transactionStatus['Pending'] || 0);

// Interactive Actions
const restockProduct = (item) => {
    const amount = prompt(`Tambahkan berapa unit stok untuk ${item.nama_produk}?`, "10");
    if (amount !== null && amount.trim() !== "" && !isNaN(amount)) {
        router.patch(route('products.restock', item.id_produk), {
            tambahan_stok: parseInt(amount)
        }, {
            preserveScroll: true
        });
    }
};

const updateStatus = (trx) => {
    const statusOpts = ["Pending", "Diproses", "Dikirim", "Selesai", "Dibatalkan"];
    let promptText = `Ubah status pesanan #${trx.id_pesanan}?\n\nPilihan yang tersedia:\n- Pending\n- Diproses\n- Dikirim\n- Selesai\n- Dibatalkan\n\nKetik status baru:`;
    const newStatus = prompt(promptText, trx.status);
    
    if (newStatus !== null && statusOpts.includes(newStatus)) {
        router.put(route('transactions.status', trx.id_pesanan), {
            status: newStatus
        }, {
            preserveScroll: true
        });
    } else if (newStatus !== null) {
        alert("Status tidak valid! Harus sesuai dengan pilihan yang tersedia (Perhatikan huruf kapital di awal).");
    }
};
</script>

<template>
    <AdminLayout>
        <section class="space-y-6 pb-12">
            <div>
                <h2 class="text-2xl font-bold text-on-surface tracking-tight">Pusat Komando Toko</h2>
                <p class="text-sm text-on-surface-variant mt-1">Pantau performa dan kelola pesanan atau stok dengan cepat (1-Click Actions).</p>
            </div>

            <!-- Top Metrik -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total Pendapatan -->
                <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant shadow-sm flex items-center gap-5 col-span-1 md:col-span-2">
                    <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center border border-emerald-100">
                        <span class="material-symbols-outlined text-3xl">payments</span>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider block">Total Pendapatan</span>
                        <h3 class="text-2xl font-bold text-on-surface mt-1">{{ rupiah(stats.total_pendapatan) }}</h3>
                    </div>
                </div>

                <!-- Total Pesanan -->
                <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant shadow-sm flex items-center gap-5">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center border border-blue-100">
                        <span class="material-symbols-outlined text-2xl">receipt_long</span>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider block">Total Pesanan</span>
                        <h3 class="text-xl font-bold text-on-surface mt-1">{{ stats.jumlah_transaksi }} Transaksi</h3>
                    </div>
                </div>

                <!-- Status Distribusi -->
                <div class="bg-surface-container-lowest p-5 rounded-2xl border border-outline-variant shadow-sm flex flex-col justify-center">
                     <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider block mb-3">Status Pesanan</span>
                     <div class="flex gap-4">
                         <div class="flex items-center gap-2">
                             <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                             <span class="text-sm font-medium">{{ pesananSelesai }} Selesai</span>
                         </div>
                         <div class="flex items-center gap-2">
                             <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                             <span class="text-sm font-medium">{{ pesananPending }} Pending</span>
                         </div>
                     </div>
                </div>
            </div>

            <!-- Main Grid Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Kiri: Chart & Tabel -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Line Chart -->
                    <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant shadow-sm">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-on-surface">Tren Pendapatan 7 Hari Terakhir</h3>
                        </div>
                        <div class="h-72 w-full">
                            <Line :data="chartData" :options="chartOptions" />
                        </div>
                    </div>

                    <!-- Transaksi Terbaru -->
                    <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant shadow-sm">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-on-surface">Transaksi Terbaru</h3>
                            <span class="text-sm text-on-surface-variant font-medium">Klik status untuk mengubahnya</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="text-on-surface-variant border-b border-outline-variant">
                                        <th class="pb-3 font-semibold">ID</th>
                                        <th class="pb-3 font-semibold">Tanggal</th>
                                        <th class="pb-3 font-semibold">Pelanggan</th>
                                        <th class="pb-3 font-semibold">Status (Aksi)</th>
                                        <th class="pb-3 font-semibold text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="trx in recentTransactions" :key="trx.id_pesanan" class="border-b border-outline-variant last:border-0 hover:bg-surface-variant/50 transition-colors">
                                        <td class="py-4">#{{ trx.id_pesanan }}</td>
                                        <td class="py-4">{{ trx.tanggal_pesanan }}</td>
                                        <td class="py-4 font-medium">{{ trx.nama_pelanggan }}</td>
                                        <td class="py-4">
                                            <button @click="updateStatus(trx)" class="px-3 py-1 rounded-full text-xs font-bold shadow-sm transition-transform hover:scale-105"
                                                :class="{
                                                    'bg-emerald-100 text-emerald-800 border border-emerald-200': trx.status === 'Selesai',
                                                    'bg-amber-100 text-amber-800 border border-amber-200': trx.status === 'Pending',
                                                    'bg-blue-100 text-blue-800 border border-blue-200': trx.status === 'Diproses',
                                                    'bg-purple-100 text-purple-800 border border-purple-200': trx.status === 'Dikirim',
                                                    'bg-error-container text-on-error-container border border-error': trx.status === 'Dibatalkan'
                                                }">
                                                {{ trx.status }} <span class="material-symbols-outlined text-[10px] ml-1 align-middle">edit</span>
                                            </button>
                                        </td>
                                        <td class="py-4 text-right font-bold text-primary">{{ rupiah(trx.total_harga) }}</td>
                                    </tr>
                                    <tr v-if="recentTransactions.length === 0">
                                        <td colspan="5" class="py-8 text-center text-on-surface-variant">Belum ada transaksi terbaru.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Kanan: Produk Terlaris & Stok Menipis -->
                <div class="space-y-6">
                    
                    <!-- Stok Menipis -->
                    <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant shadow-sm border-t-4 border-t-error">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="material-symbols-outlined text-error">warning</span>
                            <h3 class="text-lg font-bold text-on-surface">Peringatan Stok</h3>
                        </div>
                        <div class="space-y-4">
                            <div v-for="item in lowStockProducts" :key="item.id_produk" class="flex justify-between items-center p-3 bg-surface-variant rounded-xl border border-outline-variant/50 hover:border-error transition-colors">
                                <div class="min-w-0 pr-3">
                                    <p class="text-sm font-semibold text-on-surface truncate">{{ item.nama_produk }}</p>
                                    <p class="text-xs text-error font-medium mt-0.5">Sisa {{ item.stok }} unit</p>
                                </div>
                                <button @click="restockProduct(item)" title="Quick Restock" class="w-10 h-10 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-md hover:bg-primary/90 transition-transform hover:scale-105 flex-shrink-0">
                                    <span class="material-symbols-outlined text-lg">add</span>
                                </button>
                            </div>
                            <div v-if="lowStockProducts.length === 0" class="text-center py-4 text-sm text-on-surface-variant flex flex-col items-center gap-2">
                                <span class="material-symbols-outlined text-4xl text-emerald-500">check_circle</span>
                                Semua stok produk dalam batas aman.
                            </div>
                        </div>
                    </div>

                    <!-- Produk Terlaris -->
                    <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant shadow-sm">
                        <h3 class="text-lg font-bold text-on-surface mb-6">Produk Terlaris</h3>
                        <div class="space-y-5">
                            <div v-for="(item, index) in topProducts" :key="item.id_produk" class="flex items-center gap-4">
                                <div class="w-8 font-bold text-xl text-on-surface-variant flex-shrink-0 text-center">
                                    #{{ index + 1 }}
                                </div>
                                <div class="w-12 h-12 bg-surface-variant rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0 border border-outline-variant">
                                    <img v-if="item.url_gambar" :src="item.url_gambar" class="w-full h-full object-cover" />
                                    <span v-else class="material-symbols-outlined text-on-surface-variant">inventory_2</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-on-surface truncate">{{ item.nama_produk }}</p>
                                    <p class="text-xs text-primary font-medium mt-0.5">{{ item.total_terjual }} unit terjual</p>
                                </div>
                            </div>
                            <div v-if="topProducts.length === 0" class="text-center py-4 text-sm text-on-surface-variant">
                                Belum ada data penjualan produk.
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </AdminLayout>
</template>
