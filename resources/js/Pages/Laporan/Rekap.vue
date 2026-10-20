<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { Download, Filter, BarChart3 } from '@lucide/vue';

const props = defineProps({
    rekapList: Array,
    accessibleClasses: Array,
    selectedKelas: Object,
    filters: Object,
});

const kelasId = ref(props.filters.kelas_id || props.selectedKelas?.id || '');
const tipe = ref(props.filters.tipe || 'harian');
const dari = ref(props.filters.dari || '2026-08-01');
const sampai = ref(props.filters.sampai || '');

const applyFilter = () => {
    router.get('/laporan/rekap', {
        kelas_id: kelasId.value,
        tipe: tipe.value,
        dari: dari.value,
        sampai: sampai.value,
    }, { preserveState: true, replace: true });
};

const downloadPdfUrl = () => {
    const params = new URLSearchParams({
        kelas_id: kelasId.value,
        tipe: tipe.value,
        dari: dari.value,
        sampai: sampai.value,
    });
    window.open(`/laporan/rekap/pdf?${params.toString()}`, '_blank');
};
</script>

<template>
    <AppLayout title="Laporan Rekapitulasi Presensi">
        <div class="space-y-6">
            <!-- Filter Bar -->
            <div class="bg-white p-4 rounded-lg border border-slate-200 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3 flex-1">
                    <select
                        v-if="accessibleClasses.length > 1"
                        v-model="kelasId"
                        class="px-3 py-1.5 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                    >
                        <option v-for="k in accessibleClasses" :key="k.id" :value="k.id">
                            {{ k.nama_kelas }} ({{ k.tingkat }} {{ k.jurusan }})
                        </option>
                    </select>

                    <select
                        v-model="tipe"
                        class="px-3 py-1.5 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                    >
                        <option value="harian">Rekap Presensi Harian</option>
                        <option value="jam">Rekap Presensi Per Jam</option>
                    </select>

                    <div class="flex items-center gap-2 text-xs text-slate-600">
                        <span>Dari:</span>
                        <input
                            v-model="dari"
                            type="date"
                            class="px-2 py-1.5 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                        />
                        <span>Sampai:</span>
                        <input
                            v-model="sampai"
                            type="date"
                            class="px-2 py-1.5 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                        />
                    </div>

                    <button
                        type="button"
                        @click="applyFilter"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded transition border border-slate-300 min-h-[36px]"
                    >
                        <Filter class="w-3.5 h-3.5" />
                        <span>Filter</span>
                    </button>
                </div>

                <div>
                    <button
                        type="button"
                        @click="downloadPdfUrl"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-medium rounded transition min-h-[36px]"
                    >
                        <Download class="w-4 h-4" />
                        <span>Unduh PDF</span>
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-xs font-semibold text-slate-900">
                            Data Rekapitulasi: {{ selectedKelas ? selectedKelas.nama_kelas : 'Semua Siswa' }}
                        </h2>
                        <p class="text-[11px] text-slate-500">
                            Periode: {{ dari }} s/d {{ sampai }} &bull; Formula Kehadiran = (Hadir + Izin + Sakit) / Hari Efektif * 100%
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-4 py-3 w-12 text-center">No</th>
                                <th class="px-4 py-3">NIS</th>
                                <th class="px-4 py-3">Nama Siswa</th>
                                <th class="px-4 py-3 text-center">Hadir</th>
                                <th class="px-4 py-3 text-center">Izin</th>
                                <th class="px-4 py-3 text-center">Sakit</th>
                                <th class="px-4 py-3 text-center">Alpa</th>
                                <th class="px-4 py-3 text-center">Persentase</th>
                                <th class="px-4 py-3 text-right">Grafik</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(item, index) in rekapList" :key="item.siswa_id" class="hover:bg-slate-50/75">
                                <td class="px-4 py-3 text-center text-slate-400 font-mono">{{ index + 1 }}</td>
                                <td class="px-4 py-3 font-mono text-slate-600">{{ item.nis }}</td>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ item.nama }}</td>
                                <td class="px-4 py-3 text-center font-mono text-emerald-700 font-semibold">{{ item.hadir }}</td>
                                <td class="px-4 py-3 text-center font-mono text-amber-700 font-semibold">{{ item.izin }}</td>
                                <td class="px-4 py-3 text-center font-mono text-purple-700 font-semibold">{{ item.sakit }}</td>
                                <td class="px-4 py-3 text-center font-mono text-red-700 font-semibold">{{ item.alpa }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-block px-2.5 py-0.5 rounded font-mono font-bold text-[11px]"
                                        :class="{
                                            'bg-emerald-50 text-emerald-700 border border-emerald-200': item.persentase >= 85,
                                            'bg-amber-50 text-amber-700 border border-amber-200': item.persentase >= 75 && item.persentase < 85,
                                            'bg-red-50 text-red-700 border border-red-200': item.persentase < 75,
                                        }"
                                    >
                                        {{ item.persentase }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link
                                        :href="`/siswa/${item.siswa_id}/grafik`"
                                        class="inline-flex items-center gap-1 p-1.5 text-blue-700 hover:bg-blue-50 rounded text-xs font-medium"
                                        title="Buka Grafik"
                                    >
                                        <BarChart3 class="w-4 h-4" />
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="rekapList.length === 0">
                                <td colspan="9" class="px-4 py-8 text-center text-xs text-slate-400">
                                    Tidak ada data kehadiran yang tercatat pada rentang periode yang dipilih.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
