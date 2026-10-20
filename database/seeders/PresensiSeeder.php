<?php

namespace Database\Seeders;

use App\Models\Izin;
use App\Models\JadwalPelajaran;
use App\Models\Presensi;
use App\Models\PresensiJam;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;

class PresensiSeeder extends Seeder
{
    public function run(): void
    {
        $wali1 = User::where('username', 'wali_pplg1')->first();
        $wali2 = User::where('username', 'wali_pplg2')->first();
        $siswaList = Siswa::with('kelas')->get();
        $jadwalList = JadwalPelajaran::all();

        // 1. Contoh Izin Siswa
        $ahmad = $siswaList->firstWhere('nis', '102401');
        $bintang = $siswaList->firstWhere('nis', '102402');
        $citra = $siswaList->firstWhere('nis', '102403');

        $izinAhmad = Izin::create([
            'siswa_id' => $ahmad->id,
            'jenis' => 'sakit',
            'tgl_mulai' => '2026-10-12',
            'tgl_selesai' => '2026-10-13',
            'alasan' => 'Demam tinggi dan istirahat dokter.',
            'bukti_path' => null,
            'status' => 'approved',
            'diproses_oleh' => $wali1->id,
            'catatan' => 'Surat dokter terverifikasi.',
            'diproses_pada' => '2026-10-12 07:15:00',
        ]);

        Izin::create([
            'siswa_id' => $citra->id,
            'jenis' => 'izin',
            'tgl_mulai' => '2026-10-21',
            'tgl_selesai' => '2026-10-21',
            'alasan' => 'Menghadiri acara wisuda keluarga.',
            'bukti_path' => null,
            'status' => 'pending',
        ]);

        Izin::create([
            'siswa_id' => $bintang->id,
            'jenis' => 'izin',
            'tgl_mulai' => '2026-10-05',
            'tgl_selesai' => '2026-10-05',
            'alasan' => 'Kepentingan keluarga mendadak.',
            'bukti_path' => null,
            'status' => 'rejected',
            'diproses_oleh' => $wali1->id,
            'catatan' => 'Surat keterangan dari orang tua belum dilampirkan.',
            'diproses_pada' => '2026-10-05 08:00:00',
        ]);

        // 2. Riwayat Presensi 3 Bulan (Agustus s/d Oktober 2026)
        $startDate = Carbon::create(2026, 8, 3);
        $endDate = Carbon::create(2026, 10, 20);
        $period = CarbonPeriod::create($startDate, $endDate);

        $dayMap = [1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat'];

        foreach ($period as $date) {
            if (! $date->isWeekday()) {
                continue;
            }

            $dateStr = $date->format('Y-m-d');
            $dayName = $dayMap[$date->dayOfWeek] ?? null;

            foreach ($siswaList as $idx => $s) {
                $status = 'hadir';
                $keterangan = null;
                $izinId = null;

                if ($s->id === $ahmad->id && in_array($dateStr, ['2026-10-12', '2026-10-13'])) {
                    $status = 'sakit';
                    $keterangan = 'Sakit (Disetujui)';
                    $izinId = $izinAhmad->id;
                } elseif ($idx === 4 && $dateStr === '2026-09-14') {
                    $status = 'alpa';
                    $keterangan = 'Tanpa keterangan';
                } elseif ($idx === 5 && $dateStr === '2026-08-25') {
                    $status = 'izin';
                    $keterangan = 'Izin keluarga';
                } elseif ($idx === 1 && $dateStr === '2026-09-08') {
                    $status = 'sakit';
                    $keterangan = 'Sakit flu';
                }

                Presensi::create([
                    'siswa_id' => $s->id,
                    'kelas_id' => $s->kelas_id,
                    'tanggal' => $dateStr,
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'izin_id' => $izinId,
                    'dicatat_oleh' => ($s->kelas->nama_kelas === 'XI PPLG 1') ? $wali1->id : $wali2->id,
                ]);

                if ($dayName) {
                    $jadwals = $jadwalList->where('kelas_id', $s->kelas_id)->where('hari', $dayName);
                    foreach ($jadwals as $j) {
                        PresensiJam::create([
                            'siswa_id' => $s->id,
                            'jadwal_id' => $j->id,
                            'tanggal' => $dateStr,
                            'status' => $status,
                            'keterangan' => $keterangan,
                            'izin_id' => $izinId,
                            'dicatat_oleh' => $j->guru_id,
                        ]);
                    }
                }
            }
        }
    }
}
