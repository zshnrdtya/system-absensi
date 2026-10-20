<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import Pagination from '../../../Components/UI/Pagination.vue';
import Modal from '../../../Components/UI/Modal.vue';
import ConfirmDeleteModal from '../../../Components/UI/ConfirmDeleteModal.vue';
import { Search, Plus, Edit2, Trash2 } from '@lucide/vue';

const props = defineProps({
    kelasList: Object,
    tahunAjaranList: Array,
    waliKelasList: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');

const handleFilter = () => {
    router.get('/admin/kelas', { search: search.value }, { preserveState: true, replace: true });
};

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    nama_kelas: '',
    tingkat: 'XI',
    jurusan: 'PPLG',
    tahun_ajaran_id: props.tahunAjaranList.find(t => t.is_active)?.id || '',
    wali_kelas_id: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.tahun_ajaran_id = props.tahunAjaranList.find(t => t.is_active)?.id || '';
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    editingId.value = item.id;
    form.clearErrors();
    form.nama_kelas = item.nama_kelas;
    form.tingkat = item.tingkat;
    form.jurusan = item.jurusan;
    form.tahun_ajaran_id = item.tahun_ajaran_id;
    form.wali_kelas_id = item.wali_kelas_id;
    showModal.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(`/admin/kelas/${editingId.value}`, {
            onSuccess: () => { showModal.value = false; },
        });
    } else {
        form.post('/admin/kelas', {
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
    deleteForm.delete(`/admin/kelas/${deletingItem.value.id}`, {
        onSuccess: () => { showDeleteModal.value = false; },
    });
};
</script>

<template>
    <AppLayout title="Kelola Kelas">
        <div class="space-y-6">
            <div class="bg-white p-4 rounded-lg border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="relative flex-1 max-w-md">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                    <input
                        v-model="search"
                        @keyup.enter="handleFilter"
                        type="text"
                        placeholder="Cari kelas, tingkat, jurusan..."
                        class="w-full pl-9 pr-3 py-1.5 border border-slate-300 rounded text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                    />
                </div>

                <button
                    type="button"
                    @click="openCreateModal"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-medium rounded transition min-h-[36px]"
                >
                    <Plus class="w-4 h-4" />
                    <span>Tambah Kelas</span>
                </button>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-4 py-3">Nama Kelas</th>
                                <th class="px-4 py-3">Tingkat / Jurusan</th>
                                <th class="px-4 py-3">Wali Kelas</th>
                                <th class="px-4 py-3">Tahun Ajaran</th>
                                <th class="px-4 py-3">Jumlah Siswa</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in kelasList.data" :key="item.id" class="hover:bg-slate-50/75">
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ item.nama_kelas }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ item.tingkat }} &bull; {{ item.jurusan }}</td>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ item.wali_kelas?.name || '-' }}</td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ item.tahun_ajaran?.nama }} ({{ item.tahun_ajaran?.semester }})
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ item.siswa_count || 0 }} Siswa</td>
                                <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                    <button
                                        type="button"
                                        @click="openEditModal(item)"
                                        class="p-1.5 text-blue-700 hover:bg-blue-50 rounded"
                                        title="Edit"
                                        aria-label="Edit Kelas"
                                    >
                                        <Edit2 class="w-4 h-4" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="confirmDelete(item)"
                                        class="p-1.5 text-red-600 hover:bg-red-50 rounded"
                                        title="Hapus"
                                        aria-label="Hapus Kelas"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="kelasList.data.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-xs text-slate-400">
                                    Tidak ada data kelas.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-4 pb-4">
                    <Pagination :links="kelasList.links" />
                </div>
            </div>
        </div>

        <Modal :show="showModal" :title="isEditing ? 'Ubah Kelas' : 'Tambah Kelas Baru'" @close="showModal = false">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700">Nama Kelas *</label>
                    <input v-model="form.nama_kelas" type="text" placeholder="Contoh: XI PPLG 1" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                    <span v-if="form.errors.nama_kelas" class="text-[11px] text-red-600">{{ form.errors.nama_kelas }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Tingkat *</label>
                        <select v-model="form.tingkat" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            <option value="X">X (Sepuluh)</option>
                            <option value="XI">XI (Sebelas)</option>
                            <option value="XII">XII (Dua Belas)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700">Jurusan *</label>
                        <input v-model="form.jurusan" type="text" placeholder="Contoh: PPLG" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700">Tahun Ajaran *</label>
                    <select v-model="form.tahun_ajaran_id" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="" disabled>Pilih Tahun Ajaran</option>
                        <option v-for="t in tahunAjaranList" :key="t.id" :value="t.id">
                            {{ t.nama }} ({{ t.semester }}) {{ t.is_active ? '- Aktif' : '' }}
                        </option>
                    </select>
                    <span v-if="form.errors.tahun_ajaran_id" class="text-[11px] text-red-600">{{ form.errors.tahun_ajaran_id }}</span>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700">Wali Kelas *</label>
                    <select v-model="form.wali_kelas_id" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="" disabled>Pilih Guru sebagai Wali Kelas</option>
                        <option v-for="w in waliKelasList" :key="w.id" :value="w.id">
                            {{ w.name }} (@{{ w.username }})
                        </option>
                    </select>
                    <span v-if="form.errors.wali_kelas_id" class="text-[11px] text-red-600">{{ form.errors.wali_kelas_id }}</span>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-200">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-50 min-h-[44px]">Batal</button>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 text-xs font-medium text-white bg-blue-700 hover:bg-blue-800 rounded min-h-[44px]">Simpan</button>
                </div>
            </form>
        </Modal>

        <ConfirmDeleteModal
            :show="showDeleteModal"
            title="Hapus Kelas"
            :message="`Apakah Anda yakin ingin menghapus kelas '${deletingItem?.nama_kelas}'? Tindakan ini tidak dapat dibatalkan.`"
            :processing="deleteForm.processing"
            @close="showDeleteModal = false"
            @confirm="executeDelete"
        />
    </AppLayout>
</template>
