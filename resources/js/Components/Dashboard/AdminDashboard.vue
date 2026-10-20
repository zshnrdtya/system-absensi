<script setup>
import { Users, GraduationCap, FileSignature } from '@lucide/vue';

defineProps({
    stats: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-lg border border-slate-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Total Pengguna</span>
                    <Users class="w-4 h-4 text-slate-400" />
                </div>
                <p class="text-2xl font-semibold text-slate-900 mt-2">{{ stats.total_user || 0 }}</p>
                <p class="text-[11px] text-slate-500 mt-1">Akun aktif terdaftar</p>
            </div>

            <div class="bg-white p-5 rounded-lg border border-slate-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Total Kelas</span>
                    <GraduationCap class="w-4 h-4 text-slate-400" />
                </div>
                <p class="text-2xl font-semibold text-slate-900 mt-2">{{ stats.total_kelas || 0 }}</p>
                <p class="text-[11px] text-slate-500 mt-1">Tahun ajaran aktif</p>
            </div>

            <div class="bg-white p-5 rounded-lg border border-slate-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Total Siswa</span>
                    <Users class="w-4 h-4 text-slate-400" />
                </div>
                <p class="text-2xl font-semibold text-slate-900 mt-2">{{ stats.total_siswa || 0 }}</p>
                <p class="text-[11px] text-slate-500 mt-1">Terdaftar di kelas</p>
            </div>

            <div class="bg-white p-5 rounded-lg border border-slate-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Izin Menunggu</span>
                    <FileSignature class="w-4 h-4 text-amber-500" />
                </div>
                <p class="text-2xl font-semibold text-amber-600 mt-2">{{ stats.izin_pending_count || 0 }}</p>
                <p class="text-[11px] text-slate-500 mt-1">Perlu persetujuan</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg border border-slate-200">
            <h3 class="text-sm font-semibold text-slate-900 mb-4">Ringkasan Presensi Hari Ini (Seluruh Sekolah)</h3>
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
</template>
