<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Login Admin" />

    <div class="bg-slate-100 font-sans flex items-center justify-center min-h-screen px-4">
        <div class="bg-white p-8 rounded-2xl shadow-md w-full max-w-md border border-slate-200">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-slate-800">MODERN<span class="text-blue-600">SHOP</span></h1>
                <p class="text-sm text-slate-500 mt-1">Silakan masuk ke panel admin</p>
            </div>

            <div v-if="status" class="mb-4 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="username" class="block text-xs font-bold text-slate-600 uppercase mb-1">Username</label>
                    <input
                        id="username"
                        v-model="form.username"
                        type="text"
                        required
                        autofocus
                        autocomplete="username"
                        class="w-full rounded-xl border-slate-300 text-sm p-3 focus:ring-blue-500 focus:border-blue-500"
                    >
                    <p v-if="form.errors.username" class="mt-1 text-xs text-red-600">{{ form.errors.username }}</p>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-600 uppercase mb-1">Password</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="w-full rounded-xl border-slate-300 text-sm p-3 focus:ring-blue-500 focus:border-blue-500"
                    >
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input v-model="form.remember" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span>Ingat sesi admin</span>
                </label>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold p-3 rounded-xl text-sm transition-colors disabled:opacity-60"
                >
                    {{ form.processing ? 'Memverifikasi...' : 'Masuk Sistem' }}
                </button>

                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span>Demo: admin / admin123</span>
                    <Link v-if="canResetPassword" :href="route('password.request')" class="text-blue-600 hover:text-blue-700 font-semibold">
                        Lupa password?
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>
