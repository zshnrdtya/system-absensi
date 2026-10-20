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
                <h3 class="text-xs font-medium text-slate-500 uppercase tracking-wider">Persentase Kehadiran</h3>
                <p class="text-4xl font-bold text-blue-700 mt-2">{{ stats.persentase_kehadiran || 100 }}%</p>
                <p class="text-xs text-slate-500 mt-1">Formula: (Hadir + Izin + Sakit) / Hari Efektif</p>

                <div class="mt-6 pt-6 border-t border-slate-200">
                    <span class="text-xs text-slate-500 block">Status Presensi Hari Ini:</span>
                    <span
                        v-if="stats.presensi_hari_ini"
                        class="inline-block mt-2 px-3 py-1 text-xs font-semibold uppercase rounded"
                        :class="{
                            'bg-emerald-100 text-emerald-800': stats.presensi_hari_ini.status === 'hadir',
                            'bg-amber-100 text-amber-800': stats.presensi_hari_ini.status === 'izin',
                            'bg-purple-100 text-purple-800': stats.presensi_hari_ini.status === 'sakit',
                            'bg-red-100 text-red-800': stats.presensi_hari_ini.status === 'alpa',
                        }"
                    >
                        {{ stats.presensi_hari_ini.status }}
                    </span>
                    <span v-else class="inline-block mt-2 px-3 py-1 text-xs bg-slate-100 text-slate-600 rounded">
                        Belum Dicatat
                    </span>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg border border-slate-200 md:col-span-2">
                <h3 class="text-sm font-semibold text-slate-900 mb-4">Rincian Kehadiran Semester Ini</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="p-4 rounded bg-emerald-50 border border-emerald-200">
                        <span class="text-xs font-medium text-emerald-800">Hadir</span>
                        <p class="text-2xl font-semibold text-emerald-700 mt-1">{{ stats.presensi_total?.hadir || 0 }}</p>
                    </div>
                    <div class="p-4 rounded bg-amber-50 border border-amber-200">
                        <span class="text-xs font-medium text-amber-800">Izin</span>
                        <p class="text-2xl font-semibold text-amber-700 mt-1">{{ stats.presensi_total?.izin || 0 }}</p>
                    </div>
                    <div class="p-4 rounded bg-purple-50 border border-purple-200">
                        <span class="text-xs font-medium text-purple-800">Sakit</span>
                        <p class="text-2xl font-semibold text-purple-700 mt-1">{{ stats.presensi_total?.sakit || 0 }}</p>
                    </div>
                    <div class="p-4 rounded bg-red-50 border border-red-200">
                        <span class="text-xs font-medium text-red-800">Alpa</span>
                        <p class="text-2xl font-semibold text-red-700 mt-1">{{ stats.presensi_total?.alpa || 0 }}</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <Link
                        href="/izin/ajukan"
                        class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-medium rounded transition"
                    >
                        Ajukan Izin / Sakit Baru
                    </Link>
                    <Link
                        href="/siswa/grafik"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-medium rounded transition"
                    >
                        Lihat Grafik Kehadiran Saya
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
