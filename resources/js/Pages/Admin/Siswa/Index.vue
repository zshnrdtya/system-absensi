<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import Pagination from '../../../Components/UI/Pagination.vue';
import Modal from '../../../Components/UI/Modal.vue';
import ConfirmDeleteModal from '../../../Components/UI/ConfirmDeleteModal.vue';
import { Search, Plus, Edit2, Trash2 } from '@lucide/vue';

const props = defineProps({
    siswaList: Object,
    kelasList: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const kelasId = ref(props.filters.kelas_id || '');

const handleFilter = () => {
    router.get('/admin/siswa', { search: search.value, kelas_id: kelasId.value }, { preserveState: true, replace: true });
};

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    nis: '',
    nisn: '',
    nama: '',
    jenis_kelamin: 'L',
    kelas_id: props.kelasList[0]?.id || '',
    username: '',
    password: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.kelas_id = props.kelasList[0]?.id || '';
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    editingId.value = item.id;
    form.clearErrors();
    form.nis = item.nis;
    form.nisn = item.nisn || '';
    form.nama = item.nama;
    form.jenis_kelamin = item.jenis_kelamin;
    form.kelas_id = item.kelas_id;
    showModal.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(`/admin/siswa/${editingId.value}`, {
            onSuccess: () => { showModal.value = false; },
        });
    } else {
        form.post('/admin/siswa', {
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
    deleteForm.delete(`/admin/siswa/${deletingItem.value.id}`, {
        onSuccess: () => { showDeleteModal.value = false; },
    });
};
</script>

<template>
    <AppLayout title="Kelola Siswa">
        <div class="space-y-6">
            <div class="bg-white p-4 rounded-lg border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex flex-col sm:flex-row gap-3 flex-1 max-w-xl">
                    <div class="relative flex-1">
                        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                        <input
                            v-model="search"
                            @keyup.enter="handleFilter"
                            type="text"
                            placeholder="Cari nama, NIS, atau NISN..."
                            class="w-full pl-9 pr-3 py-1.5 border border-slate-300 rounded text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                        />
                    </div>
                    <select
                        v-model="kelasId"
                        @change="handleFilter"
                        class="px-3 py-1.5 border border-slate-300 rounded text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                    >
                        <option value="">Semua Kelas</option>
                        <option v-for="k in kelasList" :key="k.id" :value="k.id">
                            {{ k.nama_kelas }}
                        </option>
                    </select>
                </div>

                <button
                    type="button"
                    @click="openCreateModal"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-medium rounded transition min-h-[36px]"
                >
                    <Plus class="w-4 h-4" />
                    <span>Tambah Siswa</span>
                </button>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-4 py-3">Nama Siswa</th>
                                <th class="px-4 py-3">NIS / NISN</th>
                                <th class="px-4 py-3">L/P</th>
                                <th class="px-4 py-3">Kelas</th>
                                <th class="px-4 py-3">Akun User</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in siswaList.data" :key="item.id" class="hover:bg-slate-50/75">
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ item.nama }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ item.nis }} <span v-if="item.nisn">/ {{ item.nisn }}</span></td>
                                <td class="px-4 py-3 font-medium">{{ item.jenis_kelamin }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ item.kelas?.nama_kelas || '-' }}</td>
                                <td class="px-4 py-3 text-slate-500">@{{ item.user?.username || '-' }}</td>
                                <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                    <button
                                        type="button"
                                        @click="openEditModal(item)"
                                        class="p-1.5 text-blue-700 hover:bg-blue-50 rounded"
                                        title="Edit"
                                        aria-label="Edit Siswa"
                                    >
                                        <Edit2 class="w-4 h-4" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="confirmDelete(item)"
                                        class="p-1.5 text-red-600 hover:bg-red-50 rounded"
                                        title="Hapus"
                                        aria-label="Hapus Siswa"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="siswaList.data.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-xs text-slate-400">
                                    Tidak ada data siswa.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-4 pb-4">
                    <Pagination :links="siswaList.links" />
                </div>
            </div>
        </div>

        <Modal :show="showModal" :title="isEditing ? 'Ubah Data Siswa' : 'Tambah Siswa Baru'" @close="showModal = false">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700">Nama Lengkap Siswa *</label>
                    <input v-model="form.nama" type="text" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                    <span v-if="form.errors.nama" class="text-[11px] text-red-600">{{ form.errors.nama }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">NIS (Nomor Induk Siswa) *</label>
                        <input v-model="form.nis" type="text" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                        <span v-if="form.errors.nis" class="text-[11px] text-red-600">{{ form.errors.nis }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700">NISN (Opsional)</label>
                        <input v-model="form.nisn" type="text" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                        <span v-if="form.errors.nisn" class="text-[11px] text-red-600">{{ form.errors.nisn }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Jenis Kelamin *</label>
                        <select v-model="form.jenis_kelamin" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            <option value="L">Laki-laki (L)</option>
                            <option value="P">Perempuan (P)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700">Kelas *</label>
                        <select v-model="form.kelas_id" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            <option v-for="k in kelasList" :key="k.id" :value="k.id">
                                {{ k.nama_kelas }} ({{ k.jurusan }})
                            </option>
                        </select>
                        <span v-if="form.errors.kelas_id" class="text-[11px] text-red-600">{{ form.errors.kelas_id }}</span>
                    </div>
                </div>

                <div v-if="!isEditing" class="pt-2 border-t border-slate-200">
                    <p class="text-[11px] font-medium text-slate-500 mb-2">Akun Pengguna Otomatis:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Username Khusus (Opsional)</label>
                            <input v-model="form.username" type="text" placeholder="Default: siswa_{nis}" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Kata Sandi (Default: siswa123)</label>
                            <input v-model="form.password" type="password" placeholder="Minimal 6 karakter" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                        </div>
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
            title="Hapus Siswa"
            :message="`Apakah Anda yakin ingin menghapus siswa '${deletingItem?.nama}'? Tindakan ini akan menghapus akun login siswa yang bersangkutan.`"
            :processing="deleteForm.processing"
            @close="showDeleteModal = false"
            @confirm="executeDelete"
        />
    </AppLayout>
</template>
