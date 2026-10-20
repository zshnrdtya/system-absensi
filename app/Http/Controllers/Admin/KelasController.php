<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KelasController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $kelasList = Kelas::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_kelas', 'like', "%{$search}%")
                        ->orWhere('tingkat', 'like', "%{$search}%")
                        ->orWhere('jurusan', 'like', "%{$search}%");
                });
            })
            ->with(['tahunAjaran', 'waliKelas'])
            ->withCount('siswa')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $tahunAjaranList = TahunAjaran::orderBy('is_active', 'desc')->get();
        $waliKelasList = User::where('role', 'wali_kelas')->where('is_active', true)->get();

        return Inertia::render('Admin/Kelas/Index', [
            'kelasList' => $kelasList,
            'tahunAjaranList' => $tahunAjaranList,
            'waliKelasList' => $waliKelasList,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:50'],
            'tingkat' => ['required', 'string', 'max:10'],
            'jurusan' => ['required', 'string', 'max:50'],
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
            'wali_kelas_id' => ['required', 'exists:users,id'],
        ], [
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
            'tingkat.required' => 'Tingkat kelas wajib diisi.',
            'jurusan.required' => 'Jurusan wajib diisi.',
            'tahun_ajaran_id.required' => 'Tahun ajaran wajib dipilih.',
            'wali_kelas_id.required' => 'Wali kelas wajib dipilih.',
        ]);

        Kelas::create($validated);

        return redirect()->back()->with('success', 'Kelas baru berhasil ditambahkan.');
    }

    public function update(Request $request, Kelas $kela): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:50'],
            'tingkat' => ['required', 'string', 'max:10'],
            'jurusan' => ['required', 'string', 'max:50'],
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
            'wali_kelas_id' => ['required', 'exists:users,id'],
        ]);

        $kela->update($validated);

        return redirect()->back()->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kela): RedirectResponse
    {
        // FR-MD-07 & Blackbox Scenario 14: Proteksi relasi siswa
        if ($kela->siswa()->exists()) {
            return redirect()->back()->with('error', 'Kelas tidak dapat dihapus karena masih memiliki data siswa terdaftar.');
        }

        if ($kela->jadwalPelajaran()->exists()) {
            return redirect()->back()->with('error', 'Kelas tidak dapat dihapus karena masih terikat dengan jadwal pelajaran.');
        }

        if ($kela->delegasiPiket()->exists()) {
            return redirect()->back()->with('error', 'Kelas tidak dapat dihapus karena memiliki riwayat delegasi piket.');
        }

        $kela->delete();

        return redirect()->back()->with('success', 'Kelas berhasil dihapus.');
    }
}
