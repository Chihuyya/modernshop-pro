<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();

function logout() {
    router.post(route('logout'));
}
</script>

<template>
    <div class="bg-surface text-on-surface font-sans antialiased">
        <div class="flex h-screen overflow-hidden">
            <aside class="w-64 bg-slate-900 text-white flex flex-col z-20 shrink-0 border-r border-slate-800">
                <div class="p-6 flex items-center gap-3 border-b border-slate-800">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                        <span class="material-symbols-outlined text-2xl">shopping_cart</span>
                    </div>
                    <div>
                        <h1 class="font-bold text-lg leading-none tracking-tight text-white">MODERN<span class="text-blue-500">SHOP</span></h1>
                        <span class="text-[11px] text-slate-500 font-medium tracking-wider uppercase mt-1 block">Panel Admin</span>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-1.5">
                    <Link
                        :href="route('dashboard')"
                        class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-sm transition-all duration-200"
                        :class="page.url.startsWith('/dashboard') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white font-medium'"
                    >
                        <span class="material-symbols-outlined text-[22px]">dashboard</span>
                        <span>Dashboard Ringkasan</span>
                    </Link>

                    <Link
                        :href="route('products.index')"
                        class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-sm transition-all duration-200"
                        :class="page.url.startsWith('/products') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white font-medium'"
                    >
                        <span class="material-symbols-outlined text-[22px]">inventory_2</span>
                        <span>Manajemen Produk</span>
                    </Link>

                    <Link
                        :href="route('transactions.index')"
                        class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-sm transition-all duration-200"
                        :class="page.url.startsWith('/transactions') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white font-medium'"
                    >
                        <span class="material-symbols-outlined text-[22px]">receipt_long</span>
                        <span>Kelola Transaksi</span>
                    </Link>

                    <Link
                        :href="route('customers.index')"
                        class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-sm transition-all duration-200"
                        :class="page.url.startsWith('/customers') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white font-medium'"
                    >
                        <span class="material-symbols-outlined text-[22px]">people</span>
                        <span>Data Pelanggan</span>
                    </Link>
                </div>

                <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                    <button
                        @click="logout"
                        class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 hover:text-red-300 font-medium text-sm transition-all duration-200"
                    >
                        <span class="material-symbols-outlined text-[22px]">logout</span>
                        <span>Keluar Sistem</span>
                    </button>
                </div>
            </aside>

            <div class="flex-1 flex flex-col min-w-0 bg-surface">
                <header class="h-16 border-b border-outline-variant bg-surface-container-lowest px-8 flex items-center justify-between shrink-0">
                    <span class="text-sm font-semibold text-on-surface-variant">Sistem Manajemen Toko</span>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-200">A</div>
                        <span class="text-sm font-semibold text-on-surface">{{ page.props.auth.user.name }}</span>
                    </div>
                </header>

                <main class="flex-1 overflow-y-auto p-8 space-y-8">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
