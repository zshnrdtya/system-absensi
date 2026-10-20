<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PiketDelegasi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PiketDelegasiController extends Controller
{
    public function index(Request $request): Response
    {
        $delegasiList = PiketDelegasi::query()
            ->with(['guru', 'kelas', 'pembuat'])
            ->latest('tgl_mulai')
            ->paginate(10);

        $guruPiketList = User::where('role', 'guru_piket')->where('is_active', true)->get();
        $kelasList = Kelas::orderBy('nama_kelas')->get();

        return Inertia::render('Admin/PiketDelegasi/Index', [
            'delegasiList' => $delegasiList,
            'guruPiketList' => $guruPiketList,
            'kelasList' => $kelasList,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'guru_id' => ['required', 'exists:users,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'tgl_mulai' => ['required', 'date'],
            'tgl_selesai' => ['required', 'date', 'after_or_equal:tgl_mulai'],
        ], [
            'guru_id.required' => 'Guru piket wajib dipilih.',
            'kelas_id.required' => 'Kelas delegasi wajib dipilih.',
            'tgl_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tgl_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tgl_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ]);

        PiketDelegasi::create([
            ...$validated,
            'dibuat_oleh' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Delegasi guru piket berhasil ditetapkan.');
    }

    public function update(Request $request, PiketDelegasi $piketDelegasi): RedirectResponse
    {
        $validated = $request->validate([
            'guru_id' => ['required', 'exists:users,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'tgl_mulai' => ['required', 'date'],
            'tgl_selesai' => ['required', 'date', 'after_or_equal:tgl_mulai'],
        ]);

        $piketDelegasi->update($validated);

        return redirect()->back()->with('success', 'Data delegasi guru piket berhasil diperbarui.');
    }

    public function destroy(PiketDelegasi $piketDelegasi): RedirectResponse
    {
        $piketDelegasi->delete();

        return redirect()->back()->with('success', 'Delegasi guru piket berhasil dihapus.');
    }
}
