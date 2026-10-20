<script setup>
import { Link } from '@inertiajs/vue3';
import { CheckCircle2 } from '@lucide/vue';

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
            <h3 class="text-sm font-semibold text-slate-900 mb-1">
                Jadwal Mengajar Hari Ini ({{ stats.hari ? stats.hari.toUpperCase() : 'HARI INI' }})
            </h3>
            <p class="text-xs text-slate-500 mb-4">Pastikan presensi per jam pelajaran diinput tepat waktu.</p>

            <div v-if="stats.jadwal_hari_ini && stats.jadwal_hari_ini.length > 0" class="divide-y divide-slate-100">
                <div
                    v-for="j in stats.jadwal_hari_ini"
                    :key="j.id"
                    class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                >
                    <div>
                        <span class="font-semibold text-xs text-slate-900">{{ j.kelas?.nama_kelas }}</span>
                        <span class="text-xs text-slate-500 mx-2">&bull;</span>
                        <span class="text-xs font-medium text-slate-700">{{ j.mapel?.nama }}</span>
                        <p class="text-xs text-slate-500 mt-0.5">{{ j.jam_mulai }} - {{ j.jam_selesai }} WIB</p>
                    </div>
                    <div>
                        <span
                            v-if="j.sudah_presensi"
                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-emerald-800 bg-emerald-100 rounded"
                        >
                            <CheckCircle2 class="w-3.5 h-3.5" />
                            Sudah Diinput
                        </span>
                        <Link
                            v-else
                            href="/presensi/jam"
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-700 hover:bg-blue-800 text-white text-xs font-medium rounded transition"
                        >
                            Input Presensi
                        </Link>
                    </div>
                </div>
            </div>
            <div v-else class="text-xs text-slate-500 py-6 text-center">
                Tidak ada jadwal mengajar pada hari ini.
            </div>
        </div>
    </div>
</template>
