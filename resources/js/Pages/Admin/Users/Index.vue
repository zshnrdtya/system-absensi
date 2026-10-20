<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import Pagination from '../../../Components/UI/Pagination.vue';
import Modal from '../../../Components/UI/Modal.vue';
import ConfirmDeleteModal from '../../../Components/UI/ConfirmDeleteModal.vue';
import { Search, Plus, Edit2, Trash2 } from '@lucide/vue';

const props = defineProps({
    users: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const role = ref(props.filters.role || '');

const handleFilter = () => {
    router.get('/admin/users', { search: search.value, role: role.value }, { preserveState: true, replace: true });
};

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    username: '',
    email: '',
    role: 'siswa',
    password: '',
    is_active: true,
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
    form.name = item.name;
    form.username = item.username;
    form.email = item.email || '';
    form.role = item.role;
    form.password = '';
    form.is_active = Boolean(item.is_active);
    showModal.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(`/admin/users/${editingId.value}`, {
            onSuccess: () => { showModal.value = false; },
        });
    } else {
        form.post('/admin/users', {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
};

const showDeleteModal = ref(false);
const deletingUser = ref(null);
const deleteForm = useForm({});

const confirmDelete = (item) => {
    deletingUser.value = item;
    showDeleteModal.value = true;
};

const executeDelete = () => {
    deleteForm.delete(`/admin/users/${deletingUser.value.id}`, {
        onSuccess: () => { showDeleteModal.value = false; },
    });
};
</script>

<template>
    <AppLayout title="Kelola Pengguna">
        <div class="space-y-6">
            <!-- Header & Filter Bar -->
            <div class="bg-white p-4 rounded-lg border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex flex-col sm:flex-row gap-3 flex-1 max-w-xl">
                    <div class="relative flex-1">
                        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                        <input
                            v-model="search"
                            @keyup.enter="handleFilter"
                            type="text"
                            placeholder="Cari nama, username, atau email..."
                            class="w-full pl-9 pr-3 py-1.5 border border-slate-300 rounded text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                        />
                    </div>
                    <select
                        v-model="role"
                        @change="handleFilter"
                        class="px-3 py-1.5 border border-slate-300 rounded text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                    >
                        <option value="">Semua Peran</option>
                        <option value="admin">Administrator</option>
                        <option value="wali_kelas">Wali Kelas</option>
                        <option value="guru_mapel">Guru Mapel</option>
                        <option value="guru_piket">Guru Piket</option>
                        <option value="siswa">Siswa</option>
                    </select>
                </div>

                <button
                    type="button"
                    @click="openCreateModal"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-medium rounded transition min-h-[36px]"
                >
                    <Plus class="w-4 h-4" />
                    <span>Tambah Pengguna</span>
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-4 py-3">Nama Lengkap</th>
                                <th class="px-4 py-3">Username</th>
                                <th class="px-4 py-3">Peran</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in users.data" :key="item.id" class="hover:bg-slate-50/75">
                                <td class="px-4 py-3 font-medium text-slate-900">
                                    {{ item.name }}
                                    <span v-if="item.email" class="block text-[11px] text-slate-400 font-normal">{{ item.email }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">@{{ item.username }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-block px-2 py-0.5 text-[10px] font-medium rounded capitalize bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ item.role.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-block px-2 py-0.5 text-[10px] font-medium rounded"
                                        :class="item.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200'"
                                    >
                                        {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                    <button
                                        type="button"
                                        @click="openEditModal(item)"
                                        class="p-1.5 text-blue-700 hover:bg-blue-50 rounded"
                                        title="Edit"
                                        aria-label="Edit Pengguna"
                                    >
                                        <Edit2 class="w-4 h-4" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="confirmDelete(item)"
                                        class="p-1.5 text-red-600 hover:bg-red-50 rounded"
                                        title="Hapus"
                                        aria-label="Hapus Pengguna"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-xs text-slate-400">
                                    Tidak ada data pengguna yang sesuai dengan kriteria pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-4 pb-4">
                    <Pagination :links="users.links" />
                </div>
            </div>
        </div>

        <!-- Create / Edit Modal -->
        <Modal :show="showModal" :title="isEditing ? 'Ubah Data Pengguna' : 'Tambah Pengguna Baru'" @close="showModal = false">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700">Nama Lengkap *</label>
                    <input v-model="form.name" type="text" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                    <span v-if="form.errors.name" class="text-[11px] text-red-600">{{ form.errors.name }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Username *</label>
                        <input v-model="form.username" type="text" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                        <span v-if="form.errors.username" class="text-[11px] text-red-600">{{ form.errors.username }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700">Email (Opsional)</label>
                        <input v-model="form.email" type="email" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                        <span v-if="form.errors.email" class="text-[11px] text-red-600">{{ form.errors.email }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Peran Akun *</label>
                        <select v-model="form.role" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            <option value="admin">Administrator</option>
                            <option value="wali_kelas">Wali Kelas</option>
                            <option value="guru_mapel">Guru Mapel</option>
                            <option value="guru_piket">Guru Piket</option>
                            <option value="siswa">Siswa</option>
                        </select>
                        <span v-if="form.errors.role" class="text-[11px] text-red-600">{{ form.errors.role }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700">
                            {{ isEditing ? 'Kata Sandi Baru (Kosongkan jika tetap)' : 'Kata Sandi (Default: password123)' }}
                        </label>
                        <input v-model="form.password" type="password" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                        <span v-if="form.errors.password" class="text-[11px] text-red-600">{{ form.errors.password }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 rounded border-slate-300" />
                    <label for="is_active" class="text-xs text-slate-700 cursor-pointer">Status Akun Aktif</label>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-200">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-50 min-h-[44px]">Batal</button>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 text-xs font-medium text-white bg-blue-700 hover:bg-blue-800 rounded min-h-[44px]">Simpan</button>
                </div>
            </form>
        </Modal>

        <!-- Delete Modal -->
        <ConfirmDeleteModal
            :show="showDeleteModal"
            title="Hapus Pengguna"
            :message="`Apakah Anda yakin ingin menghapus akun '${deletingUser?.name}'? Tindakan ini tidak dapat dibatalkan.`"
            :processing="deleteForm.processing"
            @close="showDeleteModal = false"
            @confirm="executeDelete"
        />
    </AppLayout>
</template>
