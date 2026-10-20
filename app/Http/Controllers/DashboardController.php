<?php

namespace App\Http\Controllers;

use App\Models\Izin;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\PiketDelegasi;
use App\Models\Presensi;
use App\Models\PresensiJam;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $today = Carbon::today()->format('Y-m-d');
        $dayOfWeek = Carbon::today()->dayOfWeek; // 1 = Monday, 5 = Friday
        $dayMap = [1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu'];
        $todayDayName = $dayMap[$dayOfWeek] ?? '';

        $stats = [];

        if ($user->isAdmin()) {
            $stats = [
                'total_user' => User::count(),
                'total_kelas' => Kelas::count(),
                'total_siswa' => Siswa::count(),
                'tahun_ajaran_aktif' => TahunAjaran::where('is_active', true)->first(),
                'presensi_hari_ini' => [
                    'hadir' => Presensi::where('tanggal', $today)->where('status', 'hadir')->count(),
                    'izin' => Presensi::where('tanggal', $today)->where('status', 'izin')->count(),
                    'sakit' => Presensi::where('tanggal', $today)->where('status', 'sakit')->count(),
                    'alpa' => Presensi::where('tanggal', $today)->where('status', 'alpa')->count(),
                ],
                'izin_pending_count' => Izin::where('status', 'pending')->count(),
            ];
        } elseif ($user->isWaliKelas()) {
            $kelas = Kelas::where('wali_kelas_id', $user->id)->first();
            $totalSiswa = $kelas ? $kelas->siswa()->count() : 0;
            $presensiHariIni = $kelas ? [
                'hadir' => Presensi::where('kelas_id', $kelas->id)->where('tanggal', $today)->where('status', 'hadir')->count(),
                'izin' => Presensi::where('kelas_id', $kelas->id)->where('tanggal', $today)->where('status', 'izin')->count(),
                'sakit' => Presensi::where('kelas_id', $kelas->id)->where('tanggal', $today)->where('status', 'sakit')->count(),
                'alpa' => Presensi::where('kelas_id', $kelas->id)->where('tanggal', $today)->where('status', 'alpa')->count(),
            ] : ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0];

            $izinPending = $kelas
                ? Izin::whereHas('siswa', fn ($q) => $q->where('kelas_id', $kelas->id))
                    ->where('status', 'pending')
                    ->with('siswa')
                    ->latest()
                    ->take(5)
                    ->get()
                : [];

            $stats = [
                'kelas' => $kelas,
                'total_siswa' => $totalSiswa,
                'presensi_hari_ini' => $presensiHariIni,
                'izin_pending' => $izinPending,
            ];
        } elseif ($user->isGuruPiket()) {
            $delegasiAktif = PiketDelegasi::where('guru_id', $user->id)
                ->where('tgl_mulai', '<=', $today)
                ->where('tgl_selesai', '>=', $today)
                ->with('kelas')
                ->get();

            $kelasIds = $delegasiAktif->pluck('kelas_id');

            $izinPending = Izin::whereHas('siswa', fn ($q) => $q->whereIn('kelas_id', $kelasIds))
                ->where('status', 'pending')
                ->with(['siswa.kelas'])
                ->latest()
                ->take(5)
                ->get();

            $presensiHariIni = [
                'hadir' => Presensi::whereIn('kelas_id', $kelasIds)->where('tanggal', $today)->where('status', 'hadir')->count(),
                'izin' => Presensi::whereIn('kelas_id', $kelasIds)->where('tanggal', $today)->where('status', 'izin')->count(),
                'sakit' => Presensi::whereIn('kelas_id', $kelasIds)->where('tanggal', $today)->where('status', 'sakit')->count(),
                'alpa' => Presensi::whereIn('kelas_id', $kelasIds)->where('tanggal', $today)->where('status', 'alpa')->count(),
            ];

            $stats = [
                'delegasi_aktif' => $delegasiAktif,
                'presensi_hari_ini' => $presensiHariIni,
                'izin_pending' => $izinPending,
            ];
        } elseif ($user->isGuruMapel()) {
            $jadwalHariIni = JadwalPelajaran::where('guru_id', $user->id)
                ->where('hari', $todayDayName)
                ->with(['kelas', 'mapel'])
                ->get()
                ->map(function ($jadwal) use ($today) {
                    $sudahPresensi = PresensiJam::where('jadwal_id', $jadwal->id)
                        ->where('tanggal', $today)
                        ->exists();

                    $jadwal->sudah_presensi = $sudahPresensi;

                    return $jadwal;
                });

            $stats = [
                'hari' => $todayDayName,
                'jadwal_hari_ini' => $jadwalHariIni,
                'total_jadwal_mingguan' => JadwalPelajaran::where('guru_id', $user->id)->count(),
            ];
        } elseif ($user->isSiswa()) {
            $siswa = $user->siswa()->with('kelas')->first();

            $presensiTotal = [
                'hadir' => 0,
                'izin' => 0,
                'sakit' => 0,
                'alpa' => 0,
            ];
            $persentase = 100;

            if ($siswa) {
                $presensiList = Presensi::where('siswa_id', $siswa->id)->get();
                $presensiTotal['hadir'] = $presensiList->where('status', 'hadir')->count();
                $presensiTotal['izin'] = $presensiList->where('status', 'izin')->count();
                $presensiTotal['sakit'] = $presensiList->where('status', 'sakit')->count();
                $presensiTotal['alpa'] = $presensiList->where('status', 'alpa')->count();

                $totalHari = $presensiList->count();
                if ($totalHari > 0) {
                    $hadirSah = $presensiTotal['hadir'] + $presensiTotal['izin'] + $presensiTotal['sakit'];
                    $persentase = round(($hadirSah / $totalHari) * 100, 1);
                }
            }

            $presensiHariIni = $siswa
                ? Presensi::where('siswa_id', $siswa->id)->where('tanggal', $today)->first()
                : null;

            $riwayatIzin = $siswa
                ? Izin::where('siswa_id', $siswa->id)->latest()->take(3)->get()
                : [];

            $stats = [
                'siswa' => $siswa,
                'presensi_total' => $presensiTotal,
                'persentase_kehadiran' => $persentase,
                'presensi_hari_ini' => $presensiHariIni,
                'riwayat_izin' => $riwayatIzin,
            ];
        }

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'todayDate' => Carbon::today()->translatedFormat('l, d F Y'),
        ]);
    }
}
