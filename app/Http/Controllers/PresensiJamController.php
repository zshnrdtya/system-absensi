<?php

namespace App\Http\Controllers;

use App\Models\Izin;
use App\Models\JadwalPelajaran;
use App\Models\Presensi;
use App\Models\PresensiJam;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PresensiJamController extends Controller
{
    protected array $dayMap = [
        1 => 'senin', 2 => 'selasa', 3 => 'rabu',
        4 => 'kamis', 5 => 'jumat', 6 => 'sabtu', 0 => 'minggu',
    ];

    public function index(Request $request): Response
    {
        $user = $request->user();
        $this->authorizeAccess($user);

        $today = Carbon::today()->format('Y-m-d');
        $tanggal = $request->input('tanggal', $today);

        // Validasi jika jadwal tertentu diminta langsung (Skenario 22)
        $requestedJadwalId = $request->input('jadwal_id');
        if ($requestedJadwalId) {
            $requestedJadwal = JadwalPelajaran::find($requestedJadwalId);
            if ($requestedJadwal && $user->isGuruMapel() && $requestedJadwal->guru_id !== $user->id) {
                abort(403, 'Anda tidak berwenang mengakses jadwal milik guru lain.');
            }
        }

        $accessibleJadwals = $this->getAccessibleJadwals($user, $tanggal);

        if ($accessibleJadwals->isEmpty()) {
            return Inertia::render('Presensi/Jam', [
                'jadwal' => null,
                'accessibleJadwals' => [],
                'tanggal' => $tanggal,
                'siswaList' => [],
                'canEdit' => false,
                'isFutureDate' => false,
                'dayMismatch' => false,
            ]);
        }

        $jadwalId = $request->input('jadwal_id', $accessibleJadwals->first()->id);
        $jadwal = $accessibleJadwals->firstWhere('id', (int) $jadwalId);

        // Skenario 22: Guru mapel membuka jadwal guru lain -> 403
        if (! $jadwal) {
            abort(403, 'Anda tidak berwenang mengakses presensi jam untuk jadwal ini.');
        }

        $isFutureDate = $tanggal > $today;
        $canEdit = $this->canEditDate($user, $tanggal, $today);

        // Cek kecocokan hari (Skenario 15)
        $dayOfWeek = Carbon::parse($tanggal)->dayOfWeek;
        $currentDayName = $this->dayMap[$dayOfWeek] ?? '';
        $dayMismatch = $jadwal->hari !== $currentDayName;

        // Ambil data siswa di kelas jadwal
        $siswaRaw = Siswa::where('kelas_id', $jadwal->kelas_id)
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->orderBy('nama')
            ->get();

        // Ambil presensi jam yang sudah tercatat
        $existingPresensiJam = PresensiJam::where('jadwal_id', $jadwal->id)
            ->where('tanggal', $tanggal)
            ->get()
            ->keyBy('siswa_id');

        // Ambil presensi harian pada tanggal tersebut untuk auto-lock izin/sakit (FR-ATT-09)
        $presensiHarian = Presensi::where('kelas_id', $jadwal->kelas_id)
            ->where('tanggal', $tanggal)
            ->get()
            ->keyBy('siswa_id');

        $approvedIzin = Izin::whereIn('siswa_id', $siswaRaw->pluck('id'))
            ->where('status', 'approved')
            ->where('tgl_mulai', '<=', $tanggal)
            ->where('tgl_selesai', '>=', $tanggal)
            ->get()
            ->keyBy('siswa_id');

        $siswaList = $siswaRaw->map(function ($s) use ($existingPresensiJam, $presensiHarian, $approvedIzin) {
            $harian = $presensiHarian->get($s->id);
            $izin = $approvedIzin->get($s->id);
            $jam = $existingPresensiJam->get($s->id);

            // Siswa berstatus izin/sakit di presensi harian atau punya izin disetujui otomatis terkunci
            $isIzinOrSakit = ($harian && in_array($harian->status, ['izin', 'sakit'])) || $izin;
            $lockedStatus = $izin ? $izin->jenis : ($harian ? $harian->status : null);

            if ($isIzinOrSakit) {
                return [
                    'siswa_id' => $s->id,
                    'nis' => $s->nis,
                    'nama' => $s->nama,
                    'status' => $lockedStatus,
                    'keterangan' => 'Terkunci otomatis (Presensi Harian: '.ucfirst($lockedStatus).')',
                    'is_locked' => true,
                ];
            }

            return [
                'siswa_id' => $s->id,
                'nis' => $s->nis,
                'nama' => $s->nama,
                'status' => $jam ? $jam->status : 'hadir',
                'keterangan' => $jam ? ($jam->keterangan ?? '') : '',
                'is_locked' => false,
            ];
        });

        return Inertia::render('Presensi/Jam', [
            'jadwal' => $jadwal,
            'accessibleJadwals' => $accessibleJadwals,
            'tanggal' => $tanggal,
            'siswaList' => $siswaList,
            'canEdit' => $canEdit && ! $dayMismatch,
            'isFutureDate' => $isFutureDate,
            'dayMismatch' => $dayMismatch,
            'maxDate' => $today,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeAccess($user);

        $today = Carbon::today()->format('Y-m-d');

        $validated = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwal_pelajaran,id'],
            'tanggal' => ['required', 'date', 'before_or_equal:'.$today],
            'presensi' => ['required', 'array'],
            'presensi.*.siswa_id' => ['required', 'exists:siswa,id'],
            'presensi.*.status' => ['required', Rule::in(['hadir', 'izin', 'sakit', 'alpa'])],
            'presensi.*.keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'tanggal.before_or_equal' => 'Tidak dapat menginput presensi jam untuk tanggal di masa depan.',
        ]);

        $jadwalId = (int) $validated['jadwal_id'];
        $tanggal = $validated['tanggal'];
        $jadwal = JadwalPelajaran::findOrFail($jadwalId);

        // Otorisasi kepemilikan jadwal
        if ($user->isGuruMapel() && $jadwal->guru_id !== $user->id) {
            abort(403, 'Anda tidak berwenang menginput jadwal milik guru lain.');
        }

        // Blackbox Skenario 15: Validasi hari jadwal
        $dayOfWeek = Carbon::parse($tanggal)->dayOfWeek;
        $dayName = $this->dayMap[$dayOfWeek] ?? '';
        if ($jadwal->hari !== $dayName) {
            throw ValidationException::withMessages([
                'tanggal' => "Jadwal ini hanya berlangsung pada hari ".ucfirst($jadwal->hari).". Anda memilih tanggal yang jatuh pada hari ".ucfirst($dayName).".",
            ]);
        }

        // Verifikasi batas toleransi koreksi (H+1)
        if (! $this->canEditDate($user, $tanggal, $today)) {
            return redirect()->back()->with('error', 'Batas toleransi koreksi presensi jam (H+1) telah berakhir.');
        }

        // Cek presensi harian / izin untuk auto-lock
        $presensiHarian = Presensi::where('kelas_id', $jadwal->kelas_id)
            ->where('tanggal', $tanggal)
            ->whereIn('status', ['izin', 'sakit'])
            ->get()
            ->keyBy('siswa_id');

        DB::transaction(function () use ($validated, $jadwalId, $tanggal, $user, $presensiHarian) {
            foreach ($validated['presensi'] as $item) {
                $siswaId = $item['siswa_id'];
                $harianLock = $presensiHarian->get($siswaId);

                $status = $harianLock ? $harianLock->status : $item['status'];
                $keterangan = $harianLock ? 'Terkunci otomatis (Presensi Harian: '.ucfirst($status).')' : ($item['keterangan'] ?? null);
                $izinId = $harianLock ? $harianLock->izin_id : null;

                PresensiJam::updateOrCreate(
                    [
                        'siswa_id' => $siswaId,
                        'jadwal_id' => $jadwalId,
                        'tanggal' => $tanggal,
                    ],
                    [
                        'status' => $status,
                        'keterangan' => $keterangan,
                        'izin_id' => $izinId,
                        'dicatat_oleh' => $user->id,
                    ]
                );
            }
        });

        return redirect()->back()->with('success', 'Presensi per jam pelajaran berhasil disimpan.');
    }

    protected function authorizeAccess($user): void
    {
        if ($user->isSiswa()) {
            abort(403, 'Akses tidak diizinkan untuk siswa.');
        }
    }

    protected function getAccessibleJadwals($user, string $tanggal)
    {
        $query = JadwalPelajaran::with(['kelas', 'mapel', 'guru']);

        if ($user->isAdmin()) {
            return $query->get();
        }

        if ($user->isGuruMapel()) {
            return $query->where('guru_id', $user->id)->get();
        }

        if ($user->isWaliKelas()) {
            $kelasIds = $user->kelasWali()->pluck('id');
            return $query->whereIn('kelas_id', $kelasIds)->get();
        }

        if ($user->isGuruPiket()) {
            $kelasIds = $user->delegasiPiket()
                ->where('tgl_mulai', '<=', $tanggal)
                ->where('tgl_selesai', '>=', $tanggal)
                ->pluck('kelas_id');

            return $query->whereIn('kelas_id', $kelasIds)->get();
        }

        return collect();
    }

    protected function canEditDate($user, string $tanggal, string $today): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($tanggal > $today) {
            return false;
        }

        $hMinus1 = Carbon::today()->subDay()->format('Y-m-d');
        return $tanggal >= $hMinus1;
    }
}
