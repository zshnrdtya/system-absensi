<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class JadwalController extends Controller
{
    public function index(Request $request): Response
    {
        $kelasId = $request->input('kelas_id');
        $hariFilter = $request->input('hari');

        $jadwalList = JadwalPelajaran::query()
            ->when($kelasId, function ($query, $kelasId) {
                $query->where('kelas_id', $kelasId);
            })
            ->when($hariFilter, function ($query, $hariFilter) {
                $query->where('hari', $hariFilter);
            })
            ->with(['kelas', 'mapel', 'guru'])
            ->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
            ->orderBy('jam_mulai')
            ->paginate(10)
            ->withQueryString();

        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $mapelList = MataPelajaran::orderBy('nama')->get();
        $guruList = User::where('role', 'guru_mapel')->where('is_active', true)->orderBy('name')->get();

        return Inertia::render('Admin/Jadwal/Index', [
            'jadwalList' => $jadwalList,
            'kelasList' => $kelasList,
            'mapelList' => $mapelList,
            'guruList' => $guruList,
            'filters' => [
                'kelas_id' => $kelasId,
                'hari' => $hariFilter,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mapel_id' => ['required', 'exists:mata_pelajaran,id'],
            'guru_id' => ['required', 'exists:users,id'],
            'hari' => ['required', Rule::in(['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'])],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ], [
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'mapel_id.required' => 'Mata pelajaran wajib dipilih.',
            'guru_id.required' => 'Guru pengajar wajib dipilih.',
            'hari.required' => 'Hari jadwal wajib dipilih.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
        ]);

        JadwalPelajaran::create($validated);

        return redirect()->back()->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, JadwalPelajaran $jadwal): RedirectResponse
    {
        $validated = $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mapel_id' => ['required', 'exists:mata_pelajaran,id'],
            'guru_id' => ['required', 'exists:users,id'],
            'hari' => ['required', Rule::in(['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'])],
            'jam_mulai' => ['required'],
            'jam_selesai' => ['required', 'after:jam_mulai'],
        ]);

        $jadwal->update($validated);

        return redirect()->back()->with('success', 'Jadwal pelajaran berhasil diperbarui.');
    }

    public function destroy(JadwalPelajaran $jadwal): RedirectResponse
    {
        // FR-MD-07: Proteksi presensi jam
        if ($jadwal->presensiJam()->exists()) {
            return redirect()->back()->with('error', 'Jadwal tidak dapat dihapus karena telah memiliki riwayat presensi jam.');
        }

        $jadwal->delete();

        return redirect()->back()->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }
}
