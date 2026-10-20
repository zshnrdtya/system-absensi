<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MataPelajaranController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $mapelList = MataPelajaran::query()
            ->when($search, function ($query, $search) {
                $query->where('kode', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%");
            })
            ->withCount('jadwalPelajaran')
            ->orderBy('kode')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/MataPelajaran/Index', [
            'mapelList' => $mapelList,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:20', 'unique:mata_pelajaran,kode'],
            'nama' => ['required', 'string', 'max:100'],
        ], [
            'kode.required' => 'Kode mata pelajaran wajib diisi.',
            'kode.unique' => 'Kode mata pelajaran sudah digunakan.',
            'nama.required' => 'Nama mata pelajaran wajib diisi.',
        ]);

        MataPelajaran::create([
            'kode' => strtoupper($validated['kode']),
            'nama' => $validated['nama'],
        ]);

        return redirect()->back()->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, MataPelajaran $mapel): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:20', Rule::unique('mata_pelajaran', 'kode')->ignore($mapel->id)],
            'nama' => ['required', 'string', 'max:100'],
        ]);

        $mapel->update([
            'kode' => strtoupper($validated['kode']),
            'nama' => $validated['nama'],
        ]);

        return redirect()->back()->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(MataPelajaran $mapel): RedirectResponse
    {
        // FR-MD-07: Proteksi data relasi jadwal
        if ($mapel->jadwalPelajaran()->exists()) {
            return redirect()->back()->with('error', 'Mata pelajaran tidak dapat dihapus karena masih digunakan pada jadwal pelajaran aktif.');
        }

        $mapel->delete();

        return redirect()->back()->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
