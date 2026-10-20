<?php

namespace Database\Seeders;

use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\PiketDelegasi;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Seeder;

class AkademikSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $wali1 = User::where('username', 'wali_pplg1')->first();
        $wali2 = User::where('username', 'wali_pplg2')->first();
        $guruWeb = User::where('username', 'guru_web')->first();
        $guruDb = User::where('username', 'guru_db')->first();
        $guruPbo = User::where('username', 'guru_pbo')->first();
        $guruPiket = User::where('username', 'guru_piket')->first();

        // 1. Tahun Ajaran Aktif
        $ta = TahunAjaran::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tgl_mulai' => '2026-07-13',
            'tgl_selesai' => '2026-12-18',
            'is_active' => true,
        ]);

        // 2. Kelas
        $kelas1 = Kelas::create([
            'nama_kelas' => 'XI PPLG 1',
            'tingkat' => 'XI',
            'jurusan' => 'PPLG',
            'tahun_ajaran_id' => $ta->id,
            'wali_kelas_id' => $wali1->id,
        ]);

        $kelas2 = Kelas::create([
            'nama_kelas' => 'XI PPLG 2',
            'tingkat' => 'XI',
            'jurusan' => 'PPLG',
            'tahun_ajaran_id' => $ta->id,
            'wali_kelas_id' => $wali2->id,
        ]);

        // 3. Mata Pelajaran
        $mapelWeb = MataPelajaran::create(['kode' => 'PPLG-WEB', 'nama' => 'Pemrograman Web & Perangkat Bergerak']);
        $mapelDb = MataPelajaran::create(['kode' => 'PPLG-PBD', 'nama' => 'Basis Data Lanjut']);
        $mapelPbo = MataPelajaran::create(['kode' => 'PPLG-PBO', 'nama' => 'Pemrograman Berorientasi Objek']);
        MataPelajaran::create(['kode' => 'UMUM-MAT', 'nama' => 'Matematika Terapan']);

        // 4. Jadwal Pelajaran
        JadwalPelajaran::create([
            'kelas_id' => $kelas1->id,
            'mapel_id' => $mapelWeb->id,
            'guru_id' => $guruWeb->id,
            'hari' => 'senin',
            'jam_mulai' => '07:30:00',
            'jam_selesai' => '09:30:00',
        ]);
        JadwalPelajaran::create([
            'kelas_id' => $kelas1->id,
            'mapel_id' => $mapelDb->id,
            'guru_id' => $guruDb->id,
            'hari' => 'selasa',
            'jam_mulai' => '09:45:00',
            'jam_selesai' => '11:45:00',
        ]);
        JadwalPelajaran::create([
            'kelas_id' => $kelas1->id,
            'mapel_id' => $mapelPbo->id,
            'guru_id' => $guruPbo->id,
            'hari' => 'rabu',
            'jam_mulai' => '07:30:00',
            'jam_selesai' => '09:30:00',
        ]);
        JadwalPelajaran::create([
            'kelas_id' => $kelas2->id,
            'mapel_id' => $mapelWeb->id,
            'guru_id' => $guruWeb->id,
            'hari' => 'kamis',
            'jam_mulai' => '07:30:00',
            'jam_selesai' => '09:30:00',
        ]);
        JadwalPelajaran::create([
            'kelas_id' => $kelas2->id,
            'mapel_id' => $mapelDb->id,
            'guru_id' => $guruDb->id,
            'hari' => 'jumat',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
        ]);

        // 5. Delegasi Guru Piket ke Kelas XI PPLG 1 (bulan Oktober)
        PiketDelegasi::create([
            'guru_id' => $guruPiket->id,
            'kelas_id' => $kelas1->id,
            'tgl_mulai' => '2026-10-01',
            'tgl_selesai' => '2026-10-31',
            'dibuat_oleh' => $admin->id,
        ]);
    }
}
