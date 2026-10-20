<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Pagination from '../../Components/UI/Pagination.vue';
import Modal from '../../Components/UI/Modal.vue';
import { Check, X, Eye } from '@lucide/vue';

const props = defineProps({
    izinList: Object,
    kelasList: Array,
    filters: Object,
});

const status = ref(props.filters.status || 'pending');
const kelasId = ref(props.filters.kelas_id || '');

const handleFilter = () => {
    router.get('/izin/approval', { status: status.value, kelas_id: kelasId.value }, { preserveState: true, replace: true });
};

// Modal Preview & Approve
const showDetailModal = ref(false);
const activeIzin = ref(null);
const approveForm = useForm({});

const openDetail = (item) => {
    activeIzin.value = item;
    showDetailModal.value = true;
};

const executeApprove = () => {
    approveForm.post(`/izin/approval/${activeIzin.value.id}/approve`, {
        onSuccess: () => { showDetailModal.value = false; },
    });
};

// Modal Reject with required note
const showRejectModal = ref(false);
const rejectForm = useForm({
    catatan: '',
});

const openReject = (item) => {
    activeIzin.value = item;
    rejectForm.reset();
    rejectForm.clearErrors();
    showRejectModal.value = true;
};

const executeReject = () => {
    rejectForm.post(`/izin/approval/${activeIzin.value.id}/reject`, {
        onSuccess: () => { showRejectModal.value = false; showDetailModal.value = false; },
    });
};
</script>

<template>
    <AppLayout title="Persetujuan Izin / Sakit">
        <div class="space-y-6">
            <div class="bg-white p-4 rounded-lg border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex flex-col sm:flex-row gap-3 flex-1 max-w-xl">
                    <select
                        v-model="status"
                        @change="handleFilter"
                        class="px-3 py-1.5 border border-slate-300 rounded text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                    >
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu Persetujuan (Pending)</option>
                        <option value="approved">Telah Disetujui (Approved)</option>
                        <option value="rejected">Ditolak (Rejected)</option>
                    </select>

                    <select
                        v-if="kelasList.length > 1"
                        v-model="kelasId"
                        @change="handleFilter"
                        class="px-3 py-1.5 border border-slate-300 rounded text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                    >
                        <option value="">Semua Kelas Binaan</option>
                        <option v-for="k in kelasList" :key="k.id" :value="k.id">
                            {{ k.nama_kelas }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-4 py-3">Siswa & Kelas</th>
                                <th class="px-4 py-3">Jenis</th>
                                <th class="px-4 py-3">Rentang Tanggal</th>
                                <th class="px-4 py-3">Alasan</th>
                                <th class="px-4 py-3">Bukti</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in izinList.data" :key="item.id" class="hover:bg-slate-50/75">
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-slate-900 block">{{ item.siswa?.nama }}</span>
                                    <span class="text-[11px] text-slate-500">{{ item.siswa?.kelas?.nama_kelas }} &bull; NIS: {{ item.siswa?.nis }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-block px-2.5 py-0.5 text-[10px] font-semibold uppercase rounded"
                                        :class="item.jenis === 'sakit' ? 'bg-purple-100 text-purple-800' : 'bg-amber-100 text-amber-800'"
                                    >
                                        {{ item.jenis }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-900">
                                    {{ item.tgl_mulai }} <span v-if="item.tgl_mulai !== item.tgl_selesai">s/d {{ item.tgl_selesai }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 max-w-xs truncate">{{ item.alasan }}</td>
                                <td class="px-4 py-3">
                                    <span v-if="item.bukti_path" class="text-blue-700 font-medium">Ada Berkas</span>
                                    <span v-else class="text-slate-400">-</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-block px-2 py-0.5 text-[10px] font-medium rounded capitalize"
                                        :class="{
                                            'bg-amber-50 text-amber-700 border border-amber-200': item.status === 'pending',
                                            'bg-emerald-50 text-emerald-700 border border-emerald-200': item.status === 'approved',
                                            'bg-red-50 text-red-700 border border-red-200': item.status === 'rejected',
                                        }"
                                    >
                                        {{ item.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                    <button
                                        type="button"
                                        @click="openDetail(item)"
                                        class="px-2.5 py-1 text-xs font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded"
                                    >
                                        Periksa
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="izinList.data.length === 0">
                                <td colspan="7" class="px-4 py-8 text-center text-xs text-slate-400">
                                    Tidak ada pengajuan izin yang memerlukan verifikasi.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-4 pb-4">
                    <Pagination :links="izinList.links" />
                </div>
            </div>
        </div>

        <!-- Detail & Approval Modal -->
        <Modal :show="showDetailModal" title="Verifikasi Pengajuan Izin" max-width="lg" @close="showDetailModal = false">
            <div v-if="activeIzin" class="space-y-4 text-xs">
                <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50 rounded border border-slate-200">
                    <div>
                        <span class="text-slate-500">Nama Siswa:</span>
                        <p class="font-semibold text-slate-900">{{ activeIzin.siswa?.nama }} ({{ activeIzin.siswa?.kelas?.nama_kelas }})</p>
                    </div>
                    <div>
                        <span class="text-slate-500">Jenis & Periode:</span>
                        <p class="font-semibold uppercase text-slate-900">
                            {{ activeIzin.jenis }} ({{ activeIzin.tgl_mulai }} s/d {{ activeIzin.tgl_selesai }})
                        </p>
                    </div>
                </div>

                <div>
                    <span class="text-slate-500 block mb-1">Alasan Izin / Sakit:</span>
                    <p class="p-3 bg-white rounded border border-slate-200 text-slate-800 leading-relaxed">
                        {{ activeIzin.alasan }}
                    </p>
                </div>

                <div v-if="activeIzin.bukti_path">
                    <span class="text-slate-500 block mb-1">Foto Bukti Surat / Resep Dokter:</span>
                    <div class="p-2 bg-slate-50 rounded border border-slate-200 text-center">
                        <img
                            :src="`/files/bukti-izin/${activeIzin.id}`"
                            alt="Bukti Pengajuan"
                            class="max-h-72 mx-auto rounded border border-slate-300"
                        />
                    </div>
                </div>

                <div v-if="activeIzin.status === 'rejected' && activeIzin.catatan" class="p-3 bg-red-50 border border-red-200 rounded">
                    <span class="text-red-700 font-medium block">Alasan Penolakan:</span>
                    <p class="text-red-600 mt-1">{{ activeIzin.catatan }}</p>
                </div>

                <div v-if="activeIzin.status === 'pending'" class="pt-4 border-t border-slate-200 flex justify-end gap-2">
                    <button
                        type="button"
                        @click="openReject(activeIzin)"
                        class="px-4 py-2 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded transition min-h-[44px]"
                    >
                        Tolak Pengajuan
                    </button>
                    <button
                        type="button"
                        @click="executeApprove"
                        :disabled="approveForm.processing"
                        class="px-5 py-2 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded transition disabled:opacity-50 min-h-[44px]"
                    >
                        <span v-if="approveForm.processing">Memproses...</span>
                        <span v-else>Setujui Pengajuan</span>
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Reject Modal With Mandatory Note -->
        <Modal :show="showRejectModal" title="Tolak Pengajuan Izin" max-width="md" @close="showRejectModal = false">
            <form @submit.prevent="executeReject" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700">Catatan Alasan Penolakan *</label>
                    <p class="text-[11px] text-slate-500 mb-2">Siswa akan dapat membaca catatan ini pada riwayat pengajuannya.</p>
                    <textarea
                        v-model="rejectForm.catatan"
                        rows="3"
                        required
                        placeholder="Contoh: Surat keterangan dokter tidak terbaca atau belum dilampirkan..."
                        class="w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-red-500 focus:outline-none"
                    ></textarea>
                    <span v-if="rejectForm.errors.catatan" class="text-[11px] text-red-600 block mt-1">{{ rejectForm.errors.catatan }}</span>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-200">
                    <button type="button" @click="showRejectModal = false" class="px-4 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-50 min-h-[44px]">Batal</button>
                    <button type="submit" :disabled="rejectForm.processing" class="px-4 py-2 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded min-h-[44px]">Konfirmasi Tolak</button>
                </div>
            </form>
        </Modal>
    </AppLayout>
</template>
