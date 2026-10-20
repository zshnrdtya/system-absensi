<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GrafikSiswaController extends Controller
{
    public function show(Request $request, ?int $id = null): Response
    {
        $user = $request->user();

        // Tentukan siswa yang dituju
        if ($user->isSiswa()) {
            $siswa = $user->siswa()->with('kelas')->firstOrFail();
            // Blackbox Skenario 19: Siswa buka ID siswa lain di URL -> 403
            if ($id !== null && (int) $id !== $siswa->id) {
                abort(403, 'Anda hanya dapat melihat grafik kehadiran milik Anda sendiri.');
            }
        } else {
            if ($id === null) {
                $siswa = Siswa::with('kelas')->firstOrFail();
            } else {
                $siswa = Siswa::with('kelas')->findOrFail($id);
            }
            $this->authorizeAccess($user, $siswa);
        }

        $dari = $request->input('dari', '2026-08-01');
        $sampai = $request->input('sampai', Carbon::today()->format('Y-m-d'));

        // 1. Data Donut Komposisi Status
        $komposisiRaw = Presensi::where('siswa_id', $siswa->id)
            ->whereBetween('tanggal', [$dari, $sampai])
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $komposisi = [
            'hadir' => $komposisiRaw['hadir'] ?? 0,
            'izin' => $komposisiRaw['izin'] ?? 0,
            'sakit' => $komposisiRaw['sakit'] ?? 0,
            'alpa' => $komposisiRaw['alpa'] ?? 0,
        ];

        // 2. Data Bulanan (Bar & Line Chart) - Rubrik SQL GROUP BY YEAR/MONTH
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $periodFormat = $isSqlite ? "strftime('%Y-%m', tanggal)" : "DATE_FORMAT(tanggal, '%Y-%m')";

        $bulananRaw = DB::table('presensi')
            ->where('siswa_id', $siswa->id)
            ->whereBetween('tanggal', [$dari, $sampai])
            ->select(
                DB::raw("{$periodFormat} as periode"),
                DB::raw("SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as total_hadir"),
                DB::raw("SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as total_izin"),
                DB::raw("SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as total_sakit"),
                DB::raw("SUM(CASE WHEN status = 'alpa' THEN 1 ELSE 0 END) as total_alpa"),
                DB::raw('COUNT(*) as total_hari')
            )
            ->groupBy('periode')
            ->orderBy('periode')
            ->get();

        $bulanan = $bulananRaw->map(function ($row) {
            $hadir = (int) $row->total_hadir;
            $izin = (int) $row->total_izin;
            $sakit = (int) $row->total_sakit;
            $alpa = (int) $row->total_alpa;
            $totalHari = (int) $row->total_hari;
            $persentase = $totalHari > 0 ? round((($hadir + $izin + $sakit) / $totalHari) * 100, 1) : 100.0;
            $label = Carbon::createFromFormat('Y-m', $row->periode)->translatedFormat('M Y');

            return [
                'periode' => $row->periode,
                'label' => $label,
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpa' => $alpa,
                'total_hari' => $totalHari,
                'persentase' => $persentase,
            ];
        })->toArray();

        return Inertia::render('Siswa/Grafik', [
            'siswa' => $siswa,
            'komposisi' => $komposisi,
            'bulanan' => $bulanan,
            'filters' => [
                'dari' => $dari,
                'sampai' => $sampai,
            ],
        ]);
    }

    protected function authorizeAccess($user, Siswa $siswa): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->isWaliKelas() && $user->kelasWali()->where('id', $siswa->kelas_id)->exists()) {
            return;
        }

        if ($user->isGuruPiket()) {
            $today = Carbon::today()->format('Y-m-d');
            $hasActiveDelegation = $user->delegasiPiket()
                ->where('kelas_id', $siswa->kelas_id)
                ->where('tgl_mulai', '<=', $today)
                ->where('tgl_selesai', '>=', $today)
                ->exists();

            if ($hasActiveDelegation) {
                return;
            }
        }

        abort(403, 'Anda tidak berwenang melihat grafik siswa ini.');
    }
}
