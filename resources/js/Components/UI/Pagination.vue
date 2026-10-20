<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap items-center justify-between gap-2 pt-4">
        <div class="flex flex-wrap gap-1">
            <template v-for="(link, key) in links" :key="key">
                <div
                    v-if="link.url === null"
                    class="px-3 py-1.5 text-xs text-slate-400 border border-slate-200 rounded min-h-[36px] min-w-[36px] flex items-center justify-center cursor-not-allowed select-none"
                    v-html="link.label"
                />
                <Link
                    v-else
                    :href="link.url"
                    class="px-3 py-1.5 text-xs rounded border min-h-[36px] min-w-[36px] flex items-center justify-center transition"
                    :class="[
                        link.active
                            ? 'bg-blue-700 text-white font-medium border-blue-700'
                            : 'bg-white text-slate-700 hover:bg-slate-50 border-slate-200'
                    ]"
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>
