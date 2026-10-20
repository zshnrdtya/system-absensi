<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $roleFilter = $request->input('role');

        $users = User::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($roleFilter, function ($query, $roleFilter) {
                $query->where('role', $roleFilter);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'role' => $roleFilter,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'wali_kelas', 'guru_mapel', 'guru_piket', 'siswa'])],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['boolean'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan oleh akun lain.',
            'email.unique' => 'Alamat email sudah terdaftar.',
            'role.required' => 'Peran akun wajib dipilih.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => $validated['email'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make($validated['password'] ?? 'password123'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->back()->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'wali_kelas', 'guru_mapel', 'guru_piket', 'siswa'])],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['boolean'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => $validated['email'] ?? null,
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active', true),
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->back()->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Proteksi jika akun pengguna adalah akun diri sendiri
        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // FR-MD-07: Proteksi integritas relasi
        if ($user->siswa()->exists()) {
            return redirect()->back()->with('error', 'Pengguna tidak dapat dihapus karena masih terhubung ke data siswa.');
        }

        if ($user->kelasWali()->exists()) {
            return redirect()->back()->with('error', 'Pengguna tidak dapat dihapus karena masih terdaftar sebagai wali kelas aktif.');
        }

        if ($user->jadwalMengajar()->exists()) {
            return redirect()->back()->with('error', 'Pengguna tidak dapat dihapus karena masih terdaftar pada jadwal mengajar.');
        }

        if ($user->delegasiPiket()->exists()) {
            return redirect()->back()->with('error', 'Pengguna tidak dapat dihapus karena memiliki riwayat delegasi guru piket.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'Pengguna berhasil dihapus.');
    }
}
