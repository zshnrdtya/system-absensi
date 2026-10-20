<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\PresensiJam;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class LaporanController extends Controller
{
    public function rekap(Request $request): Response
    {
        $user = $request->user();
        $tipe = $request->input('tipe', 'harian');
        $dari = $request->input('dari', '2026-08-01');
        $sampai = $request->input('sampai', Carbon::today()->format('Y-m-d'));

        $accessibleClasses = $this->getAccessibleClasses($user);
        $kelasId = $request->input('kelas_id', $accessibleClasses->first()?->id);
        $selectedKelas = $accessibleClasses->firstWhere('id', (int) $kelasId);

        $rekapList = $this->getRekapData($user, $selectedKelas?->id, $dari, $sampai, $tipe);

        return Inertia::render('Laporan/Rekap', [
            'rekapList' => $rekapList,
            'accessibleClasses' => $accessibleClasses,
            'selectedKelas' => $selectedKelas,
            'filters' => [
                'kelas_id' => $kelasId,
                'dari' => $dari,
                'sampai' => $sampai,
                'tipe' => $tipe,
            ],
        ]);
    }

    public function exportPdf(Request $request): HttpResponse
    {
        $user = $request->user();
        $tipe = $request->input('tipe', 'harian');
        $dari = $request->input('dari', '2026-08-01');
        $sampai = $request->input('sampai', Carbon::today()->format('Y-m-d'));

        $accessibleClasses = $this->getAccessibleClasses($user);
        $kelasId = $request->input('kelas_id', $accessibleClasses->first()?->id);
        $kelas = $accessibleClasses->firstWhere('id', (int) $kelasId);

        $rekapList = $this->getRekapData($user, $kelas?->id, $dari, $sampai, $tipe);

        $pdf = Pdf::loadView('pdf.laporan-rekap', [
            'rekapList' => $rekapList,
            'kelas' => $kelas,
            'dari' => Carbon::parse($dari)->translatedFormat('d F Y'),
            'sampai' => Carbon::parse($sampai)->translatedFormat('d F Y'),
            'tipe' => $tipe,
        ]);

        $kelasSlug = $kelas ? str_replace(' ', '-', strtolower($kelas->nama_kelas)) : 'semua-kelas';
        $fileName = "rekap-presensi-{$kelasSlug}-{$dari}-sd-{$sampai}.pdf";

        return $pdf->download($fileName);
    }

    protected function getRekapData($user, ?int $kelasId, string $dari, string $sampai, string $tipe): array
    {
        if (! $kelasId && ! $user->isSiswa()) {
            return [];
        }

        if ($tipe === 'jam') {
            return $this->getRekapJamData($user, $kelasId, $dari, $sampai);
        }

        return $this->getRekapHarianData($user, $kelasId, $dari, $sampai);
    }

    /**
     * Query Agregasi Presensi Harian dengan INNER JOIN dan GROUP BY (Rubrik SQL).
     */
    protected function getRekapHarianData($user, ?int $kelasId, string $dari, string $sampai): array
    {
        $query = DB::table('siswa')
            ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->leftJoin('presensi', function ($join) use ($dari, $sampai) {
                $join->on('siswa.id', '=', 'presensi.siswa_id')
                    ->whereBetween('presensi.tanggal', [$dari, $sampai]);
            })
            ->select(
                'siswa.id as siswa_id',
                'siswa.nama',
                'siswa.nis',
                'kelas.nama_kelas',
                DB::raw("SUM(CASE WHEN presensi.status = 'hadir' THEN 1 ELSE 0 END) as total_hadir"),
                DB::raw("SUM(CASE WHEN presensi.status = 'izin' THEN 1 ELSE 0 END) as total_izin"),
                DB::raw("SUM(CASE WHEN presensi.status = 'sakit' THEN 1 ELSE 0 END) as total_sakit"),
                DB::raw("SUM(CASE WHEN presensi.status = 'alpa' THEN 1 ELSE 0 END) as total_alpa"),
                DB::raw("COUNT(presensi.id) as total_rekam")
            );

        if ($user->isSiswa() && $user->siswa) {
            $query->where('siswa.id', $user->siswa->id);
        } else {
            $query->where('siswa.kelas_id', $kelasId);
        }

        $results = $query->groupBy('siswa.id', 'siswa.nama', 'siswa.nis', 'kelas.nama_kelas')
            ->orderBy('siswa.nama')
            ->get();

        return $results->map(function ($row) {
            $hadir = (int) $row->total_hadir;
            $izin = (int) $row->total_izin;
            $sakit = (int) $row->total_sakit;
            $alpa = (int) $row->total_alpa;
            $totalHari = (int) $row->total_rekam;

            // Aturan Bisnis 1: Persentase = (Hadir + Izin + Sakit) / total hari efektif * 100%
            $hadirSah = $hadir + $izin + $sakit;
            $persentase = $totalHari > 0 ? round(($hadirSah / $totalHari) * 100, 1) : 100.0;

            return [
                'siswa_id' => $row->siswa_id,
                'nama' => $row->nama,
                'nis' => $row->nis,
                'kelas' => $row->nama_kelas,
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpa' => $alpa,
                'total_hari' => $totalHari,
                'persentase' => $persentase,
            ];
        })->toArray();
    }

    /**
     * Query Agregasi Presensi Jam Pelajaran (FR-ATT-10).
     */
    protected function getRekapJamData($user, ?int $kelasId, string $dari, string $sampai): array
    {
        $query = DB::table('siswa')
            ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->leftJoin('presensi_jam', function ($join) use ($dari, $sampai, $user) {
                $join->on('siswa.id', '=', 'presensi_jam.siswa_id')
                    ->whereBetween('presensi_jam.tanggal', [$dari, $sampai]);

                if ($user->isGuruMapel()) {
                    $join->join('jadwal_pelajaran', 'presensi_jam.jadwal_id', '=', 'jadwal_pelajaran.id')
                        ->where('jadwal_pelajaran.guru_id', $user->id);
                }
            })
            ->select(
                'siswa.id as siswa_id',
                'siswa.nama',
                'siswa.nis',
                'kelas.nama_kelas',
                DB::raw("SUM(CASE WHEN presensi_jam.status = 'hadir' THEN 1 ELSE 0 END) as total_hadir"),
                DB::raw("SUM(CASE WHEN presensi_jam.status = 'izin' THEN 1 ELSE 0 END) as total_izin"),
                DB::raw("SUM(CASE WHEN presensi_jam.status = 'sakit' THEN 1 ELSE 0 END) as total_sakit"),
                DB::raw("SUM(CASE WHEN presensi_jam.status = 'alpa' THEN 1 ELSE 0 END) as total_alpa"),
                DB::raw("COUNT(presensi_jam.id) as total_rekam")
            );

        if ($user->isSiswa() && $user->siswa) {
            $query->where('siswa.id', $user->siswa->id);
        } else {
            $query->where('siswa.kelas_id', $kelasId);
        }

        $results = $query->groupBy('siswa.id', 'siswa.nama', 'siswa.nis', 'kelas.nama_kelas')
            ->orderBy('siswa.nama')
            ->get();

        return $results->map(function ($row) {
            $hadir = (int) $row->total_hadir;
            $izin = (int) $row->total_izin;
            $sakit = (int) $row->total_sakit;
            $alpa = (int) $row->total_alpa;
            $totalJam = (int) $row->total_rekam;

            $hadirSah = $hadir + $izin + $sakit;
            $persentase = $totalJam > 0 ? round(($hadirSah / $totalJam) * 100, 1) : 100.0;

            return [
                'siswa_id' => $row->siswa_id,
                'nama' => $row->nama,
                'nis' => $row->nis,
                'kelas' => $row->nama_kelas,
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpa' => $alpa,
                'total_hari' => $totalJam,
                'persentase' => $persentase,
            ];
        })->toArray();
    }

    protected function getAccessibleClasses($user)
    {
        if ($user->isAdmin() || $user->isGuruMapel()) {
            return Kelas::with('waliKelas')->orderBy('nama_kelas')->get();
        }

        if ($user->isWaliKelas()) {
            return $user->kelasWali()->with('waliKelas')->orderBy('nama_kelas')->get();
        }

        if ($user->isGuruPiket()) {
            $today = Carbon::today()->format('Y-m-d');
            $kelasIds = $user->delegasiPiket()
                ->where('tgl_mulai', '<=', $today)
                ->where('tgl_selesai', '>=', $today)
                ->pluck('kelas_id');

            return Kelas::whereIn('id', $kelasIds)->with('waliKelas')->orderBy('nama_kelas')->get();
        }

        if ($user->isSiswa() && $user->siswa) {
            return Kelas::where('id', $user->siswa->kelas_id)->with('waliKelas')->get();
        }

        return collect();
    }
}
