<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    stats: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg border border-slate-200 md:col-span-1">
                <h3 class="text-xs font-medium text-slate-500 uppercase tracking-wider">Kelas Yang Diampu</h3>
                <p class="text-2xl font-semibold text-slate-900 mt-2">{{ stats.kelas?.nama_kelas || 'Belum Ditugaskan' }}</p>
                <p class="text-xs text-slate-600 mt-1">
                    {{ stats.kelas?.tingkat }} &bull; {{ stats.kelas?.jurusan }} &bull; {{ stats.total_siswa }} Siswa
                </p>

                <div class="mt-6 pt-6 border-t border-slate-200 flex flex-col gap-2">
                    <Link
                        href="/presensi/harian"
                        class="inline-flex justify-center items-center px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-medium rounded transition"
                    >
                        Input Presensi Hari Ini
                    </Link>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg border border-slate-200 md:col-span-2">
                <h3 class="text-sm font-semibold text-slate-900 mb-4">Presensi Siswa Hari Ini</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="p-4 rounded bg-emerald-50 border border-emerald-200">
                        <span class="text-xs font-medium text-emerald-800">Hadir</span>
                        <p class="text-2xl font-semibold text-emerald-700 mt-1">{{ stats.presensi_hari_ini?.hadir || 0 }}</p>
                    </div>
                    <div class="p-4 rounded bg-amber-50 border border-amber-200">
                        <span class="text-xs font-medium text-amber-800">Izin</span>
                        <p class="text-2xl font-semibold text-amber-700 mt-1">{{ stats.presensi_hari_ini?.izin || 0 }}</p>
                    </div>
                    <div class="p-4 rounded bg-purple-50 border border-purple-200">
                        <span class="text-xs font-medium text-purple-800">Sakit</span>
                        <p class="text-2xl font-semibold text-purple-700 mt-1">{{ stats.presensi_hari_ini?.sakit || 0 }}</p>
                    </div>
                    <div class="p-4 rounded bg-red-50 border border-red-200">
                        <span class="text-xs font-medium text-red-800">Alpa</span>
                        <p class="text-2xl font-semibold text-red-700 mt-1">{{ stats.presensi_hari_ini?.alpa || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg border border-slate-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-900">Pengajuan Izin Menunggu Persetujuan</h3>
                <Link href="/izin/approval" class="text-xs font-medium text-blue-700 hover:underline">
                    Lihat Semua
                </Link>
            </div>

            <div v-if="stats.izin_pending && stats.izin_pending.length > 0" class="divide-y divide-slate-100">
                <div
                    v-for="item in stats.izin_pending"
                    :key="item.id"
                    class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2"
                >
                    <div>
                        <span class="font-medium text-xs text-slate-900">{{ item.siswa?.nama }}</span>
                        <span
                            class="ml-2 inline-block px-2 py-0.5 text-[10px] font-semibold uppercase rounded"
                            :class="item.jenis === 'sakit' ? 'bg-purple-100 text-purple-800' : 'bg-amber-100 text-amber-800'"
                        >
                            {{ item.jenis }}
                        </span>
                        <p class="text-xs text-slate-500 mt-0.5">{{ item.alasan }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-500">{{ item.tgl_mulai }} s/d {{ item.tgl_selesai }}</span>
                        <Link
                            href="/izin/approval"
                            class="px-2.5 py-1 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded"
                        >
                            Periksa
                        </Link>
                    </div>
                </div>
            </div>
            <div v-else class="text-xs text-slate-500 py-6 text-center">
                Tidak ada pengajuan izin yang menunggu persetujuan saat ini.
            </div>
        </div>
    </div>
</template>
