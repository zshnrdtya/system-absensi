<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanGrafikTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $waliA;
    protected User $waliB;
    protected User $siswaUser1;
    protected User $siswaUser2;
    protected Siswa $siswa1;
    protected Siswa $siswa2;
    protected Kelas $kelasA;
    protected Kelas $kelasB;
    protected TahunAjaran $ta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ta = TahunAjaran::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tgl_mulai' => '2026-07-01',
            'tgl_selesai' => '2026-12-31',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->waliA = User::factory()->create(['role' => 'wali_kelas', 'is_active' => true]);
        $this->waliB = User::factory()->create(['role' => 'wali_kelas', 'is_active' => true]);

        $this->kelasA = Kelas::create([
            'nama_kelas' => 'XI PPLG 1',
            'tingkat' => 'XI',
            'jurusan' => 'PPLG',
            'tahun_ajaran_id' => $this->ta->id,
            'wali_kelas_id' => $this->waliA->id,
        ]);

        $this->kelasB = Kelas::create([
            'nama_kelas' => 'XI PPLG 2',
            'tingkat' => 'XI',
            'jurusan' => 'PPLG',
            'tahun_ajaran_id' => $this->ta->id,
            'wali_kelas_id' => $this->waliB->id,
        ]);

        $this->siswaUser1 = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $this->siswa1 = Siswa::create([
            'user_id' => $this->siswaUser1->id,
            'nis' => '102031',
            'nama' => 'Ahmad Siswa 1',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelasA->id,
        ]);

        $this->siswaUser2 = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $this->siswa2 = Siswa::create([
            'user_id' => $this->siswaUser2->id,
            'nis' => '102032',
            'nama' => 'Bintang Siswa 2',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelasB->id,
        ]);

        // Rekam presensi 10 hari untuk Siswa 1:
        // 8 Hadir, 1 Izin, 1 Alpa -> Persentase = (8 + 1) / 10 * 100% = 90.0%
        for ($i = 1; $i <= 8; $i++) {
            Presensi::create([
                'siswa_id' => $this->siswa1->id,
                'kelas_id' => $this->kelasA->id,
                'tanggal' => sprintf('2026-09-%02d', $i),
                'status' => 'hadir',
                'dicatat_oleh' => $this->waliA->id,
            ]);
        }
        Presensi::create([
            'siswa_id' => $this->siswa1->id,
            'kelas_id' => $this->kelasA->id,
            'tanggal' => '2026-09-09',
            'status' => 'izin',
            'dicatat_oleh' => $this->waliA->id,
        ]);
        Presensi::create([
            'siswa_id' => $this->siswa1->id,
            'kelas_id' => $this->kelasA->id,
            'tanggal' => '2026-09-10',
            'status' => 'alpa',
            'dicatat_oleh' => $this->waliA->id,
        ]);
    }

    public function test_rekap_presensi_calculation_and_filter_blackbox_scenario_13(): void
    {
        $response = $this->actingAs($this->waliA)->get('/laporan/rekap?kelas_id='.$this->kelasA->id.'&dari=2026-09-01&sampai=2026-09-15');
        $response->assertStatus(200);

        // Ambil props rekapList dari Inertia
        $page = $response->viewData('page');
        $rekapList = $page['props']['rekapList'];

        $this->assertNotEmpty($rekapList);
        $rowSiswa1 = collect($rekapList)->firstWhere('siswa_id', $this->siswa1->id);

        $this->assertEquals(8, $rowSiswa1['hadir']);
        $this->assertEquals(1, $rowSiswa1['izin']);
        $this->assertEquals(0, $rowSiswa1['sakit']);
        $this->assertEquals(1, $rowSiswa1['alpa']);
        $this->assertEquals(10, $rowSiswa1['total_hari']);
        // Formula kehadiran: (8 Hadir + 1 Izin) / 10 * 100 = 90.0%
        $this->assertEquals(90.0, $rowSiswa1['persentase']);
    }

    public function test_export_pdf_rekap_presensi(): void
    {
        $response = $this->actingAs($this->waliA)->get('/laporan/rekap/pdf?kelas_id='.$this->kelasA->id.'&dari=2026-09-01&sampai=2026-09-15');
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_grafik_siswa_data_calculation_blackbox_scenario_18(): void
    {
        $response = $this->actingAs($this->admin)->get("/siswa/{$this->siswa1->id}/grafik?dari=2026-09-01&sampai=2026-09-30");
        $response->assertStatus(200);

        $page = $response->viewData('page');
        $komposisi = $page['props']['komposisi'];
        $bulanan = $page['props']['bulanan'];

        // Cek data komposisi donut
        $this->assertEquals(8, $komposisi['hadir']);
        $this->assertEquals(1, $komposisi['izin']);
        $this->assertEquals(0, $komposisi['sakit']);
        $this->assertEquals(1, $komposisi['alpa']);

        // Cek data bulanan
        $this->assertNotEmpty($bulanan);
        $sepRow = collect($bulanan)->firstWhere('periode', '2026-09');
        $this->assertEquals(8, $sepRow['hadir']);
        $this->assertEquals(90.0, $sepRow['persentase']);
    }

    public function test_siswa_cannot_access_other_students_chart_blackbox_scenario_19(): void
    {
        // Siswa 1 mencoba membuka grafik Siswa 2 di URL
        $response = $this->actingAs($this->siswaUser1)->get("/siswa/{$this->siswa2->id}/grafik");
        $response->assertStatus(403);

        // Siswa 1 membuka grafiknya sendiri -> Berhasil (200)
        $responseSelf = $this->actingAs($this->siswaUser1)->get('/siswa/grafik');
        $responseSelf->assertStatus(200);
    }
}
