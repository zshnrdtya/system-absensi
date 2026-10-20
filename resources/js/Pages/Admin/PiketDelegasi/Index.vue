<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import Pagination from '../../../Components/UI/Pagination.vue';
import Modal from '../../../Components/UI/Modal.vue';
import ConfirmDeleteModal from '../../../Components/UI/ConfirmDeleteModal.vue';
import { Plus, Edit2, Trash2 } from '@lucide/vue';

const props = defineProps({
    delegasiList: Object,
    guruPiketList: Array,
    kelasList: Array,
});

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    guru_id: props.guruPiketList[0]?.id || '',
    kelas_id: props.kelasList[0]?.id || '',
    tgl_mulai: '',
    tgl_selesai: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.guru_id = props.guruPiketList[0]?.id || '';
    form.kelas_id = props.kelasList[0]?.id || '';
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    editingId.value = item.id;
    form.clearErrors();
    form.guru_id = item.guru_id;
    form.kelas_id = item.kelas_id;
    form.tgl_mulai = item.tgl_mulai;
    form.tgl_selesai = item.tgl_selesai;
    showModal.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(`/admin/piket-delegasi/${editingId.value}`, {
            onSuccess: () => { showModal.value = false; },
        });
    } else {
        form.post('/admin/piket-delegasi', {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
};

const showDeleteModal = ref(false);
const deletingItem = ref(null);
const deleteForm = useForm({});

const confirmDelete = (item) => {
    deletingItem.value = item;
    showDeleteModal.value = true;
};

const executeDelete = () => {
    deleteForm.delete(`/admin/piket-delegasi/${deletingItem.value.id}`, {
        onSuccess: () => { showDeleteModal.value = false; },
    });
};
</script>

<template>
    <AppLayout title="Kelola Delegasi Guru Piket">
        <div class="space-y-6">
            <div class="bg-white p-4 rounded-lg border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xs font-semibold text-slate-800">Daftar Penugasan Delegasi Piket</h2>
                    <p class="text-[11px] text-slate-500">Guru piket bertindak sebagai pengganti wali kelas pada kelas yang ditugaskan.</p>
                </div>

                <button
                    type="button"
                    @click="openCreateModal"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-medium rounded transition min-h-[36px]"
                >
                    <Plus class="w-4 h-4" />
                    <span>Tetapkan Delegasi Piket</span>
                </button>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-4 py-3">Guru Piket</th>
                                <th class="px-4 py-3">Kelas Didelegasikan</th>
                                <th class="px-4 py-3">Periode Berlaku</th>
                                <th class="px-4 py-3">Ditetapkan Oleh</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in delegasiList.data" :key="item.id" class="hover:bg-slate-50/75">
                                <td class="px-4 py-3 font-semibold text-slate-900">
                                    {{ item.guru?.name }}
                                    <span class="block text-[11px] text-slate-400 font-normal">@{{ item.guru?.username }}</span>
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-800">{{ item.kelas?.nama_kelas }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ item.tgl_mulai }} s/d {{ item.tgl_selesai }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ item.pembuat?.name || '-' }}</td>
                                <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                    <button
                                        type="button"
                                        @click="openEditModal(item)"
                                        class="p-1.5 text-blue-700 hover:bg-blue-50 rounded"
                                        title="Edit"
                                        aria-label="Edit Delegasi"
                                    >
                                        <Edit2 class="w-4 h-4" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="confirmDelete(item)"
                                        class="p-1.5 text-red-600 hover:bg-red-50 rounded"
                                        title="Hapus"
                                        aria-label="Hapus Delegasi"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="delegasiList.data.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-xs text-slate-400">
                                    Belum ada data penetapan delegasi guru piket.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-4 pb-4">
                    <Pagination :links="delegasiList.links" />
                </div>
            </div>
        </div>

        <Modal :show="showModal" :title="isEditing ? 'Ubah Delegasi Piket' : 'Tetapkan Delegasi Guru Piket'" @close="showModal = false">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700">Guru Piket *</label>
                    <select v-model="form.guru_id" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option v-for="g in guruPiketList" :key="g.id" :value="g.id">
                            {{ g.name }} (@{{ g.username }})
                        </option>
                    </select>
                    <span v-if="form.errors.guru_id" class="text-[11px] text-red-600">{{ form.errors.guru_id }}</span>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700">Kelas Yang Didelegasikan *</label>
                    <select v-model="form.kelas_id" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option v-for="k in kelasList" :key="k.id" :value="k.id">
                            {{ k.nama_kelas }} (Wali: {{ k.wali_kelas?.name || '-' }})
                        </option>
                    </select>
                    <span v-if="form.errors.kelas_id" class="text-[11px] text-red-600">{{ form.errors.kelas_id }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Tanggal Mulai Berlaku *</label>
                        <input v-model="form.tgl_mulai" type="date" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                        <span v-if="form.errors.tgl_mulai" class="text-[11px] text-red-600">{{ form.errors.tgl_mulai }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700">Tanggal Selesai Berlaku *</label>
                        <input v-model="form.tgl_selesai" type="date" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                        <span v-if="form.errors.tgl_selesai" class="text-[11px] text-red-600">{{ form.errors.tgl_selesai }}</span>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-200">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-50 min-h-[44px]">Batal</button>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 text-xs font-medium text-white bg-blue-700 hover:bg-blue-800 rounded min-h-[44px]">Simpan</button>
                </div>
            </form>
        </Modal>

        <ConfirmDeleteModal
            :show="showDeleteModal"
            title="Hapus Delegasi Piket"
            :message="`Apakah Anda yakin ingin menghapus penetapan delegasi ini?`"
            :processing="deleteForm.processing"
            @close="showDeleteModal = false"
            @confirm="executeDelete"
        />
    </AppLayout>
</template>
