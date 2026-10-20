<?php

namespace App\Http\Controllers;

use App\Models\Izin;
use App\Models\Kelas;
use App\Services\AttendanceSyncService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IzinApprovalController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $statusFilter = $request->input('status', 'pending');
        $kelasFilter = $request->input('kelas_id');
        $today = Carbon::today()->format('Y-m-d');

        $accessibleKelasIds = [];

        if ($user->isAdmin()) {
            $accessibleKelasIds = Kelas::pluck('id')->toArray();
        } elseif ($user->isWaliKelas()) {
            $accessibleKelasIds = $user->kelasWali()->pluck('id')->toArray();
        } elseif ($user->isGuruPiket()) {
            $accessibleKelasIds = $user->delegasiPiket()
                ->where('tgl_mulai', '<=', $today)
                ->where('tgl_selesai', '>=', $today)
                ->pluck('kelas_id')
                ->toArray();
        }

        $query = Izin::whereHas('siswa', function ($q) use ($accessibleKelasIds, $kelasFilter) {
            $q->whereIn('kelas_id', $accessibleKelasIds);
            if ($kelasFilter) {
                $q->where('kelas_id', $kelasFilter);
            }
        });

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $izinList = $query->with(['siswa.kelas', 'pemroses'])
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $kelasList = Kelas::whereIn('id', $accessibleKelasIds)->get();

        return Inertia::render('Izin/Approval', [
            'izinList' => $izinList,
            'kelasList' => $kelasList,
            'filters' => [
                'status' => $statusFilter,
                'kelas_id' => $kelasFilter,
            ],
        ]);
    }

    public function approve(Request $request, Izin $izin, AttendanceSyncService $syncService): RedirectResponse
    {
        $this->authorizeAction($request->user(), $izin);

        if ($izin->status !== 'pending' && ! $request->user()->isAdmin()) {
            return redirect()->back()->with('error', 'Pengajuan izin ini sudah diproses sebelumnya.');
        }

        $izin->update([
            'status' => 'approved',
            'diproses_oleh' => $request->user()->id,
            'diproses_pada' => Carbon::now(),
        ]);

        // FR-APR-04: Sinkronisasi presensi harian dan jam secara otomatis
        $syncService->syncApprovedIzin($izin, $request->user()->id);

        return redirect()->back()->with('success', 'Pengajuan izin berhasil disetujui dan data presensi otomatis diperbarui.');
    }

    public function reject(Request $request, Izin $izin): RedirectResponse
    {
        $this->authorizeAction($request->user(), $izin);

        // FR-APR-03 & Blackbox Skenario 11: Catatan wajib saat menolak
        $validated = $request->validate([
            'catatan' => ['required', 'string', 'min:3', 'max:500'],
        ], [
            'catatan.required' => 'Catatan alasan penolakan wajib diisi.',
            'catatan.min' => 'Catatan penolakan minimal 3 karakter.',
        ]);

        if ($izin->status !== 'pending' && ! $request->user()->isAdmin()) {
            return redirect()->back()->with('error', 'Pengajuan izin ini sudah diproses sebelumnya.');
        }

        $izin->update([
            'status' => 'rejected',
            'catatan' => $validated['catatan'],
            'diproses_oleh' => $request->user()->id,
            'diproses_pada' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Pengajuan izin telah ditolak.');
    }

    /**
     * Validasi hak akses wali kelas atau guru piket delegasi aktif.
     */
    protected function authorizeAction($user, Izin $izin): void
    {
        if ($user->isAdmin()) {
            return;
        }

        $kelasId = $izin->siswa->kelas_id;

        if ($user->isWaliKelas() && $user->kelasWali()->where('id', $kelasId)->exists()) {
            return;
        }

        if ($user->isGuruPiket()) {
            $today = Carbon::today()->format('Y-m-d');
            $hasActiveDelegation = $user->delegasiPiket()
                ->where('kelas_id', $kelasId)
                ->where('tgl_mulai', '<=', $today)
                ->where('tgl_selesai', '>=', $today)
                ->exists();

            if ($hasActiveDelegation) {
                return;
            }
        }

        abort(403, 'Anda tidak memiliki wewenang untuk memproses pengajuan siswa dari kelas ini.');
    }
}
