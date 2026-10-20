<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    status: {
        type: String,
        default: null,
    },
});

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

const clientError = ref('');

const submit = () => {
    clientError.value = '';

    if (!form.login.trim()) {
        clientError.value = 'Username atau email wajib diisi.';
        return;
    }

    if (!form.password) {
        clientError.value = 'Kata sandi wajib diisi.';
        return;
    }

    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};

const fillSample = (username, password) => {
    form.login = username;
    form.password = password;
    clientError.value = '';
};
</script>

<template>
    <Head title="Masuk ke Sistem - 1dex-sybsen" />

    <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex justify-center">
                <Link href="/" class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded bg-blue-700 text-white font-semibold flex items-center justify-center text-base tracking-tight">
                        1D
                    </div>
                    <span class="text-xl font-semibold tracking-tight text-slate-900">1dex-sybsen</span>
                </Link>
            </div>
            <h1 class="mt-4 text-center text-xl font-semibold tracking-tight text-slate-900">
                Masuk ke Akun Anda
            </h1>
            <p class="mt-1 text-center text-xs text-slate-600">
                Pilih atau masukkan kredensial untuk mengakses sistem presensi sekolah
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-sm border border-slate-200 sm:rounded-lg sm:px-10">
                <!-- Status Flash Message -->
                <div v-if="status" class="mb-4 p-3 bg-blue-50 border border-blue-200 text-blue-800 rounded text-xs">
                    {{ status }}
                </div>

                <!-- Client Side Validation Alert -->
                <div v-if="clientError" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded text-xs">
                    {{ clientError }}
                </div>

                <!-- Server Side Validation Alert -->
                <div v-if="form.errors.login" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded text-xs">
                    {{ form.errors.login }}
                </div>

                <form @submit.prevent="submit" class="space-y-5" novalidate>
                    <div>
                        <label for="login" class="block text-xs font-medium text-slate-700">
                            Username atau Email
                        </label>
                        <div class="mt-1">
                            <input
                                id="login"
                                v-model="form.login"
                                type="text"
                                autocomplete="username"
                                required
                                class="w-full px-3 py-2 border border-slate-300 rounded text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition"
                                placeholder="Contoh: admin atau nama_user"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-medium text-slate-700">
                            Kata Sandi
                        </label>
                        <div class="mt-1">
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                autocomplete="current-password"
                                required
                                class="w-full px-3 py-2 border border-slate-300 rounded text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition"
                                placeholder="Masukkan kata sandi"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input
                                id="remember"
                                v-model="form.remember"
                                type="checkbox"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded"
                            />
                            <label for="remember" class="ml-2 block text-xs text-slate-700 cursor-pointer">
                                Ingat sesi saya
                            </label>
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded text-sm font-medium text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600 transition disabled:opacity-50 min-h-[44px] items-center"
                        >
                            <span v-if="form.processing">Memproses...</span>
                            <span v-else>Masuk ke Sistem</span>
                        </button>
                    </div>
                </form>

                <!-- Data Akun Uji Cepat (Mempermudah Verifikasi dan Review) -->
                <div class="mt-8 pt-6 border-t border-slate-200">
                    <p class="text-xs font-semibold text-slate-700 mb-3">Akun Uji Siap Pakai (Klik untuk Mengisi):</p>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <button
                            type="button"
                            @click="fillSample('admin', 'admin123')"
                            class="p-2 text-left bg-slate-50 hover:bg-blue-50 hover:border-blue-200 border border-slate-200 rounded transition"
                        >
                            <span class="font-semibold block text-slate-900">Admin</span>
                            <span class="text-slate-500 text-[11px]">admin / admin123</span>
                        </button>

                        <button
                            type="button"
                            @click="fillSample('wali_pplg1', 'guru123')"
                            class="p-2 text-left bg-slate-50 hover:bg-blue-50 hover:border-blue-200 border border-slate-200 rounded transition"
                        >
                            <span class="font-semibold block text-slate-900">Wali Kelas</span>
                            <span class="text-slate-500 text-[11px]">wali_pplg1 / guru123</span>
                        </button>

                        <button
                            type="button"
                            @click="fillSample('guru_piket', 'guru123')"
                            class="p-2 text-left bg-slate-50 hover:bg-blue-50 hover:border-blue-200 border border-slate-200 rounded transition"
                        >
                            <span class="font-semibold block text-slate-900">Guru Piket</span>
                            <span class="text-slate-500 text-[11px]">guru_piket / guru123</span>
                        </button>

                        <button
                            type="button"
                            @click="fillSample('guru_web', 'guru123')"
                            class="p-2 text-left bg-slate-50 hover:bg-blue-50 hover:border-blue-200 border border-slate-200 rounded transition"
                        >
                            <span class="font-semibold block text-slate-900">Guru Mapel</span>
                            <span class="text-slate-500 text-[11px]">guru_web / guru123</span>
                        </button>

                        <button
                            type="button"
                            @click="fillSample('siswa_ahmad', 'siswa123')"
                            class="col-span-2 p-2 text-left bg-slate-50 hover:bg-blue-50 hover:border-blue-200 border border-slate-200 rounded transition"
                        >
                            <span class="font-semibold block text-slate-900">Siswa (Ahmad Fauzi)</span>
                            <span class="text-slate-500 text-[11px]">siswa_ahmad / siswa123</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
