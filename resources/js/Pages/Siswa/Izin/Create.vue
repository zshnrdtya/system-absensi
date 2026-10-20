<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import { UploadCloud, AlertCircle, ArrowLeft, Image as ImageIcon } from '@lucide/vue';

const props = defineProps({
    siswa: Object,
});

const form = useForm({
    jenis: 'izin',
    tgl_mulai: '',
    tgl_selesai: '',
    alasan: '',
    bukti: null,
});

const previewUrl = ref(null);
const clientError = ref('');

const handleFileChange = (e) => {
    clientError.value = '';
    const file = e.target.files[0];
    if (!file) {
        form.bukti = null;
        previewUrl.value = null;
        return;
    }

    // Validasi tipe berkas client-side
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!allowedTypes.includes(file.type)) {
        clientError.value = 'Format berkas tidak didukung. Mohon lampirkan gambar dengan format JPG, JPEG, atau PNG (Berkas PDF tidak diizinkan).';
        e.target.value = '';
        form.bukti = null;
        previewUrl.value = null;
        return;
    }

    // Validasi ukuran berkas maksimal 2 MB
    if (file.size > 2 * 1024 * 1024) {
        clientError.value = 'Ukuran berkas melebihi batas 2 MB. Silakan kompres atau pilih berkas foto lain.';
        e.target.value = '';
        form.bukti = null;
        previewUrl.value = null;
        return;
    }

    form.bukti = file;
    previewUrl.value = URL.createObjectURL(file);
};

const submitForm = () => {
    clientError.value = '';

    if (!form.tgl_mulai) {
        clientError.value = 'Tanggal mulai izin wajib diisi.';
        return;
    }

    if (!form.tgl_selesai) {
        clientError.value = 'Tanggal selesai izin wajib diisi.';
        return;
    }

    if (form.tgl_selesai < form.tgl_mulai) {
        clientError.value = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
        return;
    }

    if (form.jenis === 'sakit' && !form.bukti) {
        clientError.value = 'Surat keterangan dokter wajib dilampirkan untuk jenis sakit.';
        return;
    }

    form.post('/izin/ajukan', {
        forceFormData: true,
    });
};
</script>

<template>
    <AppLayout title="Ajukan Izin / Sakit">
        <div class="max-w-2xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900 tracking-tight">Formulir Pengajuan Izin / Sakit</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Isi rincian pengajuan izin Anda untuk diverifikasi oleh Wali Kelas.</p>
                </div>
                <Link
                    href="/izin/riwayat"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-50 min-h-[36px]"
                >
                    <ArrowLeft class="w-3.5 h-3.5" />
                    <span>Riwayat Izin</span>
                </Link>
            </div>

            <!-- Client Error Alert -->
            <div v-if="clientError" class="p-3 bg-red-50 border border-red-200 text-red-700 rounded text-xs flex items-center gap-2">
                <AlertCircle class="w-4 h-4 shrink-0" />
                <span>{{ clientError }}</span>
            </div>

            <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-sm">
                <form @submit.prevent="submitForm" class="space-y-5">
                    <!-- Info Siswa -->
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded text-xs text-slate-600">
                        <span class="font-semibold text-slate-900">{{ siswa.nama }}</span> &bull; NIS: {{ siswa.nis }} &bull; Kelas: {{ siswa.kelas?.nama_kelas }}
                    </div>

                    <!-- Jenis Pengajuan -->
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-2">Jenis Pengajuan *</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label
                                class="flex items-center justify-center gap-2 p-3 border rounded cursor-pointer transition text-xs font-medium"
                                :class="form.jenis === 'izin' ? 'border-blue-600 bg-blue-50 text-blue-700 font-semibold' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                            >
                                <input v-model="form.jenis" type="radio" value="izin" class="sr-only" />
                                <span>Izin (Acara Keluarga / Penting)</span>
                            </label>

                            <label
                                class="flex items-center justify-center gap-2 p-3 border rounded cursor-pointer transition text-xs font-medium"
                                :class="form.jenis === 'sakit' ? 'border-purple-600 bg-purple-50 text-purple-700 font-semibold' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                            >
                                <input v-model="form.jenis" type="radio" value="sakit" class="sr-only" />
                                <span>Sakit (Surat Dokter Wajib)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Rentang Tanggal -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Tanggal Mulai *</label>
                            <input
                                v-model="form.tgl_mulai"
                                type="date"
                                required
                                class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                            />
                            <span v-if="form.errors.tgl_mulai" class="text-[11px] text-red-600 block mt-1">{{ form.errors.tgl_mulai }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700">Tanggal Selesai *</label>
                            <input
                                v-model="form.tgl_selesai"
                                type="date"
                                required
                                class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                            />
                            <span v-if="form.errors.tgl_selesai" class="text-[11px] text-red-600 block mt-1">{{ form.errors.tgl_selesai }}</span>
                        </div>
                    </div>

                    <!-- Alasan -->
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Alasan Lengkap *</label>
                        <textarea
                            v-model="form.alasan"
                            rows="3"
                            required
                            placeholder="Jelaskan alasan izin atau kondisi kesehatan yang dialami..."
                            class="mt-1 w-full px-3 py-2 border border-slate-300 rounded text-xs text-slate-900 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                        ></textarea>
                        <span v-if="form.errors.alasan" class="text-[11px] text-red-600 block mt-1">{{ form.errors.alasan }}</span>
                    </div>

                    <!-- Upload Foto Bukti -->
                    <div>
                        <label class="block text-xs font-medium text-slate-700">
                            Foto Bukti / Surat Keterangan {{ form.jenis === 'sakit' ? '(Wajib)' : '(Opsional)' }}
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">Format: JPG, JPEG, atau PNG. Ukuran maksimal: 2 MB.</p>

                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-lg hover:border-slate-400 transition bg-slate-50">
                            <div class="space-y-2 text-center">
                                <div v-if="previewUrl" class="mb-3">
                                    <img :src="previewUrl" alt="Pratinjau Bukti" class="max-h-48 mx-auto rounded border border-slate-200" />
                                </div>
                                <div v-else class="flex justify-center text-slate-400">
                                    <UploadCloud class="w-8 h-8" />
                                </div>

                                <div class="flex text-xs text-slate-600 justify-center">
                                    <label class="relative cursor-pointer bg-white rounded font-medium text-blue-700 hover:text-blue-800 px-2 py-1 border border-slate-200">
                                        <span>Pilih Foto Bukti</span>
                                        <input type="file" accept="image/jpeg,image/png,image/jpg" @change="handleFileChange" class="sr-only" />
                                    </label>
                                </div>
                            </div>
                        </div>
                        <span v-if="form.errors.bukti" class="text-[11px] text-red-600 block mt-1">{{ form.errors.bukti }}</span>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="pt-4 border-t border-slate-200 flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2.5 bg-blue-700 hover:bg-blue-800 text-white text-xs font-medium rounded transition disabled:opacity-50 min-h-[44px]"
                        >
                            <span v-if="form.processing">Mengirim Pengajuan...</span>
                            <span v-else>Kirim Pengajuan Izin</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
