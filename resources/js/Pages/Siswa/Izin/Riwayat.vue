<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import Pagination from '../../../Components/UI/Pagination.vue';
import Modal from '../../../Components/UI/Modal.vue';
import ConfirmDeleteModal from '../../../Components/UI/ConfirmDeleteModal.vue';
import { Plus, Eye, XCircle } from '@lucide/vue';

defineProps({
    izinList: Object,
});

const showDetailModal = ref(false);
const selectedIzin = ref(null);

const openDetail = (item) => {
    selectedIzin.value = item;
    showDetailModal.value = true;
};

const showCancelModal = ref(false);
const cancelingIzin = ref(null);
const cancelForm = useForm({});

const confirmCancel = (item) => {
    cancelingIzin.value = item;
    showCancelModal.value = true;
};

const executeCancel = () => {
    cancelForm.delete(`/izin/ajukan/${cancelingIzin.value.id}`, {
        onSuccess: () => { showCancelModal.value = false; },
    });
};
</script>

<template>
    <AppLayout title="Riwayat Pengajuan Izin">
        <div class="space-y-6">
            <div class="bg-white p-4 rounded-lg border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Daftar Pengajuan Izin / Sakit</h2>
                    <p class="text-xs text-slate-500">Pantau status persetujuan surat izin Anda secara langsung.</p>
                </div>

                <Link
                    href="/izin/ajukan"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-medium rounded transition min-h-[36px]"
                >
                    <Plus class="w-4 h-4" />
                    <span>Buat Pengajuan Baru</span>
                </Link>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                            <tr>
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
                                        class="p-1.5 text-blue-700 hover:bg-blue-50 rounded"
                                        title="Detail"
                                        aria-label="Detail Izin"
                                    >
                                        <Eye class="w-4 h-4" />
                                    </button>
                                    <button
                                        v-if="item.status === 'pending'"
                                        type="button"
                                        @click="confirmCancel(item)"
                                        class="p-1.5 text-red-600 hover:bg-red-50 rounded"
                                        title="Batalkan Pengajuan"
                                        aria-label="Batalkan Izin"
                                    >
                                        <XCircle class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="izinList.data.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-xs text-slate-400">
                                    Belum ada riwayat pengajuan izin atau sakit.
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

        <!-- Detail Modal -->
        <Modal :show="showDetailModal" title="Detail Pengajuan Izin" @close="showDetailModal = false">
            <div v-if="selectedIzin" class="space-y-4 text-xs">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <span class="text-slate-500">Jenis:</span>
                        <span class="ml-2 font-semibold uppercase text-slate-900">{{ selectedIzin.jenis }}</span>
                    </div>
                    <div>
                        <span
                            class="px-2 py-0.5 text-[10px] font-semibold uppercase rounded"
                            :class="{
                                'bg-amber-100 text-amber-800': selectedIzin.status === 'pending',
                                'bg-emerald-100 text-emerald-800': selectedIzin.status === 'approved',
                                'bg-red-100 text-red-800': selectedIzin.status === 'rejected',
                            }"
                        >
                            {{ selectedIzin.status }}
                        </span>
                    </div>
                </div>

                <div>
                    <span class="text-slate-500 block">Periode Tanggal:</span>
                    <span class="font-medium text-slate-900">{{ selectedIzin.tgl_mulai }} s/d {{ selectedIzin.tgl_selesai }}</span>
                </div>

                <div>
                    <span class="text-slate-500 block">Alasan Pengajuan:</span>
                    <p class="mt-1 p-2.5 bg-slate-50 rounded border border-slate-200 text-slate-800 leading-relaxed">
                        {{ selectedIzin.alasan }}
                    </p>
                </div>

                <div v-if="selectedIzin.catatan">
                    <span class="text-red-600 font-medium block">Catatan Verifikator:</span>
                    <p class="mt-1 p-2.5 bg-red-50 border border-red-200 rounded text-red-700 leading-relaxed">
                        {{ selectedIzin.catatan }}
                    </p>
                </div>

                <div v-if="selectedIzin.bukti_path">
                    <span class="text-slate-500 block mb-1">Foto Bukti Surat / Keterangan:</span>
                    <div class="mt-1 p-2 bg-slate-50 border border-slate-200 rounded text-center">
                        <img
                            :src="`/files/bukti-izin/${selectedIzin.id}`"
                            alt="Bukti Izin"
                            class="max-h-60 mx-auto rounded border border-slate-300"
                        />
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Cancel Confirmation Modal -->
        <ConfirmDeleteModal
            :show="showCancelModal"
            title="Batalkan Pengajuan Izin"
            message="Apakah Anda yakin ingin membatalkan pengajuan izin ini?"
            :processing="cancelForm.processing"
            @close="showCancelModal = false"
            @confirm="executeCancel"
        />
    </AppLayout>
</template>
