<?php

namespace Tests\Feature;

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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresensiWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $waliA;
    protected User $waliB;
    protected User $guruMapel1;
    protected User $guruMapel2;
    protected User $piket;
    protected User $siswaUser;
    protected Siswa $siswaA;
    protected Kelas $kelasA;
    protected Kelas $kelasB;
    protected TahunAjaran $ta;
    protected JadwalPelajaran $jadwalSenin;

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

        $this->waliA = User::factory()->create(['role' => 'wali_kelas', 'is_active' => true]);
        $this->waliB = User::factory()->create(['role' => 'wali_kelas', 'is_active' => true]);
        $this->guruMapel1 = User::factory()->create(['role' => 'guru_mapel', 'is_active' => true]);
        $this->guruMapel2 = User::factory()->create(['role' => 'guru_mapel', 'is_active' => true]);
        $this->piket = User::factory()->create(['role' => 'guru_piket', 'is_active' => true]);

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

        $this->siswaUser = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $this->siswaA = Siswa::create([
            'user_id' => $this->siswaUser->id,
            'nis' => '102030',
            'nama' => 'Siswa Kelas A',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelasA->id,
        ]);

        $mapel = MataPelajaran::create(['kode' => 'WEB', 'nama' => 'Pemrograman Web']);
        $this->jadwalSenin = JadwalPelajaran::create([
            'kelas_id' => $this->kelasA->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $this->guruMapel1->id,
            'hari' => 'senin',
            'jam_mulai' => '07:30',
            'jam_selesai' => '09:30',
        ]);
    }

    public function test_wali_kelas_a_cannot_access_class_b_attendance_blackbox_scenario_6(): void
    {
        // Wali Kelas A mencoba membuka presensi Kelas B
        $response = $this->actingAs($this->waliA)->get("/presensi/harian?kelas_id={$this->kelasB->id}");
        $response->assertStatus(403);
    }

    public function test_cannot_input_presensi_for_future_date(): void
    {
        $futureDate = Carbon::tomorrow()->format('Y-m-d');

        $response = $this->actingAs($this->waliA)->post('/presensi/harian', [
            'kelas_id' => $this->kelasA->id,
            'tanggal' => $futureDate,
            'presensi' => [
                ['siswa_id' => $this->siswaA->id, 'status' => 'hadir'],
            ],
        ]);

        $response->assertSessionHasErrors('tanggal');
    }

    public function test_duplicate_attendance_input_updates_record_blackbox_scenario_12(): void
    {
        $today = Carbon::today()->format('Y-m-d');

        // Input pertama: Hadir
        $this->actingAs($this->waliA)->post('/presensi/harian', [
            'kelas_id' => $this->kelasA->id,
            'tanggal' => $today,
            'presensi' => [
                ['siswa_id' => $this->siswaA->id, 'status' => 'hadir', 'keterangan' => 'Pertama'],
            ],
        ]);

        $this->assertDatabaseHas('presensi', [
            'siswa_id' => $this->siswaA->id,
            'tanggal' => $today,
            'status' => 'hadir',
        ]);
        $this->assertEquals(1, Presensi::where('siswa_id', $this->siswaA->id)->where('tanggal', $today)->count());

        // Input kedua di tanggal yang sama: Koreksi jadi Sakit
        $this->actingAs($this->waliA)->post('/presensi/harian', [
            'kelas_id' => $this->kelasA->id,
            'tanggal' => $today,
            'presensi' => [
                ['siswa_id' => $this->siswaA->id, 'status' => 'sakit', 'keterangan' => 'Koreksi'],
            ],
        ]);

        // Tetap hanya 1 baris di tabel, nilai status terbarui
        $this->assertEquals(1, Presensi::where('siswa_id', $this->siswaA->id)->where('tanggal', $today)->count());
        $this->assertDatabaseHas('presensi', [
            'siswa_id' => $this->siswaA->id,
            'tanggal' => $today,
            'status' => 'sakit',
            'keterangan' => 'Koreksi',
        ]);
    }

    public function test_reject_presensi_jam_if_date_day_does_not_match_schedule_blackbox_scenario_15(): void
    {
        // Temukan tanggal yang jatuh pada hari Selasa (jadwal di-setup hari Senin)
        $tuesday = Carbon::now()->next(Carbon::TUESDAY)->subWeek()->format('Y-m-d');

        $response = $this->actingAs($this->guruMapel1)->post('/presensi/jam', [
            'jadwal_id' => $this->jadwalSenin->id,
            'tanggal' => $tuesday,
            'presensi' => [
                ['siswa_id' => $this->siswaA->id, 'status' => 'hadir'],
            ],
        ]);

        $response->assertSessionHasErrors('tanggal');
    }

    public function test_guru_mapel_cannot_input_other_teachers_schedule_blackbox_scenario_22(): void
    {
        $today = Carbon::today()->format('Y-m-d');

        // Guru Mapel 2 mencoba akses/input jadwal milik Guru Mapel 1
        $response = $this->actingAs($this->guruMapel2)->get("/presensi/jam?jadwal_id={$this->jadwalSenin->id}&tanggal={$today}");
        $response->assertStatus(403);

        $responsePost = $this->actingAs($this->guruMapel2)->post('/presensi/jam', [
            'jadwal_id' => $this->jadwalSenin->id,
            'tanggal' => $today,
            'presensi' => [
                ['siswa_id' => $this->siswaA->id, 'status' => 'hadir'],
            ],
        ]);
        $responsePost->assertStatus(403);
    }

    public function test_guru_mapel_cannot_access_daily_attendance_blackbox_scenario_23(): void
    {
        $response = $this->actingAs($this->guruMapel1)->get('/presensi/harian');
        $response->assertStatus(403);
    }

    public function test_approved_leave_is_locked_in_daily_attendance(): void
    {
        $today = Carbon::today()->format('Y-m-d');

        // Buat izin sakit yang disetujui untuk hari ini
        $izin = Izin::create([
            'siswa_id' => $this->siswaA->id,
            'jenis' => 'sakit',
            'tgl_mulai' => $today,
            'tgl_selesai' => $today,
            'alasan' => 'Demam',
            'status' => 'approved',
            'diproses_oleh' => $this->waliA->id,
        ]);

        // Coba kirim status 'hadir' manual lewat formulir
        $this->actingAs($this->waliA)->post('/presensi/harian', [
            'kelas_id' => $this->kelasA->id,
            'tanggal' => $today,
            'presensi' => [
                ['siswa_id' => $this->siswaA->id, 'status' => 'hadir'],
            ],
        ]);

        // Status di database harus tetap 'sakit' (terkunci izin)
        $this->assertDatabaseHas('presensi', [
            'siswa_id' => $this->siswaA->id,
            'tanggal' => $today,
            'status' => 'sakit',
            'izin_id' => $izin->id,
        ]);
    }
}
