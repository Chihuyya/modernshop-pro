<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
});

const showModal = ref(false);
const editingProduct = ref(null);

const form = useForm({
    id_kategori: '',
    nama_produk: '',
    harga: '',
    stok: '',
    url_gambar: '',
});

const modalTitle = computed(() => (editingProduct.value ? 'Ubah Detail Barang' : 'Tambah Produk Baru'));

function rupiah(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value || 0);
}

function getSmartImage(namaProduk) {
    if (!namaProduk) {
        return 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?w=400&fit=crop&q=60';
    }

    const nama = namaProduk.toLowerCase().trim();

    if (nama.includes('lego')) return 'https://i.pinimg.com/736x/bc/67/11/bc6711d6fb6973fd4d9a255ab3d932b2.jpg';
    if (nama.includes('novel') || nama.includes('buku')) return 'https://i.pinimg.com/736x/72/14/9d/72149d1fb83e6946db7d9130b97b1f46.jpg';
    if (nama.includes('kopi')) return 'https://i.pinimg.com/736x/9a/19/e2/9a19e27144d1f594e69eaf116baf4513.jpg';
    if (nama.includes('keripik') || nama.includes('kentang')) return 'https://i.pinimg.com/1200x/32/ae/f5/32aef5a4ca9fe9a9d1236c379df1c8f7.jpg';
    if (nama.includes('dress') || nama.includes('bunga')) return 'https://i.pinimg.com/736x/a4/63/6f/a4636f1c59e7d69c813ffe59491912cb.jpg';
    if (nama.includes('hoodie') || nama.includes('jaket')) return 'https://i.pinimg.com/736x/e3/c0/38/e3c038cd126db056f21cfc86b4c5a2d5.jpg';
    if (nama.includes('futsal')) return 'https://i.pinimg.com/736x/75/cc/bc/75ccbc57a7bf7cc8f5b869dd9964b875.jpg';
    if (nama.includes('compass')) return 'https://i.pinimg.com/736x/93/2f/8b/932f8b506a10fc9352cb73b4484431cf.jpg';
    if (nama.includes('flanel') || nama.includes('kemeja')) return 'https://i.pinimg.com/736x/b6/c5/04/b6c504a7fcac18bbcc8ed85febbd3f8d.jpg';
    if (nama.includes('samsung') || nama.includes('smartphone')) return 'https://i.pinimg.com/736x/0e/c2/e5/0ec2e5baa1f277c474343b7b4120989b.jpg';

    return 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?w=400&fit=crop&q=60';
}

function imageFor(product) {
    return product.url_gambar || getSmartImage(product.nama_produk);
}

function openAddModal() {
    editingProduct.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEditModal(product) {
    editingProduct.value = product;
    form.clearErrors();
    form.id_kategori = product.id_kategori;
    form.nama_produk = product.nama_produk;
    form.harga = product.harga;
    form.stok = product.stok;
    form.url_gambar = product.url_gambar || '';
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    form.clearErrors();
}

function saveProduct() {
    if (editingProduct.value) {
        form.put(route('products.update', editingProduct.value.id_produk), {
            preserveScroll: true,
            onSuccess: closeModal,
        });
        return;
    }

    form.post(route('products.store'), {
        preserveScroll: true,
        onSuccess: closeModal,
    });
}

function deleteProduct(product) {
    if (!confirm('Hapus produk ini secara permanen?')) return;

    form.delete(route('products.destroy', product.id_produk), {
        preserveScroll: true,
    });
}
</script>

<template>
    <AdminLayout>
        <section class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-on-surface tracking-tight">Katalog Produk Modern PRO</h2>
                    <p class="text-sm text-on-surface-variant mt-1">Daftar semua koleksi produk toko Anda yang terdaftar di database MySQL.</p>
                </div>

                <button
                    @click="openAddModal"
                    class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-xl font-semibold text-sm shadow-md shadow-blue-600/10 transition-all"
                >
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    <span>Tambah Produk Baru</span>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div v-if="props.products.length === 0" class="col-span-full p-8 text-center text-on-surface-variant">
                    Belum ada barang di database.
                </div>

                <div
                    v-for="product in props.products"
                    :key="product.id_produk"
                    class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-all duration-200"
                >
                    <div class="w-full h-48 bg-slate-100 relative overflow-hidden shrink-0 border-b border-outline-variant">
                        <img
                            :src="imageFor(product)"
                            :alt="product.nama_produk"
                            class="w-full h-full object-cover"
                            @error="$event.target.src = 'https://placehold.co/400x300?text=Gambar+Produk'"
                        >
                        <span class="absolute top-3 left-3 px-2.5 py-1 bg-slate-900/80 text-white rounded-md text-[11px] font-bold tracking-wider">
                            ID #{{ product.id_produk }}
                        </span>
                    </div>

                    <div class="p-5 flex flex-col flex-1 space-y-3">
                        <div class="flex-1">
                            <span class="px-2 py-0.5 bg-surface-container text-on-surface-variant rounded text-[10px] font-bold border border-outline-variant uppercase tracking-wider">
                                {{ product.kategori?.nama_kategori || `Kategori ${product.id_kategori}` }}
                            </span>
                            <h4 class="font-bold text-on-surface text-base mt-2 line-clamp-2 leading-tight">{{ product.nama_produk }}</h4>
                        </div>

                        <div class="flex items-baseline justify-between border-t border-outline-variant/60 pt-3">
                            <span class="text-lg font-extrabold text-blue-600">{{ rupiah(product.harga) }}</span>
                            <span class="text-xs font-semibold" :class="product.stok > 0 ? 'text-on-surface-variant' : 'text-red-600 font-bold'">
                                Stok: {{ product.stok }} unit
                            </span>
                        </div>

                        <div class="flex gap-2 pt-1 border-t border-outline-variant/40">
                            <button
                                @click="openEditModal(product)"
                                class="flex-1 flex items-center justify-center gap-1.5 py-2 border border-blue-200 hover:bg-blue-50 text-blue-600 rounded-xl text-xs font-bold transition-colors"
                            >
                                <span class="material-symbols-outlined text-[16px]">edit</span>
                                Edit
                            </button>

                            <button
                                @click="deleteProduct(product)"
                                class="p-2 border border-red-200 hover:bg-red-50 text-red-600 rounded-xl transition-colors"
                                aria-label="Hapus produk"
                            >
                                <span class="material-symbols-outlined text-[16px]">delete</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div v-if="showModal" class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-surface-container-lowest rounded-2xl w-full max-w-md shadow-xl border border-outline-variant p-6 space-y-5">
                <div class="flex justify-between items-center border-b border-outline-variant pb-3">
                    <h3 class="text-lg font-bold text-on-surface">{{ modalTitle }}</h3>
                    <button @click="closeModal" class="text-on-surface-variant hover:text-on-surface p-1 rounded-lg hover:bg-surface-container transition-colors">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <form @submit.prevent="saveProduct" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1.5 tracking-wider uppercase">Nama Barang</label>
                        <input v-model="form.nama_produk" type="text" required class="w-full rounded-xl border-outline-variant bg-surface focus:border-blue-500 focus:ring-blue-500/20 text-sm p-3">
                        <p v-if="form.errors.nama_produk" class="text-xs text-red-600 mt-1">{{ form.errors.nama_produk }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1.5 tracking-wider uppercase">Kategori</label>
                        <select v-model="form.id_kategori" required class="w-full rounded-xl border-outline-variant bg-surface focus:border-blue-500 focus:ring-blue-500/20 text-sm p-3">
                            <option value="">Pilih kategori</option>
                            <option v-for="category in props.categories" :key="category.id_kategori" :value="category.id_kategori">
                                {{ category.nama_kategori }}
                            </option>
                        </select>
                        <p v-if="form.errors.id_kategori" class="text-xs text-red-600 mt-1">{{ form.errors.id_kategori }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1.5 tracking-wider uppercase">Custom Link URL Gambar</label>
                        <input v-model="form.url_gambar" type="url" placeholder="Kosongkan jika ingin auto-gambar dari sistem" class="w-full rounded-xl border-outline-variant bg-surface focus:border-blue-500 focus:ring-blue-500/20 text-sm p-3">
                        <p v-if="form.errors.url_gambar" class="text-xs text-red-600 mt-1">{{ form.errors.url_gambar }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-on-surface-variant mb-1.5 tracking-wider uppercase">Harga Jual</label>
                            <input v-model="form.harga" type="number" required min="0" class="w-full rounded-xl border-outline-variant bg-surface focus:border-blue-500 focus:ring-blue-500/20 text-sm p-3">
                            <p v-if="form.errors.harga" class="text-xs text-red-600 mt-1">{{ form.errors.harga }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-on-surface-variant mb-1.5 tracking-wider uppercase">Stok Gudang</label>
                            <input v-model="form.stok" type="number" required min="0" class="w-full rounded-xl border-outline-variant bg-surface focus:border-blue-500 focus:ring-blue-500/20 text-sm p-3">
                            <p v-if="form.errors.stok" class="text-xs text-red-600 mt-1">{{ form.errors.stok }}</p>
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold p-3.5 rounded-xl shadow-md transition-colors text-sm disabled:opacity-60"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan ke Database' }}
                    </button>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
