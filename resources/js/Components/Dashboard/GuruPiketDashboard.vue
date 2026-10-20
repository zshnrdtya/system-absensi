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
        <div class="bg-white p-6 rounded-lg border border-slate-200">
            <h3 class="text-sm font-semibold text-slate-900 mb-2">Delegasi Kelas Aktif Anda</h3>
            <p class="text-xs text-slate-500 mb-4">
                Anda memiliki hak akses pengganti wali kelas pada kelas berikut selama masa delegasi:
            </p>

            <div v-if="stats.delegasi_aktif && stats.delegasi_aktif.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div
                    v-for="del in stats.delegasi_aktif"
                    :key="del.id"
                    class="p-4 border border-blue-200 bg-blue-50/50 rounded-lg flex items-center justify-between"
                >
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ del.kelas?.nama_kelas }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">Periode: {{ del.tgl_mulai }} s/d {{ del.tgl_selesai }}</p>
                    </div>
                    <Link
                        href="/presensi/harian"
                        class="px-3 py-1.5 bg-blue-700 hover:bg-blue-800 text-white text-xs font-medium rounded transition"
                    >
                        Presensi
                    </Link>
                </div>
            </div>
            <div v-else class="p-4 bg-slate-50 border border-slate-200 rounded text-xs text-slate-500">
                Tidak ada delegasi kelas yang aktif untuk akun Anda pada hari ini.
            </div>
        </div>
    </div>
</template>
