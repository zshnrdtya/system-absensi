<?php

namespace App\Services;

use App\Models\Izin;
use App\Models\JadwalPelajaran;
use App\Models\Presensi;
use App\Models\PresensiJam;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AttendanceSyncService
{
    /**
     * Sinkronisasi presensi harian dan presensi jam saat izin disetujui (FR-APR-04).
     */
    public function syncApprovedIzin(Izin $izin, int $processorId): void
    {
        $siswa = $izin->siswa;
        if (! $siswa) {
            return;
        }

        $dayMap = [
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
        ];

        DB::transaction(function () use ($izin, $siswa, $processorId, $dayMap) {
            $period = CarbonPeriod::create($izin->tgl_mulai, $izin->tgl_selesai);

            foreach ($period as $date) {
                // Lewati hari Minggu (presensi hanya hari kerja)
                if ($date->dayOfWeek === Carbon::SUNDAY) {
                    continue;
                }

                $dateStr = $date->format('Y-m-d');
                $dayName = $dayMap[$date->dayOfWeek] ?? null;

                // 1. Sinkronisasi Presensi Harian
                Presensi::updateOrCreate(
                    [
                        'siswa_id' => $siswa->id,
                        'tanggal' => $dateStr,
                    ],
                    [
                        'kelas_id' => $siswa->kelas_id,
                        'status' => $izin->jenis,
                        'keterangan' => 'Disetujui dari pengajuan '.$izin->jenis,
                        'izin_id' => $izin->id,
                        'dicatat_oleh' => $processorId,
                    ]
                );

                // 2. Sinkronisasi Presensi Jam Pelajaran
                if ($dayName) {
                    $jadwals = JadwalPelajaran::where('kelas_id', $siswa->kelas_id)
                        ->where('hari', $dayName)
                        ->get();

                    foreach ($jadwals as $jadwal) {
                        PresensiJam::updateOrCreate(
                            [
                                'siswa_id' => $siswa->id,
                                'jadwal_id' => $jadwal->id,
                                'tanggal' => $dateStr,
                            ],
                            [
                                'status' => $izin->jenis,
                                'keterangan' => 'Disetujui dari pengajuan '.$izin->jenis,
                                'izin_id' => $izin->id,
                                'dicatat_oleh' => $processorId,
                            ]
                        );
                    }
                }
            }
        });
    }
}
