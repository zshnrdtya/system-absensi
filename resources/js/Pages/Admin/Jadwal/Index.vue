<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import Pagination from '../../../Components/UI/Pagination.vue';
import Modal from '../../../Components/UI/Modal.vue';
import ConfirmDeleteModal from '../../../Components/UI/ConfirmDeleteModal.vue';
import { Plus, Edit2, Trash2 } from '@lucide/vue';

const props = defineProps({
    jadwalList: Object,
    kelasList: Array,
    mapelList: Array,
    guruList: Array,
    filters: Object,
});

const kelasId = ref(props.filters.kelas_id || '');
const hari = ref(props.filters.hari || '');

const handleFilter = () => {
    router.get('/admin/jadwal', { kelas_id: kelasId.value, hari: hari.value }, { preserveState: true, replace: true });
};

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    kelas_id: props.kelasList[0]?.id || '',
    mapel_id: props.mapelList[0]?.id || '',
    guru_id: props.guruList[0]?.id || '',
    hari: 'senin',
    jam_mulai: '07:30',
    jam_selesai: '09:30',
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.kelas_id = props.kelasList[0]?.id || '';
    form.mapel_id = props.mapelList[0]?.id || '';
    form.guru_id = props.guruList[0]?.id || '';
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    editingId.value = item.id;
    form.clearErrors();
    form.kelas_id = item.kelas_id;
    form.mapel_id = item.mapel_id;
    form.guru_id = item.guru_id;
    form.hari = item.hari;
    form.jam_mulai = item.jam_mulai.substring(0, 5);
    form.jam_selesai = item.jam_selesai.substring(0, 5);
    showModal.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(`/admin/jadwal/${editingId.value}`, {
            onSuccess: () => { showModal.value = false; },
        });
    } else {
        form.post('/admin/jadwal', {
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
    deleteForm.delete(`/admin/jadwal/${deletingItem.value.id}`, {
        onSuccess: () => { showDeleteModal.value = false; },
    });
};
</script>

<template>
    <AppLayout title="Kelola Jadwal Pelajaran">
        <div class="space-y-6">
            <div class="bg-white p-4 rounded-lg border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex flex-col sm:flex-row gap-3 flex-1 max-w-xl">
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

                    <select
                        v-model="hari"
                        @change="handleFilter"
                        class="px-3 py-1.5 border border-slate-300 rounded text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                    >
                        <option value="">Semua Hari</option>
                        <option value="senin">Senin</option>
                        <option value="selasa">Selasa</option>
                        <option value="rabu">Rabu</option>
                        <option value="kamis">Kamis</option>
                        <option value="jumat">Jumat</option>
                        <option value="sabtu">Sabtu</option>
                    </select>
                </div>

                <button
                    type="button"
                    @click="openCreateModal"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-medium rounded transition min-h-[36px]"
                >
                    <Plus class="w-4 h-4" />
                    <span>Tambah Jadwal</span>
                </button>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-4 py-3">Hari & Waktu</th>
                                <th class="px-4 py-3">Kelas</th>
                                <th class="px-4 py-3">Mata Pelajaran</th>
                                <th class="px-4 py-3">Guru Pengajar</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in jadwalList.data" :key="item.id" class="hover:bg-slate-50/75">
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-slate-900 capitalize">{{ item.hari }}</span>
                                    <span class="block text-[11px] text-slate-500">{{ item.jam_mulai.substring(0, 5) }} - {{ item.jam_selesai.substring(0, 5) }} WIB</span>
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-800">{{ item.kelas?.nama_kelas }}</td>
                                <td class="px-4 py-3 text-slate-800">{{ item.mapel?.nama }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ item.guru?.name }}</td>
                                <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                    <button
                                        type="button"
                                        @click="openEditModal(item)"
                                        class="p-1.5 text-blue-700 hover:bg-blue-50 rounded"
                                        title="Edit"
                                        aria-label="Edit Jadwal"
                                    >
                                        <Edit2 class="w-4 h-4" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="confirmDelete(item)"
                                        class="p-1.5 text-red-600 hover:bg-red-50 rounded"
                                        title="Hapus"
                                        aria-label="Hapus Jadwal"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="jadwalList.data.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-xs text-slate-400">
                                    Tidak ada data jadwal pelajaran.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-4 pb-4">
                    <Pagination :links="jadwalList.links" />
                </div>
            </div>
        </div>

        <Modal :show="showModal" :title="isEditing ? 'Ubah Jadwal Pelajaran' : 'Tambah Jadwal Baru'" @close="showModal = false">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Kelas *</label>
                        <select v-model="form.kelas_id" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            <option v-for="k in kelasList" :key="k.id" :value="k.id">
                                {{ k.nama_kelas }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700">Mata Pelajaran *</label>
                        <select v-model="form.mapel_id" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            <option v-for="m in mapelList" :key="m.id" :value="m.id">
                                {{ m.nama }} ({{ m.kode }})
                            </option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700">Guru Pengajar (Guru Mapel) *</label>
                    <select v-model="form.guru_id" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option v-for="g in guruList" :key="g.id" :value="g.id">
                            {{ g.name }} (@{{ g.username }})
                        </option>
                    </select>
                    <span v-if="form.errors.guru_id" class="text-[11px] text-red-600">{{ form.errors.guru_id }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Hari *</label>
                        <select v-model="form.hari" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            <option value="senin">Senin</option>
                            <option value="selasa">Selasa</option>
                            <option value="rabu">Rabu</option>
                            <option value="kamis">Kamis</option>
                            <option value="jumat">Jumat</option>
                            <option value="sabtu">Sabtu</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700">Jam Mulai *</label>
                        <input v-model="form.jam_mulai" type="time" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                        <span v-if="form.errors.jam_mulai" class="text-[11px] text-red-600">{{ form.errors.jam_mulai }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700">Jam Selesai *</label>
                        <input v-model="form.jam_selesai" type="time" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                        <span v-if="form.errors.jam_selesai" class="text-[11px] text-red-600">{{ form.errors.jam_selesai }}</span>
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
            title="Hapus Jadwal Pelajaran"
            :message="`Apakah Anda yakin ingin menghapus jadwal ini? Tindakan ini tidak dapat dibatalkan.`"
            :processing="deleteForm.processing"
            @close="showDeleteModal = false"
            @confirm="executeDelete"
        />
    </AppLayout>
</template>
