<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        $kelas1 = Kelas::where('nama_kelas', 'XI PPLG 1')->first();
        $kelas2 = Kelas::where('nama_kelas', 'XI PPLG 2')->first();
        $passwordSiswa = Hash::make('siswa123');

        $siswaData = [
            ['nis' => '102401', 'nisn' => '0081234561', 'nama' => 'Ahmad Fauzi', 'jk' => 'L', 'username' => 'siswa_ahmad', 'kelas_id' => $kelas1->id],
            ['nis' => '102402', 'nisn' => '0081234562', 'nama' => 'Bintang Pratama', 'jk' => 'L', 'username' => 'siswa_bintang', 'kelas_id' => $kelas1->id],
            ['nis' => '102403', 'nisn' => '0081234563', 'nama' => 'Citra Lestari', 'jk' => 'P', 'username' => 'siswa_citra', 'kelas_id' => $kelas1->id],
            ['nis' => '102404', 'nisn' => '0081234564', 'nama' => 'Dewi Anggraini', 'jk' => 'P', 'username' => 'siswa_dewi', 'kelas_id' => $kelas1->id],
            ['nis' => '102405', 'nisn' => '0081234565', 'nama' => 'Eko Prasetyo', 'jk' => 'L', 'username' => 'siswa_eko', 'kelas_id' => $kelas1->id],
            ['nis' => '102406', 'nisn' => '0081234566', 'nama' => 'Fani Rahmawati', 'jk' => 'P', 'username' => 'siswa_fani', 'kelas_id' => $kelas1->id],
            ['nis' => '102407', 'nisn' => '0081234567', 'nama' => 'Gilang Ramadhan', 'jk' => 'L', 'username' => 'siswa_gilang', 'kelas_id' => $kelas2->id],
            ['nis' => '102408', 'nisn' => '0081234568', 'nama' => 'Hana Pertiwi', 'jk' => 'P', 'username' => 'siswa_hana', 'kelas_id' => $kelas2->id],
            ['nis' => '102409', 'nisn' => '0081234569', 'nama' => 'Indra Wijaya', 'jk' => 'L', 'username' => 'siswa_indra', 'kelas_id' => $kelas2->id],
            ['nis' => '102410', 'nisn' => '0081234570', 'nama' => 'Jihan Salsabila', 'jk' => 'P', 'username' => 'siswa_jihan', 'kelas_id' => $kelas2->id],
            ['nis' => '102411', 'nisn' => '0081234571', 'nama' => 'Kurnia Mega', 'jk' => 'L', 'username' => 'siswa_kurnia', 'kelas_id' => $kelas2->id],
            ['nis' => '102412', 'nisn' => '0081234572', 'nama' => 'Lestari Indah', 'jk' => 'P', 'username' => 'siswa_lestari', 'kelas_id' => $kelas2->id],
        ];

        foreach ($siswaData as $data) {
            $user = User::create([
                'name' => $data['nama'],
                'username' => $data['username'],
                'email' => $data['username'].'@siswa.1dex-sybsen.sch.id',
                'password' => $passwordSiswa,
                'role' => 'siswa',
                'is_active' => true,
            ]);

            Siswa::create([
                'user_id' => $user->id,
                'nis' => $data['nis'],
                'nisn' => $data['nisn'],
                'nama' => $data['nama'],
                'jenis_kelamin' => $data['jk'],
                'kelas_id' => $data['kelas_id'],
            ]);
        }
    }
}
