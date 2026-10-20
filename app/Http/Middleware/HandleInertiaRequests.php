<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'appName' => config('app.name', '1dex-sybsen'),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_active' => $user->is_active,
                ] : null,
                'siswa' => ($user && $user->role === 'siswa')
                    ? $user->siswa()->with('kelas')->first()
                    : null,
                'kelasWali' => ($user && $user->role === 'wali_kelas')
                    ? $user->kelasWali()->get()
                    : [],
                'delegasiPiket' => ($user && $user->role === 'guru_piket')
                    ? $user->delegasiPiket()
                        ->where('tgl_mulai', '<=', Carbon::today())
                        ->where('tgl_selesai', '>=', Carbon::today())
                        ->with('kelas')
                        ->get()
                    : [],
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'message' => fn () => $request->session()->get('message'),
            ],
        ];
    }
}
