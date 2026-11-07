<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JadwalController extends Controller
{
    const HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    public function index(Request $request)
    {
        $kelasId = $request->query('kelas_id');
        $hari = $request->query('hari');

        $jadwals = JadwalPelajaran::with(['kelas', 'mataPelajaran', 'guru'])
            ->when($kelasId, fn($q) => $q->where('kelas_id', $kelasId))
            ->when($hari, fn($q) => $q->where('hari', $hari))
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('jam_mulai')
            ->get();

        return view('jadwal.index', [
            'jadwals' => $jadwals,
            'kelasList' => Kelas::orderBy('tingkat')->orderBy('nama_rombel')->get(),
            'mapels' => MataPelajaran::orderBy('nama_mapel')->get(),
            'gurus' => Guru::where('status', 'Aktif')->orderBy('nama_lengkap')->get(),
            'hariList' => self::HARI,
            'filterKelas' => $kelasId,
            'filterHari' => $hari,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'guru_id' => 'nullable|exists:guru,id',
            'hari' => ['required', Rule::in(self::HARI)],
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ]);

        if ($error = $this->cekBentrok($validated)) {
            return back()->withInput()->with('error', $error);
        }

        JadwalPelajaran::create($validated);

        return redirect()->route('jadwal.index', [
            'kelas_id' => $validated['kelas_id'],
            'hari' => $validated['hari'],
        ])->with('success', 'Jadwal pelajaran berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $jadwal = JadwalPelajaran::findOrFail($id);

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'guru_id' => 'nullable|exists:guru,id',
            'hari' => ['required', Rule::in(self::HARI)],
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ]);

        if ($error = $this->cekBentrok($validated, $jadwal->id)) {
            return back()->withInput()->with('error', $error);
        }

        $jadwal->update($validated);

        return redirect()->route('jadwal.index', [
            'kelas_id' => $validated['kelas_id'],
            'hari' => $validated['hari'],
        ])->with('success', 'Jadwal pelajaran berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $jadwal = JadwalPelajaran::findOrFail($id);
        $jadwal->delete();

        return back()->with('success', 'Jadwal pelajaran berhasil dihapus!');
    }

    // Cek jadwal bentrok: kelas atau guru yang sama pada hari & rentang jam yang tumpang tindih
    private function cekBentrok(array $data, ?string $ignoreId = null): ?string
    {
        $query = JadwalPelajaran::where('hari', $data['hari'])
            // Rentang jam tumpang tindih
            ->where('jam_mulai', '<', $data['jam_selesai'])
            ->where('jam_selesai', '>', $data['jam_mulai'])
            // Kelas yang sama ATAU guru pengajar yang sama
            ->where(function ($q) use ($data) {
                $q->where('kelas_id', $data['kelas_id']);
                if (!empty($data['guru_id'])) {
                    $q->orWhere('guru_id', $data['guru_id']);
                }
            });

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $bentrok = $query->with(['kelas', 'mataPelajaran'])->first();
        if (!$bentrok) {
            return null;
        }

        if ($bentrok->guru_id === ($data['guru_id'] ?? null) && $bentrok->kelas_id !== $data['kelas_id']) {
            $target = 'Guru pengajar tersebut sudah mengajar di kelas lain';
        } else {
            $target = "Kelas {$bentrok->kelas->tingkat} {$bentrok->kelas->nama_rombel}";
        }

        return "{$target} sudah memiliki jadwal {$bentrok->mataPelajaran->nama_mapel} pada hari {$bentrok->hari}, "
            . substr($bentrok->jam_mulai, 0, 5) . ' - ' . substr($bentrok->jam_selesai, 0, 5)
            . '. Silakan pilih waktu lain.';
    }
}
