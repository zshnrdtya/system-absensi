<?php

namespace App\Http\Controllers;

use App\Models\Izin;
use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PresensiHarianController extends Controller
{
    /**
     * Tampilkan formulir entri presensi harian kelas.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $this->authorizeDailyAttendanceRole($user);

        $today = Carbon::today()->format('Y-m-d');
        $tanggal = $request->input('tanggal', $today);
        $accessibleClasses = $this->getAccessibleClasses($user, $tanggal);

        if ($accessibleClasses->isEmpty()) {
            return Inertia::render('Presensi/Harian', [
                'kelas' => null,
                'accessibleClasses' => [],
                'tanggal' => $tanggal,
                'siswaList' => [],
                'canEdit' => false,
                'isFutureDate' => false,
            ]);
        }

        $kelasId = $request->input('kelas_id', $accessibleClasses->first()->id);
        $kelas = $accessibleClasses->firstWhere('id', (int) $kelasId);

        // Blackbox Skenario 6: Wali kelas A membuka kelas wali B -> 403
        if (! $kelas) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuka presensi kelas ini.');
        }

        $isFutureDate = $tanggal > $today;
        $canEdit = $this->canEditDate($user, $tanggal, $today);

        // Ambil data siswa aktif kelas
        $siswaRaw = Siswa::where('kelas_id', $kelas->id)
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->orderBy('nama')
            ->get();

        // Ambil presensi yang sudah tercatat pada tanggal tersebut
        $existingPresensi = Presensi::where('kelas_id', $kelas->id)
            ->where('tanggal', $tanggal)
            ->get()
            ->keyBy('siswa_id');

        // Ambil izin yang telah disetujui pada tanggal tersebut (FR-ATT-03)
        $approvedIzin = Izin::whereIn('siswa_id', $siswaRaw->pluck('id'))
            ->where('status', 'approved')
            ->where('tgl_mulai', '<=', $tanggal)
            ->where('tgl_selesai', '>=', $tanggal)
            ->get()
            ->keyBy('siswa_id');

        $siswaList = $siswaRaw->map(function ($s) use ($existingPresensi, $approvedIzin) {
            $izin = $approvedIzin->get($s->id);
            $presensi = $existingPresensi->get($s->id);

            if ($izin) {
                return [
                    'siswa_id' => $s->id,
                    'nis' => $s->nis,
                    'nama' => $s->nama,
                    'status' => $izin->jenis,
                    'keterangan' => 'Disetujui dari pengajuan '.$izin->jenis,
                    'is_locked' => true,
                    'izin_id' => $izin->id,
                ];
            }

            return [
                'siswa_id' => $s->id,
                'nis' => $s->nis,
                'nama' => $s->nama,
                'status' => $presensi ? $presensi->status : 'hadir',
                'keterangan' => $presensi ? ($presensi->keterangan ?? '') : '',
                'is_locked' => false,
                'izin_id' => null,
            ];
        });

        return Inertia::render('Presensi/Harian', [
            'kelas' => $kelas,
            'accessibleClasses' => $accessibleClasses,
            'tanggal' => $tanggal,
            'siswaList' => $siswaList,
            'canEdit' => $canEdit,
            'isFutureDate' => $isFutureDate,
            'maxDate' => $today,
        ]);
    }

    /**
     * Simpan presensi harian massal satu kelas (FR-ATT-02, FR-ATT-05).
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeDailyAttendanceRole($user);

        $today = Carbon::today()->format('Y-m-d');

        $validated = $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'tanggal' => ['required', 'date', 'before_or_equal:'.$today],
            'presensi' => ['required', 'array'],
            'presensi.*.siswa_id' => ['required', 'exists:siswa,id'],
            'presensi.*.status' => ['required', Rule::in(['hadir', 'izin', 'sakit', 'alpa'])],
            'presensi.*.keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'tanggal.before_or_equal' => 'Tidak dapat menginput presensi untuk tanggal di masa depan.',
        ]);

        $kelasId = (int) $validated['kelas_id'];
        $tanggal = $validated['tanggal'];

        // Verifikasi hak akses kelas pada tanggal tersebut
        $accessibleClasses = $this->getAccessibleClasses($user, $tanggal);
        if (! $accessibleClasses->contains('id', $kelasId)) {
            abort(403, 'Anda tidak berwenang menginput presensi pada kelas ini.');
        }

        // Verifikasi batas toleransi koreksi (H+1)
        if (! $this->canEditDate($user, $tanggal, $today)) {
            return redirect()->back()->with('error', 'Batas toleransi koreksi presensi (H+1) untuk tanggal ini telah berakhir.');
        }

        // Ambil izin yang disetujui untuk proteksi penguncian
        $approvedIzin = Izin::where('status', 'approved')
            ->where('tgl_mulai', '<=', $tanggal)
            ->where('tgl_selesai', '>=', $tanggal)
            ->get()
            ->keyBy('siswa_id');

        DB::transaction(function () use ($validated, $kelasId, $tanggal, $user, $approvedIzin) {
            foreach ($validated['presensi'] as $item) {
                $siswaId = $item['siswa_id'];
                $izin = $approvedIzin->get($siswaId);

                // FR-ATT-03: Siswa dengan izin disetujui terkunci otomatis
                $status = $izin ? $izin->jenis : $item['status'];
                $keterangan = $izin ? 'Disetujui dari pengajuan '.$izin->jenis : ($item['keterangan'] ?? null);
                $izinId = $izin ? $izin->id : null;

                // Skenario 12: Input ganda di tanggal sama memperbarui data (tidak duplikat)
                Presensi::updateOrCreate(
                    [
                        'siswa_id' => $siswaId,
                        'tanggal' => $tanggal,
                    ],
                    [
                        'kelas_id' => $kelasId,
                        'status' => $status,
                        'keterangan' => $keterangan,
                        'izin_id' => $izinId,
                        'dicatat_oleh' => $user->id,
                    ]
                );
            }
        });

        return redirect()->back()->with('success', 'Data presensi harian kelas berhasil disimpan.');
    }

    /**
     * Pastikan role pengguna memiliki izin presensi harian (Scenario 23: Guru mapel ditolak).
     */
    protected function authorizeDailyAttendanceRole($user): void
    {
        if ($user->isGuruMapel() || $user->isSiswa()) {
            abort(403, 'Role akun Anda tidak memiliki hak akses untuk presensi harian.');
        }
    }

    /**
     * Ambil daftar kelas yang berhak diakses pengguna pada tanggal tersebut.
     */
    protected function getAccessibleClasses($user, string $tanggal)
    {
        if ($user->isAdmin()) {
            return Kelas::orderBy('nama_kelas')->get();
        }

        if ($user->isWaliKelas()) {
            return $user->kelasWali()->orderBy('nama_kelas')->get();
        }

        if ($user->isGuruPiket()) {
            $kelasIds = $user->delegasiPiket()
                ->where('tgl_mulai', '<=', $tanggal)
                ->where('tgl_selesai', '>=', $tanggal)
                ->pluck('kelas_id');

            return Kelas::whereIn('id', $kelasIds)->orderBy('nama_kelas')->get();
        }

        return collect();
    }

    /**
     * Cek apakah pengguna berhak mengedit data pada tanggal tersebut (Toleransi H+1).
     */
    protected function canEditDate($user, string $tanggal, string $today): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Tanggal masa depan tidak bisa diedit
        if ($tanggal > $today) {
            return false;
        }

        // Toleransi H+1: Boleh mengedit hari ini dan kemarin (H-1)
        $hMinus1 = Carbon::today()->subDay()->format('Y-m-d');

        return $tanggal >= $hMinus1;
    }
}
