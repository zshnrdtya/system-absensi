<script setup>
import { ref, computed } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import SidebarNav from '../Components/Nav/SidebarNav.vue';
import {
    LayoutDashboard,
    Users,
    Calendar,
    GraduationCap,
    BookOpen,
    Clock,
    UserCheck,
    FileText,
    LogOut,
    Menu,
    X,
    FileSignature,
    CheckSquare,
    BarChart3,
    CalendarDays
} from '@lucide/vue';

defineProps({
    title: {
        type: String,
        default: '',
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user || {});
const flash = computed(() => page.props.flash || {});

const isMobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
};

const logout = () => {
    router.post('/logout');
};

const roleLabel = computed(() => {
    switch (user.value.role) {
        case 'admin': return 'Administrator';
        case 'wali_kelas': return 'Wali Kelas';
        case 'guru_piket': return 'Guru Piket';
        case 'guru_mapel': return 'Guru Mata Pelajaran';
        case 'siswa': return 'Siswa';
        default: return 'Pengguna';
    }
});

const navLinks = computed(() => {
    const role = user.value.role;
    const links = [
        { label: 'Dashboard', href: '/dashboard', icon: LayoutDashboard, active: page.url === '/dashboard' },
    ];

    if (role === 'admin') {
        links.push(
            { label: 'Kelola Pengguna', href: '/admin/users', icon: Users, active: page.url.startsWith('/admin/users') },
            { label: 'Tahun Ajaran', href: '/admin/tahun-ajaran', icon: Calendar, active: page.url.startsWith('/admin/tahun-ajaran') },
            { label: 'Kelola Kelas', href: '/admin/kelas', icon: GraduationCap, active: page.url.startsWith('/admin/kelas') },
            { label: 'Kelola Siswa', href: '/admin/siswa', icon: Users, active: page.url.startsWith('/admin/siswa') },
            { label: 'Mata Pelajaran', href: '/admin/mapel', icon: BookOpen, active: page.url.startsWith('/admin/mapel') },
            { label: 'Jadwal Pelajaran', href: '/admin/jadwal', icon: Clock, active: page.url.startsWith('/admin/jadwal') },
            { label: 'Delegasi Piket', href: '/admin/piket-delegasi', icon: UserCheck, active: page.url.startsWith('/admin/piket-delegasi') },
            { label: 'Rekap Presensi', href: '/laporan/rekap', icon: FileText, active: page.url.startsWith('/laporan/rekap') }
        );
    } else if (role === 'wali_kelas') {
        links.push(
            { label: 'Presensi Harian', href: '/presensi/harian', icon: CheckSquare, active: page.url.startsWith('/presensi/harian') },
            { label: 'Presensi Jam', href: '/presensi/jam', icon: Clock, active: page.url.startsWith('/presensi/jam') },
            { label: 'Persetujuan Izin', href: '/izin/approval', icon: FileSignature, active: page.url.startsWith('/izin/approval') },
            { label: 'Rekap Kelas', href: '/laporan/rekap', icon: FileText, active: page.url.startsWith('/laporan/rekap') }
        );
    } else if (role === 'guru_piket') {
        links.push(
            { label: 'Presensi Harian', href: '/presensi/harian', icon: CheckSquare, active: page.url.startsWith('/presensi/harian') },
            { label: 'Presensi Jam', href: '/presensi/jam', icon: Clock, active: page.url.startsWith('/presensi/jam') },
            { label: 'Persetujuan Izin', href: '/izin/approval', icon: FileSignature, active: page.url.startsWith('/izin/approval') },
            { label: 'Rekap Delegasi', href: '/laporan/rekap', icon: FileText, active: page.url.startsWith('/laporan/rekap') }
        );
    } else if (role === 'guru_mapel') {
        links.push(
            { label: 'Jadwal Mengajar', href: '/jadwal/mengajar', icon: CalendarDays, active: page.url.startsWith('/jadwal/mengajar') },
            { label: 'Presensi Jam Pelajaran', href: '/presensi/jam', icon: Clock, active: page.url.startsWith('/presensi/jam') },
            { label: 'Rekap Mapel', href: '/laporan/rekap', icon: FileText, active: page.url.startsWith('/laporan/rekap') }
        );
    } else if (role === 'siswa') {
        links.push(
            { label: 'Ajukan Izin / Sakit', href: '/izin/ajukan', icon: FileSignature, active: page.url.startsWith('/izin/ajukan') },
            { label: 'Riwayat Izin', href: '/izin/riwayat', icon: FileText, active: page.url.startsWith('/izin/riwayat') },
            { label: 'Jadwal Pelajaran', href: '/siswa/jadwal', icon: CalendarDays, active: page.url.startsWith('/siswa/jadwal') },
            { label: 'Riwayat Presensi', href: '/siswa/presensi', icon: CheckSquare, active: page.url.startsWith('/siswa/presensi') },
            { label: 'Grafik Kehadiran', href: '/siswa/grafik', icon: BarChart3, active: page.url.startsWith('/siswa/grafik') }
        );
    }

    return links;
});
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex">
        <Head :title="title ? `${title} - 1dex-sybsen` : '1dex-sybsen'" />

        <!-- Sidebar Desktop -->
        <aside class="hidden lg:flex lg:flex-col w-64 bg-white border-r border-slate-200">
            <div class="h-16 flex items-center gap-3 px-6 border-b border-slate-200">
                <div class="w-8 h-8 rounded bg-blue-700 text-white font-semibold flex items-center justify-center text-sm">
                    1D
                </div>
                <div>
                    <span class="font-semibold text-sm tracking-tight text-slate-900 block">1dex-sybsen</span>
                    <span class="text-[11px] text-slate-500 block leading-none">Presensi Terpadu</span>
                </div>
            </div>

            <SidebarNav
                :user="user"
                :role-label="roleLabel"
                :nav-links="navLinks"
                @logout="logout"
            />
        </aside>

        <!-- Mobile Drawer Overlay -->
        <div
            v-if="isMobileMenuOpen"
            class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"
            @click="closeMobileMenu"
        ></div>

        <!-- Sidebar Mobile Drawer -->
        <aside
            :class="[
                isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full',
                'fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 transition-transform duration-200 ease-in-out lg:hidden flex flex-col'
            ]"
        >
            <div class="h-16 flex items-center justify-between px-6 border-b border-slate-200">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded bg-blue-700 text-white font-semibold flex items-center justify-center text-sm">
                        1D
                    </div>
                    <span class="font-semibold text-sm tracking-tight text-slate-900">1dex-sybsen</span>
                </div>
                <button
                    type="button"
                    @click="closeMobileMenu"
                    class="p-2 text-slate-500 hover:text-slate-700 rounded min-h-[44px] min-w-[44px] flex items-center justify-center"
                    aria-label="Tutup Menu"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <SidebarNav
                :user="user"
                :role-label="roleLabel"
                :nav-links="navLinks"
                @logout="logout"
                @link-click="closeMobileMenu"
            />
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Header Topbar -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="toggleMobileMenu"
                        class="p-2 text-slate-500 hover:text-slate-700 lg:hidden min-h-[44px] min-w-[44px] flex items-center justify-center rounded"
                        aria-label="Buka Menu"
                    >
                        <Menu class="w-6 h-6" />
                    </button>
                    <h1 class="text-base font-semibold text-slate-900 truncate">
                        {{ title || 'Dashboard' }}
                    </h1>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-xs font-semibold text-slate-900">{{ user.name }}</span>
                        <span class="text-[11px] text-slate-500">{{ roleLabel }}</span>
                    </div>
                    <button
                        type="button"
                        @click="logout"
                        class="p-2 text-slate-400 hover:text-red-600 rounded transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center"
                        title="Keluar"
                        aria-label="Keluar"
                    >
                        <LogOut class="w-5 h-5" />
                    </button>
                </div>
            </header>

            <!-- Flash Message Banner -->
            <div v-if="flash.success || flash.error || flash.message" class="px-4 sm:px-6 lg:px-8 pt-4">
                <div
                    v-if="flash.success"
                    class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded text-xs flex items-center justify-between"
                >
                    <span>{{ flash.success }}</span>
                </div>
                <div
                    v-if="flash.error"
                    class="p-3 bg-red-50 border border-red-200 text-red-800 rounded text-xs flex items-center justify-between"
                >
                    <span>{{ flash.error }}</span>
                </div>
                <div
                    v-if="flash.message"
                    class="p-3 bg-blue-50 border border-blue-200 text-blue-800 rounded text-xs flex items-center justify-between"
                >
                    <span>{{ flash.message }}</span>
                </div>
            </div>

            <!-- Page Body Slot -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
