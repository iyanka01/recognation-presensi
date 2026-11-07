<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Presensi;
use Carbon\Carbon;

class IzinController extends Controller
{
    public function index()
    {
        $siswas = Siswa::with('kelas')->orderBy('nama_lengkap')->get();
        $gurus = Guru::orderBy('nama_lengkap')->get();
        
        $izins = Presensi::whereIn('status', ['Izin', 'Sakit', 'Alpa', 'Dinas Luar', 'Cuti', 'Dispensasi'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('izin.index', compact('siswas', 'gurus', 'izins'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required', // Format: "siswa|uuid" or "guru|uuid"
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:Izin,Sakit,Alpa,Dinas Luar,Cuti,Dispensasi',
            'keterangan' => 'nullable|string|max:255'
        ]);

        $parts = explode('|', $request->user_id);
        if(count($parts) !== 2) {
            return redirect()->back()->with('error', 'Format user tidak valid.');
        }

        $userType = $parts[0];
        $userId = $parts[1];

        $mulai = Carbon::parse($request->tanggal_mulai);
        $selesai = Carbon::parse($request->tanggal_selesai);
        $diff = $mulai->diffInDays($selesai);

        // Validasi max 30 hari untuk mencegah spam
        if ($diff > 30) {
            return redirect()->back()->with('error', 'Rentang tanggal maksimal adalah 30 hari.');
        }

        $berhasil = 0;
        for ($i = 0; $i <= $diff; $i++) {
            $tgl = $mulai->copy()->addDays($i)->toDateString();

            // Cek apakah sudah ada presensi hari itu (Mungkin Hadir/Terlambat/Izin)
            $existing = Presensi::where('user_type', $userType)
                ->where('user_id', $userId)
                ->where('tanggal', $tgl)
                ->first();

            if ($existing) {
                // Update jika ada (misal menimpa Alpa jadi Sakit, atau Hadir jadi Izin)
                $existing->update([
                    'status' => $request->status,
                    'keterangan' => $request->keterangan
                ]);
            } else {
                // Create baru
                Presensi::create([
                    'user_type' => $userType,
                    'user_id' => $userId,
                    'tanggal' => $tgl,
                    'status' => $request->status,
                    'keterangan' => $request->keterangan
                ]);
            }
            $berhasil++;
        }

        return redirect()->route('izin.index')->with('success', "Berhasil mencatat $berhasil hari perizinan/ketidakhadiran.");
    }
}
