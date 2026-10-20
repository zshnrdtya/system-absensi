<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import Pagination from '../../../Components/UI/Pagination.vue';
import Modal from '../../../Components/UI/Modal.vue';
import ConfirmDeleteModal from '../../../Components/UI/ConfirmDeleteModal.vue';
import { Search, Plus, Edit2, Trash2 } from '@lucide/vue';

const props = defineProps({
    mapelList: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

const handleFilter = () => {
    router.get('/admin/mapel', { search: search.value }, { preserveState: true, replace: true });
};

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    kode: '',
    nama: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    editingId.value = item.id;
    form.clearErrors();
    form.kode = item.kode;
    form.nama = item.nama;
    showModal.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(`/admin/mapel/${editingId.value}`, {
            onSuccess: () => { showModal.value = false; },
        });
    } else {
        form.post('/admin/mapel', {
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
    deleteForm.delete(`/admin/mapel/${deletingItem.value.id}`, {
        onSuccess: () => { showDeleteModal.value = false; },
    });
};
</script>

<template>
    <AppLayout title="Kelola Mata Pelajaran">
        <div class="space-y-6">
            <div class="bg-white p-4 rounded-lg border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="relative flex-1 max-w-md">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                    <input
                        v-model="search"
                        @keyup.enter="handleFilter"
                        type="text"
                        placeholder="Cari kode atau nama mata pelajaran..."
                        class="w-full pl-9 pr-3 py-1.5 border border-slate-300 rounded text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                    />
                </div>

                <button
                    type="button"
                    @click="openCreateModal"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-medium rounded transition min-h-[36px]"
                >
                    <Plus class="w-4 h-4" />
                    <span>Tambah Mata Pelajaran</span>
                </button>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-4 py-3">Kode Mapel</th>
                                <th class="px-4 py-3">Nama Mata Pelajaran</th>
                                <th class="px-4 py-3">Jumlah Jadwal Terpasang</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in mapelList.data" :key="item.id" class="hover:bg-slate-50/75">
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ item.kode }}</td>
                                <td class="px-4 py-3 text-slate-800 font-medium">{{ item.nama }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ item.jadwal_pelajaran_count || 0 }} Jadwal</td>
                                <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                    <button
                                        type="button"
                                        @click="openEditModal(item)"
                                        class="p-1.5 text-blue-700 hover:bg-blue-50 rounded"
                                        title="Edit"
                                        aria-label="Edit Mata Pelajaran"
                                    >
                                        <Edit2 class="w-4 h-4" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="confirmDelete(item)"
                                        class="p-1.5 text-red-600 hover:bg-red-50 rounded"
                                        title="Hapus"
                                        aria-label="Hapus Mata Pelajaran"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="mapelList.data.length === 0">
                                <td colspan="4" class="px-4 py-8 text-center text-xs text-slate-400">
                                    Tidak ada data mata pelajaran.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-4 pb-4">
                    <Pagination :links="mapelList.links" />
                </div>
            </div>
        </div>

        <Modal :show="showModal" :title="isEditing ? 'Ubah Mata Pelajaran' : 'Tambah Mata Pelajaran Baru'" @close="showModal = false">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700">Kode Mata Pelajaran *</label>
                    <input v-model="form.kode" type="text" placeholder="Contoh: PPLG-WEB" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none uppercase" />
                    <span v-if="form.errors.kode" class="text-[11px] text-red-600">{{ form.errors.kode }}</span>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700">Nama Mata Pelajaran *</label>
                    <input v-model="form.nama" type="text" placeholder="Contoh: Pemrograman Web" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                    <span v-if="form.errors.nama" class="text-[11px] text-red-600">{{ form.errors.nama }}</span>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-200">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-50 min-h-[44px]">Batal</button>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 text-xs font-medium text-white bg-blue-700 hover:bg-blue-800 rounded min-h-[44px]">Simpan</button>
                </div>
            </form>
        </Modal>

        <ConfirmDeleteModal
            :show="showDeleteModal"
            title="Hapus Mata Pelajaran"
            :message="`Apakah Anda yakin ingin menghapus '${deletingItem?.nama} (${deletingItem?.kode})'? Tindakan ini tidak dapat dibatalkan.`"
            :processing="deleteForm.processing"
            @close="showDeleteModal = false"
            @confirm="executeDelete"
        />
    </AppLayout>
</template>
