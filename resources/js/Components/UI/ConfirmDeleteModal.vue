<script setup>
import Modal from './Modal.vue';
import { AlertTriangle } from '@lucide/vue';

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Konfirmasi Penghapusan',
    },
    message: {
        type: String,
        default: 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.',
    },
    processing: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['close', 'confirm']);
</script>

<template>
    <Modal :show="show" :title="title" max-width="md" @close="$emit('close')">
        <div class="space-y-4">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-red-100 text-red-600 rounded-full shrink-0">
                    <AlertTriangle class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        {{ message }}
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                <button
                    type="button"
                    @click="$emit('close')"
                    :disabled="processing"
                    class="px-4 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-50 transition min-h-[44px]"
                >
                    Batal
                </button>
                <button
                    type="button"
                    @click="$emit('confirm')"
                    :disabled="processing"
                    class="px-4 py-2 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700 transition disabled:opacity-50 min-h-[44px]"
                >
                    <span v-if="processing">Menghapus...</span>
                    <span v-else>Hapus Data</span>
                </button>
            </div>
        </div>
    </Modal>
</template>
