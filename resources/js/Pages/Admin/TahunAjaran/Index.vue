<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import Pagination from '../../../Components/UI/Pagination.vue';
import Modal from '../../../Components/UI/Modal.vue';
import ConfirmDeleteModal from '../../../Components/UI/ConfirmDeleteModal.vue';
import { Search, Plus, Edit2, Trash2 } from '@lucide/vue';

const props = defineProps({
    tahunAjaran: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

const handleFilter = () => {
    router.get('/admin/tahun-ajaran', { search: search.value }, { preserveState: true, replace: true });
};

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    nama: '',
    semester: 'ganjil',
    tgl_mulai: '',
    tgl_selesai: '',
    is_active: false,
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
    form.nama = item.nama;
    form.semester = item.semester;
    form.tgl_mulai = item.tgl_mulai;
    form.tgl_selesai = item.tgl_selesai;
    form.is_active = Boolean(item.is_active);
    showModal.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(`/admin/tahun-ajaran/${editingId.value}`, {
            onSuccess: () => { showModal.value = false; },
        });
    } else {
        form.post('/admin/tahun-ajaran', {
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
    deleteForm.delete(`/admin/tahun-ajaran/${deletingItem.value.id}`, {
        onSuccess: () => { showDeleteModal.value = false; },
    });
};
</script>

<template>
    <AppLayout title="Kelola Tahun Ajaran">
        <div class="space-y-6">
            <div class="bg-white p-4 rounded-lg border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="relative flex-1 max-w-md">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                    <input
                        v-model="search"
                        @keyup.enter="handleFilter"
                        type="text"
                        placeholder="Cari tahun ajaran (misal: 2026/2027)..."
                        class="w-full pl-9 pr-3 py-1.5 border border-slate-300 rounded text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                    />
                </div>

                <button
                    type="button"
                    @click="openCreateModal"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-medium rounded transition min-h-[36px]"
                >
                    <Plus class="w-4 h-4" />
                    <span>Tambah Tahun Ajaran</span>
                </button>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-4 py-3">Tahun Ajaran</th>
                                <th class="px-4 py-3">Semester</th>
                                <th class="px-4 py-3">Rentang Tanggal</th>
                                <th class="px-4 py-3">Jumlah Kelas</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in tahunAjaran.data" :key="item.id" class="hover:bg-slate-50/75">
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ item.nama }}</td>
                                <td class="px-4 py-3 capitalize text-slate-700">{{ item.semester }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ item.tgl_mulai }} s/d {{ item.tgl_selesai }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ item.kelas_count || 0 }} Kelas</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-block px-2 py-0.5 text-[10px] font-medium rounded"
                                        :class="item.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200'"
                                    >
                                        {{ item.is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                    <button
                                        type="button"
                                        @click="openEditModal(item)"
                                        class="p-1.5 text-blue-700 hover:bg-blue-50 rounded"
                                        title="Edit"
                                        aria-label="Edit Tahun Ajaran"
                                    >
                                        <Edit2 class="w-4 h-4" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="confirmDelete(item)"
                                        class="p-1.5 text-red-600 hover:bg-red-50 rounded"
                                        title="Hapus"
                                        aria-label="Hapus Tahun Ajaran"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="tahunAjaran.data.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-xs text-slate-400">
                                    Tidak ada data tahun ajaran.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-4 pb-4">
                    <Pagination :links="tahunAjaran.links" />
                </div>
            </div>
        </div>

        <Modal :show="showModal" :title="isEditing ? 'Ubah Tahun Ajaran' : 'Tambah Tahun Ajaran Baru'" @close="showModal = false">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700">Nama Tahun Ajaran *</label>
                    <input v-model="form.nama" type="text" placeholder="Contoh: 2026/2027" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                    <span v-if="form.errors.nama" class="text-[11px] text-red-600">{{ form.errors.nama }}</span>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700">Semester *</label>
                    <select v-model="form.semester" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="ganjil">Ganjil</option>
                        <option value="genap">Genap</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Tanggal Mulai *</label>
                        <input v-model="form.tgl_mulai" type="date" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                        <span v-if="form.errors.tgl_mulai" class="text-[11px] text-red-600">{{ form.errors.tgl_mulai }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700">Tanggal Selesai *</label>
                        <input v-model="form.tgl_selesai" type="date" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                        <span v-if="form.errors.tgl_selesai" class="text-[11px] text-red-600">{{ form.errors.tgl_selesai }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input id="ta_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 rounded border-slate-300" />
                    <label for="ta_active" class="text-xs text-slate-700 cursor-pointer">Setel sebagai Tahun Ajaran Aktif</label>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-200">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-50 min-h-[44px]">Batal</button>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 text-xs font-medium text-white bg-blue-700 hover:bg-blue-800 rounded min-h-[44px]">Simpan</button>
                </div>
            </form>
        </Modal>

        <ConfirmDeleteModal
            :show="showDeleteModal"
            title="Hapus Tahun Ajaran"
            :message="`Apakah Anda yakin ingin menghapus '${deletingItem?.nama} (${deletingItem?.semester})'? Tindakan ini tidak dapat dibatalkan.`"
            :processing="deleteForm.processing"
            @close="showDeleteModal = false"
            @confirm="executeDelete"
        />
    </AppLayout>
</template>
