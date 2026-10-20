<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $wali;
    protected User $siswaUser;
    protected TahunAjaran $ta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->wali = User::factory()->create(['role' => 'wali_kelas', 'is_active' => true]);
        $this->siswaUser = User::factory()->create(['role' => 'siswa', 'is_active' => true]);

        $this->ta = TahunAjaran::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tgl_mulai' => '2026-07-01',
            'tgl_selesai' => '2026-12-31',
            'is_active' => true,
        ]);
    }

    public function test_non_admin_cannot_access_admin_routes_blackbox_scenario_5(): void
    {
        // Siswa mencoba akses data user admin
        $response = $this->actingAs($this->siswaUser)->get('/admin/users');
        $response->assertStatus(403);

        // Wali kelas mencoba akses data user admin
        $response = $this->actingAs($this->wali)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_master_data_pages(): void
    {
        $this->actingAs($this->admin)->get('/admin/users')->assertStatus(200);
        $this->actingAs($this->admin)->get('/admin/tahun-ajaran')->assertStatus(200);
        $this->actingAs($this->admin)->get('/admin/kelas')->assertStatus(200);
        $this->actingAs($this->admin)->get('/admin/siswa')->assertStatus(200);
        $this->actingAs($this->admin)->get('/admin/mapel')->assertStatus(200);
        $this->actingAs($this->admin)->get('/admin/jadwal')->assertStatus(200);
        $this->actingAs($this->admin)->get('/admin/piket-delegasi')->assertStatus(200);
    }

    public function test_admin_can_create_user(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/users', [
            'name' => 'Guru Baru',
            'username' => 'guru_baru',
            'email' => 'guru_baru@test.com',
            'role' => 'guru_mapel',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['username' => 'guru_baru']);
    }

    public function test_only_one_tahun_ajaran_can_be_active(): void
    {
        // Ta pertama sudah aktif ($this->ta)
        $this->assertTrue($this->ta->fresh()->is_active);

        // Buat ta kedua dengan status aktif
        $this->actingAs($this->admin)->post('/admin/tahun-ajaran', [
            'nama' => '2026/2027',
            'semester' => 'genap',
            'tgl_mulai' => '2027-01-01',
            'tgl_selesai' => '2027-06-30',
            'is_active' => true,
        ]);

        // Ta pertama harus otomatis menjadi tidak aktif
        $this->assertFalse($this->ta->fresh()->is_active);
    }

    public function test_cannot_delete_kelas_with_students_blackbox_scenario_14(): void
    {
        $kelas = Kelas::create([
            'nama_kelas' => 'XI PPLG 1',
            'tingkat' => 'XI',
            'jurusan' => 'PPLG',
            'tahun_ajaran_id' => $this->ta->id,
            'wali_kelas_id' => $this->wali->id,
        ]);

        // Daftarkan siswa ke kelas tersebut
        Siswa::create([
            'user_id' => $this->siswaUser->id,
            'nis' => '998877',
            'nama' => 'Siswa Test',
            'jenis_kelamin' => 'L',
            'kelas_id' => $kelas->id,
        ]);

        // Coba hapus kelas
        $response = $this->actingAs($this->admin)->delete("/admin/kelas/{$kelas->id}");

        // Harus ditolak dengan pesan error
        $response->assertSessionHas('error', 'Kelas tidak dapat dihapus karena masih memiliki data siswa terdaftar.');
        $this->assertDatabaseHas('kelas', ['id' => $kelas->id]);
    }

    public function test_admin_can_create_siswa_with_automatic_user_account(): void
    {
        $kelas = Kelas::create([
            'nama_kelas' => 'XI PPLG 2',
            'tingkat' => 'XI',
            'jurusan' => 'PPLG',
            'tahun_ajaran_id' => $this->ta->id,
            'wali_kelas_id' => $this->wali->id,
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/siswa', [
            'nis' => '112233',
            'nisn' => '0099887766',
            'nama' => 'Budi Permana',
            'jenis_kelamin' => 'L',
            'kelas_id' => $kelas->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('siswa', ['nis' => '112233']);
        $this->assertDatabaseHas('users', ['username' => 'siswa_112233', 'role' => 'siswa']);
    }
}
