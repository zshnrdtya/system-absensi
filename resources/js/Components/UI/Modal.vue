<script setup>
import { watch, onMounted, onUnmounted } from 'vue';
import { X } from '@lucide/vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: '',
    },
    maxWidth: {
        type: String,
        default: 'md',
    },
});

const emit = defineEmits(['close']);

const close = () => {
    emit('close');
};

const handleKeyDown = (e) => {
    if (e.key === 'Escape' && props.show) {
        close();
    }
};

onMounted(() => document.addEventListener('keydown', handleKeyDown));
onUnmounted(() => document.removeEventListener('keydown', handleKeyDown));

watch(() => props.show, (val) => {
    if (val) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});
</script>

<template>
    <teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <!-- Backdrop -->
            <div
                class="fixed inset-0 bg-slate-900/50 backdrop-blur-none transition-opacity"
                @click="close"
            ></div>

            <!-- Modal Panel -->
            <div
                class="relative bg-white rounded-lg border border-slate-200 text-left overflow-hidden shadow-xl transform transition-all sm:w-full z-10 my-8"
                :class="{
                    'sm:max-w-md': maxWidth === 'md',
                    'sm:max-w-lg': maxWidth === 'lg',
                    'sm:max-w-xl': maxWidth === 'xl',
                    'sm:max-w-2xl': maxWidth === '2xl',
                }"
            >
                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">
                        {{ title }}
                    </h3>
                    <button
                        type="button"
                        @click="close"
                        class="p-1 text-slate-400 hover:text-slate-600 rounded min-h-[36px] min-w-[36px] flex items-center justify-center"
                        aria-label="Tutup"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <slot />
                </div>
            </div>
        </div>
    </teleport>
</template>
