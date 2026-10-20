<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import AdminDashboard from '../Components/Dashboard/AdminDashboard.vue';
import WaliKelasDashboard from '../Components/Dashboard/WaliKelasDashboard.vue';
import GuruPiketDashboard from '../Components/Dashboard/GuruPiketDashboard.vue';
import GuruMapelDashboard from '../Components/Dashboard/GuruMapelDashboard.vue';
import SiswaDashboard from '../Components/Dashboard/SiswaDashboard.vue';

defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
    todayDate: {
        type: String,
        default: '',
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user || {});
</script>

<template>
    <AppLayout title="Dashboard">
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-lg border border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900 tracking-tight">
                        Selamat Datang, {{ user.name }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ todayDate }} &bull; Anda masuk sebagai <span class="font-medium text-slate-700 capitalize">{{ user.role.replace('_', ' ') }}</span>
                    </p>
                </div>
            </div>

            <!-- Role-Specific Views (Rule of 300 Modular Components) -->
            <AdminDashboard v-if="user.role === 'admin'" :stats="stats" />
            <WaliKelasDashboard v-else-if="user.role === 'wali_kelas'" :stats="stats" />
            <GuruPiketDashboard v-else-if="user.role === 'guru_piket'" :stats="stats" />
            <GuruMapelDashboard v-else-if="user.role === 'guru_mapel'" :stats="stats" />
            <SiswaDashboard v-else-if="user.role === 'siswa'" :stats="stats" />
        </div>
    </AppLayout>
</template>
