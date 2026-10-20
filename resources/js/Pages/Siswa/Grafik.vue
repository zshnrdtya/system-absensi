<script setup>
import { computed, ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { Doughnut, Bar, Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    ArcElement,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
} from 'chart.js';
import { ArrowLeft, Filter, Calendar } from '@lucide/vue';

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    ArcElement,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement
);

const props = defineProps({
    siswa: Object,
    komposisi: Object,
    bulanan: Array,
    filters: Object,
});

const dari = ref(props.filters.dari || '2026-08-01');
const sampai = ref(props.filters.sampai || '');

const applyFilter = () => {
    router.get(`/siswa/${props.siswa.id}/grafik`, {
        dari: dari.value,
        sampai: sampai.value,
    }, { preserveState: true, replace: true });
};

// 1. Donut Chart Data
const doughnutData = computed(() => ({
    labels: ['Hadir', 'Izin', 'Sakit', 'Alpa'],
    datasets: [{
        data: [
            props.komposisi.hadir || 0,
            props.komposisi.izin || 0,
            props.komposisi.sakit || 0,
            props.komposisi.alpa || 0,
        ],
        backgroundColor: ['#047857', '#B45309', '#6D28D9', '#B91C1C'],
        borderWidth: 1,
    }],
}));

// 2. Bar Chart Data (Bulanan)
const barData = computed(() => ({
    labels: props.bulanan.map(b => b.label),
    datasets: [
        { label: 'Hadir', backgroundColor: '#047857', data: props.bulanan.map(b => b.hadir) },
        { label: 'Izin', backgroundColor: '#B45309', data: props.bulanan.map(b => b.izin) },
        { label: 'Sakit', backgroundColor: '#6D28D9', data: props.bulanan.map(b => b.sakit) },
        { label: 'Alpa', backgroundColor: '#B91C1C', data: props.bulanan.map(b => b.alpa) },
    ],
}));

// 3. Line Chart Data (Tren Persentase)
const lineData = computed(() => ({
    labels: props.bulanan.map(b => b.label),
    datasets: [{
        label: 'Persentase Kehadiran (%)',
        borderColor: '#1D4ED8',
        backgroundColor: '#1D4ED8',
        data: props.bulanan.map(b => b.persentase),
        tension: 0.2,
        fill: false,
    }],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: { boxWidth: 12, font: { size: 11 } },
        },
    },
};

const lineOptions = {
    ...chartOptions,
    scales: {
        y: {
            min: 0,
            max: 100,
            ticks: { callback: (val) => `${val}%` },
        },
    },
};
</script>

<template>
    <AppLayout title="Grafik Kehadiran Siswa">
        <div class="space-y-6">
            <!-- Header Card -->
            <div class="bg-white p-6 rounded-lg border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">
                        Grafik Kehadiran: {{ siswa.nama }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        NIS: {{ siswa.nis }} &bull; Kelas: {{ siswa.kelas?.nama_kelas }} &bull; Periode: {{ filters.dari }} s/d {{ filters.sampai }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <input
                        v-model="dari"
                        type="date"
                        class="px-2.5 py-1.5 border border-slate-300 rounded text-xs text-slate-900 focus:outline-none"
                    />
                    <span class="text-xs text-slate-500">s/d</span>
                    <input
                        v-model="sampai"
                        type="date"
                        class="px-2.5 py-1.5 border border-slate-300 rounded text-xs text-slate-900 focus:outline-none"
                    />
                    <button
                        type="button"
                        @click="applyFilter"
                        class="px-3 py-1.5 bg-blue-700 hover:bg-blue-800 text-white text-xs font-medium rounded transition min-h-[36px]"
                    >
                        Terapkan
                    </button>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Donut Chart: Komposisi Kehadiran -->
                <div class="bg-white p-5 rounded-lg border border-slate-200 flex flex-col justify-between">
                    <h3 class="text-xs font-semibold text-slate-900 mb-4">Komposisi Status Kehadiran</h3>
                    <div class="h-64 relative">
                        <Doughnut :data="doughnutData" :options="chartOptions" />
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-4 text-center text-xs">
                        <div>
                            <span class="text-emerald-700 font-bold block">{{ komposisi.hadir }}</span>
                            <span class="text-[10px] text-slate-500">Hadir</span>
                        </div>
                        <div>
                            <span class="text-amber-700 font-bold block">{{ komposisi.izin }}</span>
                            <span class="text-[10px] text-slate-500">Izin</span>
                        </div>
                        <div>
                            <span class="text-purple-700 font-bold block">{{ komposisi.sakit }}</span>
                            <span class="text-[10px] text-slate-500">Sakit</span>
                        </div>
                        <div>
                            <span class="text-red-700 font-bold block">{{ komposisi.alpa }}</span>
                            <span class="text-[10px] text-slate-500">Alpa</span>
                        </div>
                    </div>
                </div>

                <!-- Bar Chart: Jumlah per Bulan -->
                <div class="bg-white p-5 rounded-lg border border-slate-200 lg:col-span-2">
                    <h3 class="text-xs font-semibold text-slate-900 mb-4">Frekuensi Kehadiran Per Bulan</h3>
                    <div class="h-64 relative">
                        <Bar :data="barData" :options="chartOptions" />
                    </div>
                </div>
            </div>

            <!-- Line Chart: Tren Persentase Kehadiran -->
            <div class="bg-white p-5 rounded-lg border border-slate-200">
                <h3 class="text-xs font-semibold text-slate-900 mb-2">Tren Persentase Kehadiran Bulanan</h3>
                <p class="text-[11px] text-slate-500 mb-4">
                    Target kehadiran standar sekolah minimal 85%. Evaluasi jika grafik menunjukkan penurunan berkala.
                </p>
                <div class="h-64 relative">
                    <Line :data="lineData" :options="lineOptions" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
