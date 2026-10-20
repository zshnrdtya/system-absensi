<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TahunAjaranController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $tahunAjaran = TahunAjaran::query()
            ->when($search, function ($query, $search) {
                $query->where('nama', 'like', "%{$search}%");
            })
            ->withCount('kelas')
            ->orderBy('is_active', 'desc')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/TahunAjaran/Index', [
            'tahunAjaran' => $tahunAjaran,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:50'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'tgl_mulai' => ['required', 'date'],
            'tgl_selesai' => ['required', 'date', 'after_or_equal:tgl_mulai'],
            'is_active' => ['boolean'],
        ], [
            'nama.required' => 'Nama tahun ajaran wajib diisi.',
            'semester.required' => 'Semester wajib dipilih.',
            'tgl_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tgl_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tgl_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ]);

        $isActive = $request->boolean('is_active', false);

        // FR-MD-02: Hanya satu tahun ajaran yang boleh aktif
        if ($isActive) {
            TahunAjaran::where('is_active', true)->update(['is_active' => false]);
        }

        TahunAjaran::create([
            ...$validated,
            'is_active' => $isActive,
        ]);

        return redirect()->back()->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function update(Request $request, TahunAjaran $tahunAjaran): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:50'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'tgl_mulai' => ['required', 'date'],
            'tgl_selesai' => ['required', 'date', 'after_or_equal:tgl_mulai'],
            'is_active' => ['boolean'],
        ]);

        $isActive = $request->boolean('is_active', false);

        if ($isActive && ! $tahunAjaran->is_active) {
            TahunAjaran::where('id', '!=', $tahunAjaran->id)->update(['is_active' => false]);
        }

        $tahunAjaran->update([
            ...$validated,
            'is_active' => $isActive,
        ]);

        return redirect()->back()->with('success', 'Data tahun ajaran berhasil diperbarui.');
    }

    public function destroy(TahunAjaran $tahunAjaran): RedirectResponse
    {
        // FR-MD-07: Proteksi data yang berelasi
        if ($tahunAjaran->kelas()->exists()) {
            return redirect()->back()->with('error', 'Tahun ajaran tidak dapat dihapus karena masih digunakan oleh data kelas.');
        }

        $tahunAjaran->delete();

        return redirect()->back()->with('success', 'Tahun ajaran berhasil dihapus.');
    }
}
