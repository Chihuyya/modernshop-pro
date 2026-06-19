<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    customers: Array
});

function rupiah(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value || 0);
}
</script>

<template>
    <Head title="Data Pelanggan" />
    <AdminLayout>
        <section class="space-y-6 pb-12">
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-2xl font-bold text-on-surface tracking-tight">Manajemen Pelanggan</h2>
                    <p class="text-sm text-on-surface-variant mt-1">Daftar pelanggan setia Anda beserta metrik pembelanjaan mereka.</p>
                </div>
            </div>

            <!-- Tabel Pelanggan -->
            <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-on-surface-variant border-b border-outline-variant">
                                <th class="pb-3 font-semibold">Nama Pelanggan</th>
                                <th class="pb-3 font-semibold">Kontak</th>
                                <th class="pb-3 font-semibold text-center">Jumlah Pesanan</th>
                                <th class="pb-3 font-semibold text-right">Total Dibelanjakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="customer in customers" :key="customer.id_pelanggan" class="border-b border-outline-variant last:border-0 hover:bg-surface-variant/30 transition-colors">
                                <td class="py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                                            {{ customer.nama_pelanggan.charAt(0).toUpperCase() }}
                                        </div>
                                        <span class="font-bold text-on-surface">{{ customer.nama_pelanggan }}</span>
                                    </div>
                                </td>
                                <td class="py-4">
                                    <p class="text-on-surface">{{ customer.email }}</p>
                                    <p class="text-xs text-on-surface-variant mt-0.5">{{ customer.telepon || 'Tidak ada no telepon' }}</p>
                                </td>
                                <td class="py-4 text-center">
                                    <span class="px-3 py-1 bg-surface-variant rounded-full font-semibold text-on-surface-variant">
                                        {{ customer.jumlah_pesanan }}x Order
                                    </span>
                                </td>
                                <td class="py-4 text-right font-bold text-primary">
                                    {{ rupiah(customer.total_belanja) }}
                                </td>
                            </tr>
                            <tr v-if="customers.length === 0">
                                <td colspan="4" class="py-12 text-center text-on-surface-variant">
                                    Belum ada data pelanggan yang terdaftar.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
