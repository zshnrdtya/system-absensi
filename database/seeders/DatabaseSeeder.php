<?php

namespace Database\Seeders;

use App\Models\Izin;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\PiketDelegasi;
use App\Models\Presensi;
use App\Models\PresensiJam;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Pengguna untuk 5 Role
        $admin = User::create([
            'name' => 'Administrator Kurikulum',
            'username' => 'admin',
            'email' => 'admin@1dex-sybsen.sch.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $wali1 = User::create([
            'name' => 'Budi Santoso, S.Kom',
            'username' => 'wali_pplg1',
            'email' => 'budi.santoso@1dex-sybsen.sch.id',
            'password' => Hash::make('guru123'),
            'role' => 'wali_kelas',
            'is_active' => true,
        ]);

        $wali2 = User::create([
            'name' => 'Siti Rahmawati, S.Pd',
            'username' => 'wali_pplg2',
            'email' => 'siti.rahmawati@1dex-sybsen.sch.id',
            'password' => Hash::make('guru123'),
            'role' => 'wali_kelas',
            'is_active' => true,
        ]);

        $guruWeb = User::create([
            'name' => 'Hendra Pratama, M.Kom',
            'username' => 'guru_web',
            'email' => 'hendra.pratama@1dex-sybsen.sch.id',
            'password' => Hash::make('guru123'),
            'role' => 'guru_mapel',
            'is_active' => true,
        ]);

        $guruDb = User::create([
            'name' => 'Dewi Kusuma, S.T',
            'username' => 'guru_db',
            'email' => 'dewi.kusuma@1dex-sybsen.sch.id',
            'password' => Hash::make('guru123'),
            'role' => 'guru_mapel',
            'is_active' => true,
        ]);

        $guruPbo = User::create([
            'name' => 'Rahmat Hidayat, M.Kom',
            'username' => 'guru_pbo',
            'email' => 'rahmat.hidayat@1dex-sybsen.sch.id',
            'password' => Hash::make('guru123'),
            'role' => 'guru_mapel',
            'is_active' => true,
        ]);

        $guruPiket = User::create([
            'name' => 'Agus Setiawan, S.Pd',
            'username' => 'guru_piket',
            'email' => 'agus.setiawan@1dex-sybsen.sch.id',
            'password' => Hash::make('guru123'),
            'role' => 'guru_piket',
            'is_active' => true,
        ]);

        // 2. Tahun Ajaran Aktif
        $tahunAjaran = TahunAjaran::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tgl_mulai' => '2026-07-13',
            'tgl_selesai' => '2026-12-18',
            'is_active' => true,
        ]);

        // 3. Kelas
        $kelas1 = Kelas::create([
            'nama_kelas' => 'XI PPLG 1',
            'tingkat' => 'XI',
            'jurusan' => 'PPLG',
            'tahun_ajaran_id' => $tahunAjaran->id,
            'wali_kelas_id' => $wali1->id,
        ]);

        $kelas2 = Kelas::create([
            'nama_kelas' => 'XI PPLG 2',
            'tingkat' => 'XI',
            'jurusan' => 'PPLG',
            'tahun_ajaran_id' => $tahunAjaran->id,
            'wali_kelas_id' => $wali2->id,
        ]);

        // 4. Mata Pelajaran
        $mapelWeb = MataPelajaran::create(['kode' => 'PPLG-WEB', 'nama' => 'Pemrograman Web & Perangkat Bergerak']);
        $mapelDb = MataPelajaran::create(['kode' => 'PPLG-PBD', 'nama' => 'Basis Data Lanjut']);
        $mapelPbo = MataPelajaran::create(['kode' => 'PPLG-PBO', 'nama' => 'Pemrograman Berorientasi Objek']);
        $mapelMat = MataPelajaran::create(['kode' => 'UMUM-MAT', 'nama' => 'Matematika Terapan']);

        // 5. Jadwal Pelajaran
        $jadwalList = [
            // XI PPLG 1
            JadwalPelajaran::create([
                'kelas_id' => $kelas1->id,
                'mapel_id' => $mapelWeb->id,
                'guru_id' => $guruWeb->id,
                'hari' => 'senin',
                'jam_mulai' => '07:30:00',
                'jam_selesai' => '09:30:00',
            ]),
            JadwalPelajaran::create([
                'kelas_id' => $kelas1->id,
                'mapel_id' => $mapelDb->id,
                'guru_id' => $guruDb->id,
                'hari' => 'selasa',
                'jam_mulai' => '09:45:00',
                'jam_selesai' => '11:45:00',
            ]),
            JadwalPelajaran::create([
                'kelas_id' => $kelas1->id,
                'mapel_id' => $mapelPbo->id,
                'guru_id' => $guruPbo->id,
                'hari' => 'rabu',
                'jam_mulai' => '07:30:00',
                'jam_selesai' => '09:30:00',
            ]),
            // XI PPLG 2
            JadwalPelajaran::create([
                'kelas_id' => $kelas2->id,
                'mapel_id' => $mapelWeb->id,
                'guru_id' => $guruWeb->id,
                'hari' => 'kamis',
                'jam_mulai' => '07:30:00',
                'jam_selesai' => '09:30:00',
            ]),
            JadwalPelajaran::create([
                'kelas_id' => $kelas2->id,
                'mapel_id' => $mapelDb->id,
                'guru_id' => $guruDb->id,
                'hari' => 'jumat',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '10:00:00',
            ]),
        ];

        // 6. Delegasi Guru Piket ke Kelas XI PPLG 1 (periode bulan Oktober)
        PiketDelegasi::create([
            'guru_id' => $guruPiket->id,
            'kelas_id' => $kelas1->id,
            'tgl_mulai' => '2026-10-01',
            'tgl_selesai' => '2026-10-31',
            'dibuat_oleh' => $admin->id,
        ]);

        // 7. Siswa (6 siswa per kelas)
        $siswaData = [
            // Kelas 1
            ['nis' => '102401', 'nisn' => '0081234561', 'nama' => 'Ahmad Fauzi', 'jk' => 'L', 'username' => 'siswa_ahmad', 'kelas_id' => $kelas1->id],
            ['nis' => '102402', 'nisn' => '0081234562', 'nama' => 'Bintang Pratama', 'jk' => 'L', 'username' => 'siswa_bintang', 'kelas_id' => $kelas1->id],
            ['nis' => '102403', 'nisn' => '0081234563', 'nama' => 'Citra Lestari', 'jk' => 'P', 'username' => 'siswa_citra', 'kelas_id' => $kelas1->id],
            ['nis' => '102404', 'nisn' => '0081234564', 'nama' => 'Dewi Anggraini', 'jk' => 'P', 'username' => 'siswa_dewi', 'kelas_id' => $kelas1->id],
            ['nis' => '102405', 'nisn' => '0081234565', 'nama' => 'Eko Prasetyo', 'jk' => 'L', 'username' => 'siswa_eko', 'kelas_id' => $kelas1->id],
            ['nis' => '102406', 'nisn' => '0081234566', 'nama' => 'Fani Rahmawati', 'jk' => 'P', 'username' => 'siswa_fani', 'kelas_id' => $kelas1->id],
            // Kelas 2
            ['nis' => '102407', 'nisn' => '0081234567', 'nama' => 'Gilang Ramadhan', 'jk' => 'L', 'username' => 'siswa_gilang', 'kelas_id' => $kelas2->id],
            ['nis' => '102408', 'nisn' => '0081234568', 'nama' => 'Hana Pertiwi', 'jk' => 'P', 'username' => 'siswa_hana', 'kelas_id' => $kelas2->id],
            ['nis' => '102409', 'nisn' => '0081234569', 'nama' => 'Indra Wijaya', 'jk' => 'L', 'username' => 'siswa_indra', 'kelas_id' => $kelas2->id],
            ['nis' => '102410', 'nisn' => '0081234570', 'nama' => 'Jihan Salsabila', 'jk' => 'P', 'username' => 'siswa_jihan', 'kelas_id' => $kelas2->id],
            ['nis' => '102411', 'nisn' => '0081234571', 'nama' => 'Kurnia Mega', 'jk' => 'L', 'username' => 'siswa_kurnia', 'kelas_id' => $kelas2->id],
            ['nis' => '102412', 'nisn' => '0081234572', 'nama' => 'Lestari Indah', 'jk' => 'P', 'username' => 'siswa_lestari', 'kelas_id' => $kelas2->id],
        ];

        $siswaModels = [];
        foreach ($siswaData as $data) {
            $user = User::create([
                'name' => $data['nama'],
                'username' => $data['username'],
                'email' => $data['username'].'@siswa.1dex-sybsen.sch.id',
                'password' => Hash::make('siswa123'),
                'role' => 'siswa',
                'is_active' => true,
            ]);

            $siswa = Siswa::create([
                'user_id' => $user->id,
                'nis' => $data['nis'],
                'nisn' => $data['nisn'],
                'nama' => $data['nama'],
                'jenis_kelamin' => $data['jk'],
                'kelas_id' => $data['kelas_id'],
            ]);

            $siswaModels[] = $siswa;
        }

        // 8. Contoh Pengajuan Izin
        $izinAhmad = Izin::create([
            'siswa_id' => $siswaModels[0]->id, // Ahmad
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

        $izinCitra = Izin::create([
            'siswa_id' => $siswaModels[2]->id, // Citra
            'jenis' => 'izin',
            'tgl_mulai' => '2026-10-21',
            'tgl_selesai' => '2026-10-21',
            'alasan' => 'Menghadiri acara wisuda kakak kandung di luar kota.',
            'bukti_path' => null,
            'status' => 'pending',
            'diproses_oleh' => null,
            'catatan' => null,
            'diproses_pada' => null,
        ]);

        $izinBintang = Izin::create([
            'siswa_id' => $siswaModels[1]->id, // Bintang
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

        // 9. Riwayat Presensi Harian (Agustus s/d Oktober 2026 untuk hari kerja)
        $startDate = Carbon::create(2026, 8, 3);
        $endDate = Carbon::create(2026, 10, 20);
        $period = CarbonPeriod::create($startDate, $endDate);

        $dayMap = [
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
        ];

        foreach ($period as $date) {
            if (! $date->isWeekday()) {
                continue;
            }

            $dateStr = $date->format('Y-m-d');
            $dayOfWeek = $date->dayOfWeek; // 1 = Monday, 5 = Friday
            $dayName = $dayMap[$dayOfWeek] ?? null;

            foreach ($siswaModels as $idx => $s) {
                // Tentukan status default hadir, sesekali izin/sakit/alpa untuk variasi data grafik
                $status = 'hadir';
                $keterangan = null;
                $izinId = null;

                // Cek tanggal izin yang disetujui untuk Ahmad
                if ($s->id === $siswaModels[0]->id && in_array($dateStr, ['2026-10-12', '2026-10-13'])) {
                    $status = 'sakit';
                    $keterangan = 'Sakit (Disetujui)';
                    $izinId = $izinAhmad->id;
                } else {
                    // Variasi realistis: alpa sesekali di hari tertentu
                    if ($idx === 4 && $dateStr === '2026-09-14') {
                        $status = 'alpa';
                        $keterangan = 'Tanpa keterangan';
                    } elseif ($idx === 5 && $dateStr === '2026-08-25') {
                        $status = 'izin';
                        $keterangan = 'Izin keluarga';
                    } elseif ($idx === 1 && $dateStr === '2026-09-08') {
                        $status = 'sakit';
                        $keterangan = 'Sakit flu';
                    }
                }

                $presensi = Presensi::create([
                    'siswa_id' => $s->id,
                    'kelas_id' => $s->kelas_id,
                    'tanggal' => $dateStr,
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'izin_id' => $izinId,
                    'dicatat_oleh' => ($s->kelas_id === $kelas1->id) ? $wali1->id : $wali2->id,
                ]);

                // Presensi Jam Pelajaran jika ada jadwal pada hari tersebut
                if ($dayName) {
                    $jadwalHariIni = array_filter($jadwalList, fn ($j) => $j->kelas_id === $s->kelas_id && $j->hari === $dayName);
                    foreach ($jadwalHariIni as $j) {
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
