<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $passwordGuru = Hash::make('guru123');

        User::create([
            'name' => 'Administrator Kurikulum',
            'username' => 'admin',
            'email' => 'admin@1dex-sybsen.sch.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Budi Santoso, S.Kom',
            'username' => 'wali_pplg1',
            'email' => 'budi.santoso@1dex-sybsen.sch.id',
            'password' => $passwordGuru,
            'role' => 'wali_kelas',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Siti Rahmawati, S.Pd',
            'username' => 'wali_pplg2',
            'email' => 'siti.rahmawati@1dex-sybsen.sch.id',
            'password' => $passwordGuru,
            'role' => 'wali_kelas',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Hendra Pratama, M.Kom',
            'username' => 'guru_web',
            'email' => 'hendra.pratama@1dex-sybsen.sch.id',
            'password' => $passwordGuru,
            'role' => 'guru_mapel',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Dewi Kusuma, S.T',
            'username' => 'guru_db',
            'email' => 'dewi.kusuma@1dex-sybsen.sch.id',
            'password' => $passwordGuru,
            'role' => 'guru_mapel',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Rahmat Hidayat, M.Kom',
            'username' => 'guru_pbo',
            'email' => 'rahmat.hidayat@1dex-sybsen.sch.id',
            'password' => $passwordGuru,
            'role' => 'guru_mapel',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Agus Setiawan, S.Pd',
            'username' => 'guru_piket',
            'email' => 'agus.setiawan@1dex-sybsen.sch.id',
            'password' => $passwordGuru,
            'role' => 'guru_piket',
            'is_active' => true,
        ]);
    }
}
