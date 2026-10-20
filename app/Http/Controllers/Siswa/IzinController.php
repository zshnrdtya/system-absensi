<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Izin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class IzinController extends Controller
{
    public function create(Request $request): Response
    {
        $siswa = $request->user()->siswa()->with('kelas')->firstOrFail();

        return Inertia::render('Siswa/Izin/Create', [
            'siswa' => $siswa,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $siswa = $request->user()->siswa()->firstOrFail();

        $rules = [
            'jenis' => ['required', Rule::in(['izin', 'sakit'])],
            'tgl_mulai' => ['required', 'date'],
            'tgl_selesai' => ['required', 'date', 'after_or_equal:tgl_mulai'],
            'alasan' => ['required', 'string', 'min:5', 'max:500'],
            'bukti' => [
                $request->input('jenis') === 'sakit' ? 'required' : 'nullable',
                'file',
                'mimes:jpg,jpeg,png',
                'max:2048', // Maksimal 2 MB
            ],
        ];

        $messages = [
            'jenis.required' => 'Jenis pengajuan (Izin/Sakit) wajib dipilih.',
            'tgl_mulai.required' => 'Tanggal mulai izin wajib diisi.',
            'tgl_selesai.required' => 'Tanggal selesai izin wajib diisi.',
            'tgl_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'alasan.required' => 'Alasan izin wajib dijelaskan secara rinci.',
            'alasan.min' => 'Alasan izin minimal 5 karakter.',
            'bukti.required' => 'Surat bukti dokter wajib dilampirkan untuk jenis sakit.',
            'bukti.mimes' => 'Format berkas bukti harus berupa gambar (JPG, JPEG, atau PNG). Berkas PDF tidak diizinkan.',
            'bukti.max' => 'Ukuran berkas bukti maksimal 2 MB.',
        ];

        $validated = $request->validate($rules, $messages);

        $tglMulai = $validated['tgl_mulai'];
        $tglSelesai = $validated['tgl_selesai'];

        // FR-LV-03 & Blackbox Skenario 9: Validasi rentang tanggal tidak boleh bertumpuk
        $overlapExists = Izin::where('siswa_id', $siswa->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($tglMulai, $tglSelesai) {
                $query->whereBetween('tgl_mulai', [$tglMulai, $tglSelesai])
                    ->orWhereBetween('tgl_selesai', [$tglMulai, $tglSelesai])
                    ->orWhere(function ($q) use ($tglMulai, $tglSelesai) {
                        $q->where('tgl_mulai', '<=', $tglMulai)
                            ->where('tgl_selesai', '>=', $tglSelesai);
                    });
            })
            ->exists();

        if ($overlapExists) {
            throw ValidationException::withMessages([
                'tgl_mulai' => 'Rentang tanggal bertumpuk dengan pengajuan izin Anda yang lain yang masih aktif.',
            ]);
        }

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('bukti_izin', 'local');
        }

        Izin::create([
            'siswa_id' => $siswa->id,
            'jenis' => $validated['jenis'],
            'tgl_mulai' => $tglMulai,
            'tgl_selesai' => $tglSelesai,
            'alasan' => $validated['alasan'],
            'bukti_path' => $buktiPath,
            'status' => 'pending',
        ]);

        return redirect()->route('siswa.izin.riwayat')->with('success', 'Pengajuan izin berhasil dikirimkan dan menunggu persetujuan.');
    }

    public function riwayat(Request $request): Response
    {
        $siswa = $request->user()->siswa()->firstOrFail();

        $izinList = Izin::where('siswa_id', $siswa->id)
            ->with('pemroses')
            ->latest('id')
            ->paginate(10);

        return Inertia::render('Siswa/Izin/Riwayat', [
            'izinList' => $izinList,
        ]);
    }

    public function destroy(Request $request, Izin $izin): RedirectResponse
    {
        $siswa = $request->user()->siswa()->firstOrFail();

        if ($izin->siswa_id !== $siswa->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        // FR-LV-04: Siswa hanya bisa membatalkan saat masih status pending
        if ($izin->status !== 'pending') {
            return redirect()->back()->with('error', 'Pengajuan izin yang sudah diproses tidak dapat dibatalkan.');
        }

        if ($izin->bukti_path && Storage::disk('local')->exists($izin->bukti_path)) {
            Storage::disk('local')->delete($izin->bukti_path);
        }

        $izin->delete();

        return redirect()->back()->with('success', 'Pengajuan izin berhasil dibatalkan.');
    }
}
