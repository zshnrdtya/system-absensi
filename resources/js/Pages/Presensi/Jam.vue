<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { Lock, AlertCircle, Clock } from '@lucide/vue';

const props = defineProps({
    jadwal: Object,
    accessibleJadwals: Array,
    tanggal: String,
    siswaList: Array,
    canEdit: Boolean,
    isFutureDate: Boolean,
    dayMismatch: Boolean,
    maxDate: String,
});

const selectedJadwalId = ref(props.jadwal?.id || '');
const selectedTanggal = ref(props.tanggal || '');

const form = useForm({
    jadwal_id: props.jadwal?.id || '',
    tanggal: props.tanggal || '',
    presensi: props.siswaList.map(s => ({
        siswa_id: s.siswa_id,
        status: s.status,
        keterangan: s.keterangan || '',
        is_locked: s.is_locked,
    })),
});

watch(() => props.siswaList, (newList) => {
    form.presensi = newList.map(s => ({
        siswa_id: s.siswa_id,
        status: s.status,
        keterangan: s.keterangan || '',
        is_locked: s.is_locked,
    }));
});

const handleFilterChange = () => {
    router.get('/presensi/jam', {
        jadwal_id: selectedJadwalId.value,
        tanggal: selectedTanggal.value,
    }, { preserveState: true, replace: true });
};

const summary = computed(() => {
    const counts = { hadir: 0, izin: 0, sakit: 0, alpa: 0 };
    form.presensi.forEach(item => {
        if (counts[item.status] !== undefined) {
            counts[item.status]++;
        }
    });
    return counts;
});

const setAllHadir = () => {
    form.presensi.forEach(item => {
        if (!item.is_locked) {
            item.status = 'hadir';
        }
    });
};

const setStatus = (index, status) => {
    if (!props.canEdit || form.presensi[index].is_locked) return;
    form.presensi[index].status = status;
};

const submitPresensi = () => {
    form.jadwal_id = selectedJadwalId.value;
    form.tanggal = selectedTanggal.value;
    form.post('/presensi/jam');
};
</script>

<template>
    <AppLayout title="Presensi Jam Pelajaran">
        <div class="space-y-6">
            <!-- Filter Bar -->
            <div class="bg-white p-4 rounded-lg border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex flex-col sm:flex-row gap-3 flex-1 max-w-2xl">
                    <select
                        v-model="selectedJadwalId"
                        @change="handleFilterChange"
                        class="px-3 py-1.5 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none flex-1"
                    >
                        <option v-for="j in accessibleJadwals" :key="j.id" :value="j.id">
                            {{ j.kelas?.nama_kelas }} &bull; {{ j.mapel?.nama }} ({{ j.hari.toUpperCase() }} {{ j.jam_mulai.substring(0, 5) }} - {{ j.jam_selesai.substring(0, 5) }})
                        </option>
                    </select>

                    <input
                        v-model="selectedTanggal"
                        @change="handleFilterChange"
                        type="date"
                        :max="maxDate"
                        class="px-3 py-1.5 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                    />
                </div>

                <div class="flex items-center gap-2">
                    <button
                        v-if="canEdit"
                        type="button"
                        @click="setAllHadir"
                        class="px-3 py-2 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded transition min-h-[36px]"
                    >
                        Set Semua Hadir
                    </button>
                    <button
                        v-if="canEdit"
                        type="button"
                        @click="submitPresensi"
                        :disabled="form.processing"
                        class="px-4 py-2 text-xs font-medium text-white bg-blue-700 hover:bg-blue-800 rounded transition disabled:opacity-50 min-h-[36px]"
                    >
                        <span v-if="form.processing">Menyimpan...</span>
                        <span v-else>Simpan Presensi Jam</span>
                    </button>
                </div>
            </div>

            <!-- Banner Peringatan Hari Tidak Cocok (Skenario 15) -->
            <div v-if="dayMismatch" class="p-3 bg-red-50 border border-red-200 text-red-700 rounded text-xs flex items-center gap-2">
                <AlertCircle class="w-4 h-4 shrink-0" />
                <span>
                    Jadwal pelajaran ini berlangsung pada hari <strong>{{ jadwal?.hari?.toUpperCase() }}</strong>. Tanggal yang Anda pilih tidak jatuh pada hari tersebut, sehingga presensi tidak dapat diisi.
                </span>
            </div>

            <!-- Banner Toleransi Koreksi H+1 -->
            <div v-else-if="!canEdit" class="p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded text-xs flex items-center gap-2">
                <AlertCircle class="w-4 h-4 shrink-0" />
                <span>Mode hanya baca: Batas toleransi koreksi (H+1) telah berakhir atau tanggal berada di masa depan.</span>
            </div>

            <!-- Live Status Counter -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-3 rounded bg-emerald-50 border border-emerald-200 flex items-center justify-between">
                    <span class="text-xs font-medium text-emerald-800">Hadir</span>
                    <span class="text-lg font-bold text-emerald-700 font-mono">{{ summary.hadir }}</span>
                </div>
                <div class="p-3 rounded bg-amber-50 border border-amber-200 flex items-center justify-between">
                    <span class="text-xs font-medium text-amber-800">Izin</span>
                    <span class="text-lg font-bold text-amber-700 font-mono">{{ summary.izin }}</span>
                </div>
                <div class="p-3 rounded bg-purple-50 border border-purple-200 flex items-center justify-between">
                    <span class="text-xs font-medium text-purple-800">Sakit</span>
                    <span class="text-lg font-bold text-purple-700 font-mono">{{ summary.sakit }}</span>
                </div>
                <div class="p-3 rounded bg-red-50 border border-red-200 flex items-center justify-between">
                    <span class="text-xs font-medium text-red-800">Alpa</span>
                    <span class="text-lg font-bold text-red-700 font-mono">{{ summary.alpa }}</span>
                </div>
            </div>

            <!-- Student Attendance Table -->
            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-4 py-3 w-12 text-center">No</th>
                                <th class="px-4 py-3">Nama Siswa</th>
                                <th class="px-4 py-3 text-center">Kehadiran Jam Pelajaran</th>
                                <th class="px-4 py-3">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(s, index) in siswaList" :key="s.siswa_id" class="hover:bg-slate-50/75">
                                <td class="px-4 py-3 text-center text-slate-400 font-mono">{{ index + 1 }}</td>
                                <td class="px-4 py-3 font-medium text-slate-900">
                                    {{ s.nama }}
                                    <span class="block text-[11px] text-slate-400 font-mono">{{ s.nis }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button
                                            type="button"
                                            :disabled="!canEdit || form.presensi[index].is_locked"
                                            @click="setStatus(index, 'hadir')"
                                            class="w-10 h-10 rounded text-xs font-bold transition flex items-center justify-center font-mono"
                                            :class="form.presensi[index].status === 'hadir' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                        >
                                            H
                                        </button>
                                        <button
                                            type="button"
                                            :disabled="!canEdit || form.presensi[index].is_locked"
                                            @click="setStatus(index, 'izin')"
                                            class="w-10 h-10 rounded text-xs font-bold transition flex items-center justify-center font-mono"
                                            :class="form.presensi[index].status === 'izin' ? 'bg-amber-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                        >
                                            I
                                        </button>
                                        <button
                                            type="button"
                                            :disabled="!canEdit || form.presensi[index].is_locked"
                                            @click="setStatus(index, 'sakit')"
                                            class="w-10 h-10 rounded text-xs font-bold transition flex items-center justify-center font-mono"
                                            :class="form.presensi[index].status === 'sakit' ? 'bg-purple-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                        >
                                            S
                                        </button>
                                        <button
                                            type="button"
                                            :disabled="!canEdit || form.presensi[index].is_locked"
                                            @click="setStatus(index, 'alpa')"
                                            class="w-10 h-10 rounded text-xs font-bold transition flex items-center justify-center font-mono"
                                            :class="form.presensi[index].status === 'alpa' ? 'bg-red-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                        >
                                            A
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div v-if="form.presensi[index].is_locked" class="flex items-center gap-1.5 text-slate-500 italic">
                                        <Lock class="w-3.5 h-3.5 text-amber-600 shrink-0" />
                                        <span>{{ s.keterangan }}</span>
                                    </div>
                                    <input
                                        v-else
                                        v-model="form.presensi[index].keterangan"
                                        :disabled="!canEdit"
                                        type="text"
                                        placeholder="Catatan..."
                                        class="w-full px-2.5 py-1 border border-slate-300 rounded text-xs text-slate-900 focus:ring-1 focus:ring-blue-600 focus:outline-none"
                                    />
                                </td>
                            </tr>
                            <tr v-if="siswaList.length === 0">
                                <td colspan="4" class="px-4 py-8 text-center text-xs text-slate-400">
                                    Pilih jadwal pelajaran untuk menampilkan daftar siswa.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
