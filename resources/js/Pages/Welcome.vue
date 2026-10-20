<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <Head title="Sistem Presensi & Pengajuan Izin Siswa" />

    <div class="min-h-screen bg-slate-50 text-slate-900 flex flex-col justify-between">
        <header class="border-b border-slate-200 bg-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded bg-blue-700 text-white font-semibold flex items-center justify-center text-sm tracking-tight">
                        1D
                    </div>
                    <div>
                        <span class="font-semibold text-base tracking-tight text-slate-900">1dex-sybsen</span>
                        <span class="hidden sm:inline-block ml-2 text-xs text-slate-500 font-normal">Sistem Presensi Siswa</span>
                    </div>
                </div>

                <nav class="flex items-center gap-3">
                    <Link
                        v-if="canLogin"
                        href="/login"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-700 hover:bg-blue-800 rounded transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2"
                    >
                        Masuk ke Sistem
                    </Link>
                </nav>
            </div>
        </header>

        <main class="max-w-6xl mx-auto px-4 sm:px-6 py-12 flex-1 flex flex-col justify-center">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded bg-slate-100 border border-slate-200 text-xs font-medium text-slate-700 mb-6">
                    <span>Versi Produksi Akademik</span>
                </div>

                <h1 class="text-3xl sm:text-4xl font-semibold tracking-tight text-slate-900 leading-tight mb-4">
                    Pencatatan Presensi dan Alur Pengajuan Izin Terpusat
                </h1>

                <p class="text-slate-600 text-base leading-relaxed mb-8">
                    Platform presensi digital untuk efisiensi absensi harian dan per jam pelajaran, verifikasi surat izin atau sakit dengan foto bukti, serta rekapitulasi kehadiran otomatis untuk kebutuhan sekolah.
                </p>

                <div class="flex flex-col sm:flex-row gap-3">
                    <Link
                        href="/login"
                        class="inline-flex justify-center items-center px-5 py-2.5 text-sm font-medium text-white bg-blue-700 hover:bg-blue-800 rounded transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2"
                    >
                        Buka Halaman Masuk
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-16 pt-10 border-t border-slate-200">
                <div class="p-5 bg-white rounded-lg border border-slate-200">
                    <h2 class="text-sm font-semibold text-slate-900 mb-2">Presensi Harian & Per Jam</h2>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Input kehadiran satu kelas secara cepat oleh Wali Kelas, Guru Mapel, atau Guru Piket yang didelegasikan.
                    </p>
                </div>

                <div class="p-5 bg-white rounded-lg border border-slate-200">
                    <h2 class="text-sm font-semibold text-slate-900 mb-2">Pengajuan Izin Digital</h2>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Siswa mengunggah bukti surat dokter atau foto keterangan izin secara langsung dengan notifikasi status persetujuan.
                    </p>
                </div>

                <div class="p-5 bg-white rounded-lg border border-slate-200">
                    <h2 class="text-sm font-semibold text-slate-900 mb-2">Rekapitulasi & Cetak PDF</h2>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Laporan persentase kehadiran berkala per siswa, kelas, dan periode kalender yang siap diunduh dalam format PDF.
                    </p>
                </div>
            </div>
        </main>

        <footer class="border-t border-slate-200 bg-white py-6">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
                <p>1dex-sybsen &copy; 2026. Hak cipta dilindungi.</p>
                <p>Sistem Informasi Presensi & Manajemen Kehadiran Siswa</p>
            </div>
        </footer>
    </div>
</template>
