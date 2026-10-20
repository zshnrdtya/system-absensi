<script setup>
import { Link } from '@inertiajs/vue3';
import { LogOut } from '@lucide/vue';

defineProps({
    user: {
        type: Object,
        required: true,
    },
    roleLabel: {
        type: String,
        required: true,
    },
    navLinks: {
        type: Array,
        required: true,
    },
});

defineEmits(['logout', 'link-click']);
</script>

<template>
    <div class="flex flex-col h-full">
        <!-- User Info Badge -->
        <div class="p-4 mx-3 my-3 bg-slate-50 border border-slate-200 rounded-lg">
            <p class="text-xs font-semibold text-slate-900 truncate">{{ user.name }}</p>
            <div class="mt-1 flex items-center justify-between">
                <span class="inline-block px-2 py-0.5 text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200 rounded">
                    {{ roleLabel }}
                </span>
                <span class="text-[11px] text-slate-400">@{{ user.username }}</span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-3 py-2 space-y-1 overflow-y-auto">
            <Link
                v-for="link in navLinks"
                :key="link.href"
                :href="link.href"
                @click="$emit('link-click')"
                :class="[
                    link.active
                        ? 'bg-blue-50 text-blue-700 font-medium border-blue-200'
                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 border-transparent',
                    'group flex items-center gap-3 px-3 py-2 text-xs rounded border transition-colors min-h-[44px]'
                ]"
            >
                <component :is="link.icon" class="w-4 h-4 shrink-0 text-slate-500 group-hover:text-slate-900" />
                <span>{{ link.label }}</span>
            </Link>
        </nav>

        <!-- Logout Action -->  
        <div class="p-3 border-t border-slate-200">
            <button
                type="button"
                @click="$emit('logout')"
                class="w-full flex items-center gap-3 px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-50 rounded transition-colors min-h-[44px]"
            >
                <LogOut class="w-4 h-4" />
                <span>Keluar dari Sistem</span>
            </button>
        </div>
    </div>
</template>
