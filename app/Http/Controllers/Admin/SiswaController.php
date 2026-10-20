<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SiswaController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $kelasId = $request->input('kelas_id');

        $siswaList = Siswa::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                });
            })
            ->when($kelasId, function ($query, $kelasId) {
                $query->where('kelas_id', $kelasId);
            })
            ->with(['kelas', 'user'])
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $kelasList = Kelas::orderBy('nama_kelas')->get();

        return Inertia::render('Admin/Siswa/Index', [
            'siswaList' => $siswaList,
            'kelasList' => $kelasList,
            'filters' => [
                'search' => $search,
                'kelas_id' => $kelasId,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'max:20', 'unique:siswa,nis'],
            'nisn' => ['nullable', 'string', 'max:20', 'unique:siswa,nisn'],
            'nama' => ['required', 'string', 'max:100'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'username' => ['nullable', 'string', 'max:50', 'unique:users,username'],
            'password' => ['nullable', 'string', 'min:6'],
        ], [
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah digunakan oleh siswa lain.',
            'nama.required' => 'Nama siswa wajib diisi.',
            'kelas_id.required' => 'Kelas wajib dipilih.',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $username = $validated['username'] ?? 'siswa_'.$validated['nis'];
            $password = $validated['password'] ?? 'siswa123';

            // Buat akun user siswa
            $user = User::create([
                'name' => $validated['nama'],
                'username' => strtolower($username),
                'email' => $username.'@siswa.1dex-sybsen.sch.id',
                'password' => Hash::make($password),
                'role' => 'siswa',
                'is_active' => true,
            ]);

            Siswa::create([
                'user_id' => $user->id,
                'nis' => $validated['nis'],
                'nisn' => $validated['nisn'] ?? null,
                'nama' => $validated['nama'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'kelas_id' => $validated['kelas_id'],
            ]);
        });

        return redirect()->back()->with('success', 'Data siswa dan akun login berhasil dibuat.');
    }

    public function update(Request $request, Siswa $siswa): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'max:20', Rule::unique('siswa', 'nis')->ignore($siswa->id)],
            'nisn' => ['nullable', 'string', 'max:20', Rule::unique('siswa', 'nisn')->ignore($siswa->id)],
            'nama' => ['required', 'string', 'max:100'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'kelas_id' => ['required', 'exists:kelas,id'],
        ]);

        DB::transaction(function () use ($validated, $siswa) {
            $siswa->update($validated);

            if ($siswa->user) {
                $siswa->user->update([
                    'name' => $validated['nama'],
                ]);
            }
        });

        return redirect()->back()->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa): RedirectResponse
    {
        // FR-MD-07: Proteksi riwayat presensi dan izin
        if ($siswa->presensi()->exists()) {
            return redirect()->back()->with('error', 'Siswa tidak dapat dihapus karena memiliki riwayat presensi.');
        }

        if ($siswa->izin()->exists()) {
            return redirect()->back()->with('error', 'Siswa tidak dapat dihapus karena memiliki riwayat pengajuan izin.');
        }

        DB::transaction(function () use ($siswa) {
            $user = $siswa->user;
            $siswa->delete();
            if ($user) {
                $user->delete();
            }
        });

        return redirect()->back()->with('success', 'Data siswa berhasil dihapus.');
    }
}
