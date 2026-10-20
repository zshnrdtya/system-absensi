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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IzinWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $wali;
    protected User $piket;
    protected User $guruMapel;
    protected User $siswaUser;
    protected Siswa $siswa;
    protected Kelas $kelas;
    protected TahunAjaran $ta;
    protected JadwalPelajaran $jadwal;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->ta = TahunAjaran::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tgl_mulai' => '2026-07-01',
            'tgl_selesai' => '2026-12-31',
            'is_active' => true,
        ]);

        $this->wali = User::factory()->create(['role' => 'wali_kelas', 'is_active' => true]);
        $this->piket = User::factory()->create(['role' => 'guru_piket', 'is_active' => true]);
        $this->guruMapel = User::factory()->create(['role' => 'guru_mapel', 'is_active' => true]);

        $this->kelas = Kelas::create([
            'nama_kelas' => 'XI PPLG 1',
            'tingkat' => 'XI',
            'jurusan' => 'PPLG',
            'tahun_ajaran_id' => $this->ta->id,
            'wali_kelas_id' => $this->wali->id,
        ]);

        $this->siswaUser = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $this->siswa = Siswa::create([
            'user_id' => $this->siswaUser->id,
            'nis' => '102030',
            'nama' => 'Siswa Alif',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas->id,
        ]);

        $mapel = MataPelajaran::create(['kode' => 'WEB', 'nama' => 'Pemrograman Web']);
        $this->jadwal = JadwalPelajaran::create([
            'kelas_id' => $this->kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $this->guruMapel->id,
            'hari' => 'senin',
            'jam_mulai' => '07:30',
            'jam_selesai' => '09:30',
        ]);
    }

    public function test_reject_upload_pdf_or_large_file_blackbox_scenario_7(): void
    {
        // 1. PDF file rejection
        $pdfFile = UploadedFile::fake()->create('surat.pdf', 500, 'application/pdf');
        $response = $this->actingAs($this->siswaUser)->post('/izin/ajukan', [
            'jenis' => 'sakit',
            'tgl_mulai' => '2026-10-26',
            'tgl_selesai' => '2026-10-26',
            'alasan' => 'Sakit demam',
            'bukti' => $pdfFile,
        ]);
        $response->assertSessionHasErrors('bukti');

        // 2. File size > 2MB rejection
        $largeImage = UploadedFile::fake()->create('besar.jpg', 2500, 'image/jpeg');
        $response = $this->actingAs($this->siswaUser)->post('/izin/ajukan', [
            'jenis' => 'sakit',
            'tgl_mulai' => '2026-10-26',
            'tgl_selesai' => '2026-10-26',
            'alasan' => 'Sakit demam',
            'bukti' => $largeImage,
        ]);
        $response->assertSessionHasErrors('bukti');
    }

    public function test_reject_if_tgl_selesai_before_tgl_mulai_blackbox_scenario_8(): void
    {
        $response = $this->actingAs($this->siswaUser)->post('/izin/ajukan', [
            'jenis' => 'izin',
            'tgl_mulai' => '2026-10-28',
            'tgl_selesai' => '2026-10-26',
            'alasan' => 'Acara keluarga',
        ]);

        $response->assertSessionHasErrors('tgl_selesai');
    }

    public function test_reject_overlapping_izin_dates_blackbox_scenario_9(): void
    {
        // Buat izin pertama tanggal 26-28 Oktober
        Izin::create([
            'siswa_id' => $this->siswa->id,
            'jenis' => 'izin',
            'tgl_mulai' => '2026-10-26',
            'tgl_selesai' => '2026-10-28',
            'alasan' => 'Izin pertama',
            'status' => 'pending',
        ]);

        // Coba ajukan izin bertumpuk tanggal 27-29 Oktober
        $response = $this->actingAs($this->siswaUser)->post('/izin/ajukan', [
            'jenis' => 'izin',
            'tgl_mulai' => '2026-10-27',
            'tgl_selesai' => '2026-10-29',
            'alasan' => 'Izin kedua yang bertumpuk',
        ]);

        $response->assertSessionHasErrors('tgl_mulai');
    }

    public function test_approve_3_days_izin_creates_attendance_blackbox_scenario_10_and_16(): void
    {
        // Buat izin 3 hari kerja: Senin 2026-10-26 s/d Rabu 2026-10-28
        $izin = Izin::create([
            'siswa_id' => $this->siswa->id,
            'jenis' => 'sakit',
            'tgl_mulai' => '2026-10-26',
            'tgl_selesai' => '2026-10-28',
            'alasan' => 'Sakit tifus',
            'status' => 'pending',
        ]);

        // Wali kelas menyetujui
        $response = $this->actingAs($this->wali)->post("/izin/approval/{$izin->id}/approve");
        $response->assertSessionHas('success');

        // Status izin terbarui
        $this->assertEquals('approved', $izin->fresh()->status);
        $this->assertEquals($this->wali->id, $izin->fresh()->diproses_oleh);

        // Presensi harian 3 hari otomatis terbuat berstatus 'sakit'
        $this->assertDatabaseHas('presensi', [
            'siswa_id' => $this->siswa->id,
            'tanggal' => '2026-10-26',
            'status' => 'sakit',
            'izin_id' => $izin->id,
        ]);
        $this->assertDatabaseHas('presensi', [
            'siswa_id' => $this->siswa->id,
            'tanggal' => '2026-10-27',
            'status' => 'sakit',
            'izin_id' => $izin->id,
        ]);
        $this->assertDatabaseHas('presensi', [
            'siswa_id' => $this->siswa->id,
            'tanggal' => '2026-10-28',
            'status' => 'sakit',
            'izin_id' => $izin->id,
        ]);

        // Presensi jam pada hari Senin (2026-10-26) otomatis terisi status 'sakit' (Skenario 16)
        $this->assertDatabaseHas('presensi_jam', [
            'siswa_id' => $this->siswa->id,
            'jadwal_id' => $this->jadwal->id,
            'tanggal' => '2026-10-26',
            'status' => 'sakit',
            'izin_id' => $izin->id,
        ]);
    }

    public function test_reject_without_catatan_fails_blackbox_scenario_11(): void
    {
        $izin = Izin::create([
            'siswa_id' => $this->siswa->id,
            'jenis' => 'izin',
            'tgl_mulai' => '2026-10-26',
            'tgl_selesai' => '2026-10-26',
            'alasan' => 'Alasan izin',
            'status' => 'pending',
        ]);

        // Tolak tanpa mengisi catatan
        $response = $this->actingAs($this->wali)->post("/izin/approval/{$izin->id}/reject", [
            'catatan' => '',
        ]);

        $response->assertSessionHasErrors('catatan');
        $this->assertEquals('pending', $izin->fresh()->status);

        // Tolak dengan mengisi catatan
        $responseSuccess = $this->actingAs($this->wali)->post("/izin/approval/{$izin->id}/reject", [
            'catatan' => 'Surat bukti dari orang tua belum ada.',
        ]);

        $responseSuccess->assertSessionHas('success');
        $this->assertEquals('rejected', $izin->fresh()->status);
        $this->assertEquals('Surat bukti dari orang tua belum ada.', $izin->fresh()->catatan);
    }

    public function test_guru_piket_can_approve_delegated_class_blackbox_scenario_17(): void
    {
        // Delegasikan kelas ke guru piket untuk periode aktif
        PiketDelegasi::create([
            'guru_id' => $this->piket->id,
            'kelas_id' => $this->kelas->id,
            'tgl_mulai' => '2026-10-01',
            'tgl_selesai' => '2026-10-31',
            'dibuat_oleh' => $this->wali->id,
        ]);

        $izin = Izin::create([
            'siswa_id' => $this->siswa->id,
            'jenis' => 'izin',
            'tgl_mulai' => '2026-10-26',
            'tgl_selesai' => '2026-10-26',
            'alasan' => 'Keperluan keluarga',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->piket)->post("/izin/approval/{$izin->id}/approve");
        $response->assertSessionHas('success');
        $this->assertEquals('approved', $izin->fresh()->status);
        $this->assertEquals($this->piket->id, $izin->fresh()->diproses_oleh);
    }

    public function test_guru_piket_cannot_approve_non_delegated_class_blackbox_scenario_20(): void
    {
        // Guru piket tidak didelegasikan ke kelas ini
        $izin = Izin::create([
            'siswa_id' => $this->siswa->id,
            'jenis' => 'izin',
            'tgl_mulai' => '2026-10-26',
            'tgl_selesai' => '2026-10-26',
            'alasan' => 'Keperluan keluarga',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->piket)->post("/izin/approval/{$izin->id}/approve");
        $response->assertStatus(403);
    }

    public function test_guru_piket_cannot_approve_after_delegation_expired_blackbox_scenario_21(): void
    {
        // Delegasi sudah kedaluwarsa bulan lalu
        PiketDelegasi::create([
            'guru_id' => $this->piket->id,
            'kelas_id' => $this->kelas->id,
            'tgl_mulai' => '2026-08-01',
            'tgl_selesai' => '2026-08-31',
            'dibuat_oleh' => $this->wali->id,
        ]);

        $izin = Izin::create([
            'siswa_id' => $this->siswa->id,
            'jenis' => 'izin',
            'tgl_mulai' => '2026-10-26',
            'tgl_selesai' => '2026-10-26',
            'alasan' => 'Keperluan keluarga',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->piket)->post("/izin/approval/{$izin->id}/approve");
        $response->assertStatus(403);
    }
}
