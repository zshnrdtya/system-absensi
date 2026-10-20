<?php

namespace App\Http\Controllers;

use App\Models\Izin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PrivateFileController extends Controller
{
    /**
     * Tampilkan berkas bukti izin dengan otorisasi ketat (FR-LV-06).
     */
    public function viewBuktiIzin(Request $request, Izin $izin): Response
    {
        $user = $request->user();

        // 1. Otorisasi Akses
        $isAllowed = false;

        if ($user->isAdmin()) {
            $isAllowed = true;
        } elseif ($user->isSiswa() && $user->siswa && $user->siswa->id === $izin->siswa_id) {
            $isAllowed = true;
        } elseif ($user->isWaliKelas()) {
            $kelasWali = $user->kelasWali()->pluck('id')->toArray();
            if (in_array($izin->siswa->kelas_id, $kelasWali)) {
                $isAllowed = true;
            }
        } elseif ($user->isGuruPiket()) {
            $today = Carbon::today()->format('Y-m-d');
            $hasDelegation = $user->delegasiPiket()
                ->where('kelas_id', $izin->siswa->kelas_id)
                ->where('tgl_mulai', '<=', $today)
                ->where('tgl_selesai', '>=', $today)
                ->exists();

            if ($hasDelegation) {
                $isAllowed = true;
            }
        }

        if (! $isAllowed) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat berkas bukti ini.');
        }

        if (! $izin->bukti_path || ! Storage::disk('local')->exists($izin->bukti_path)) {
            abort(404, 'Berkas bukti tidak ditemukan.');
        }

        $filePath = Storage::disk('local')->path($izin->bukti_path);
        $mimeType = mime_content_type($filePath) ?: 'image/jpeg';

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.basename($filePath).'"',
        ]);
    }
}
