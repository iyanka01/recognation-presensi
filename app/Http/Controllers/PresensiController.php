<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\PengaturanLembaga;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Services\Notification\PresensiNotificationService;
use App\Services\Presensi\PresensiScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresensiController extends Controller
{
    public function __construct(
        protected PresensiScheduleService $scheduleService,
        protected PresensiNotificationService $notificationService
    ) {}

    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $isFilteringDate = $request->filled('tanggal');
        $tanggal = $request->input('tanggal', $today);

        if ($isFilteringDate) {
            $presensi = Presensi::whereDate('tanggal', $tanggal)
                ->orderBy('jam_masuk', 'desc')
                ->get();
        } else {
            $presensiHariIni = Presensi::whereDate('tanggal', $today)
                ->orderBy('jam_masuk', 'desc')
                ->get();

            $guruBelumPulangSebelumnya = Presensi::where('user_type', 'guru')
                ->whereDate('tanggal', '<', $today)
                ->whereNotNull('jam_masuk')
                ->whereNull('jam_pulang')
                ->where('is_unlocked', false)
                ->orderBy('tanggal', 'desc')
                ->orderBy('jam_masuk', 'desc')
                ->get();

            $presensi = $guruBelumPulangSebelumnya->concat($presensiHariIni);
        }

        return view('presensi.index', compact('presensi', 'tanggal', 'isFilteringDate'));
    }

    public function scan()
    {
        $totalHariIni = Presensi::whereDate('tanggal', Carbon::today()->toDateString())->count();
        $pengaturan = PengaturanLembaga::first();

        return view('presensi.scan', compact('totalHariIni', 'pengaturan'));
    }

    public function processScan(Request $request)
    {
        $qr = $request->qr_code;
        if (!$qr) {
            return response()->json(['success' => false, 'message' => 'QR Code tidak valid.']);
        }

        $user = $this->resolveUserByQr($qr);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan di sistem.']);
        }

        $today = Carbon::today()->toDateString();

        if ($hariLibur = $this->scheduleService->cekHariLibur($today)) {
            return response()->json([
                'success' => false,
                'message' => 'Presensi ditolak. Hari ini adalah hari libur: ' . $hariLibur->keterangan,
            ]);
        }

        $userType = $user instanceof Guru ? 'guru' : 'siswa';

        $cekIzin = Presensi::where('user_type', $userType)
            ->where('user_id', $user->id)
            ->where('tanggal', $today)
            ->whereIn('status', ['Izin', 'Sakit', 'Alpa'])
            ->first();

        if ($cekIzin) {
            return response()->json([
                'success' => false,
                'message' => "Presensi ditolak. {$user->nama_lengkap} sudah tercatat {$cekIzin->status} hari ini.",
            ]);
        }

        $hariIni = Carbon::now()->locale('id')->dayName;
        $pengaturan = $this->scheduleService->getPengaturanJam($user, $userType, $hariIni);

        if (!$pengaturan) {
            return response()->json([
                'success' => false,
                'message' => "Jadwal absensi untuk hari {$hariIni} belum diatur oleh admin.",
            ]);
        }

        $now = Carbon::now();
        $jamSekarang = $now->format('H:i:s');

        if ($jamSekarang < $pengaturan->jam_mulai) {
            return response()->json([
                'success' => false,
                'message' => 'Belum waktunya absen. Absen dibuka jam ' . substr($pengaturan->jam_mulai, 0, 5),
            ]);
        }

        $presensi = Presensi::where('user_type', $userType)
            ->where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        if ($presensi) {
            return $this->handleCheckOut($presensi, $user, $userType, $pengaturan, $today, $now, $jamSekarang);
        }

        return $this->handleCheckIn($user, $userType, $pengaturan, $today, $jamSekarang);
    }

    public function unlock($id)
    {
        $presensi = Presensi::findOrFail($id);
        $adminName = Auth::user() ? (Auth::user()->nama_lengkap ?? Auth::user()->username ?? 'Admin') : 'Admin';

        $presensi->update([
            'is_unlocked' => true,
            'unlocked_at' => Carbon::now(),
            'unlocked_by' => $adminName,
        ]);

        $guruNama = $presensi->user ? $presensi->user->nama_lengkap : 'Guru';
        $tglFmt = Carbon::parse($presensi->tanggal)->locale('id')->isoFormat('D MMMM Y');

        return redirect()->back()->with(
            'success',
            "Kunci presensi untuk {$guruNama} pada tanggal {$tglFmt} berhasil dibuka. Guru sekarang dapat melakukan absen masuk kembali."
        );
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'string|exists:presensi,id',
        ]);

        Presensi::whereIn('id', $request->ids)->delete();

        return redirect()->back()->with('success', count($request->ids) . ' data presensi berhasil di-reset (dihapus).');
    }

    protected function resolveUserByQr(string $qr): Siswa|Guru|null
    {
        return Siswa::find($qr)
            ?? Guru::find($qr)
            ?? Siswa::where('nis', $qr)->first()
            ?? Guru::where('nip', $qr)->first();
    }

    protected function handleCheckOut(
        Presensi $presensi,
        Siswa|Guru $user,
        string $userType,
        $pengaturan,
        string $today,
        Carbon $now,
        string $jamSekarang
    ) {
        if ($presensi->jam_pulang) {
            return response()->json([
                'success' => false,
                'message' => 'Presensi ditolak. Anda sudah melakukan absen pulang hari ini.',
            ]);
        }

        if ($jamSekarang < $pengaturan->jam_pulang) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah absen masuk! Belum waktunya pulang (buka jam ' . substr($pengaturan->jam_pulang, 0, 5) . ').',
            ]);
        }

        $lastScan = Carbon::parse($today . ' ' . $presensi->jam_masuk);
        if ($now->diffInSeconds($lastScan) < 30) {
            return response()->json([
                'success' => false,
                'message' => 'Anda baru saja absen masuk. Harap tunggu sebentar.',
            ]);
        }

        $presensi->jam_pulang = $jamSekarang;
        $presensi->save();

        $this->notificationService->sendPulangNotification($user, $userType, $jamSekarang, $presensi->status);

        return response()->json([
            'success' => true,
            'type' => 'pulang',
            'status' => $presensi->status,
            'user_name' => $user->nama_lengkap,
            'message' => "Presensi PULANG berhasil. Hati-hati di jalan, {$user->nama_lengkap}!",
        ]);
    }

    protected function handleCheckIn(
        Siswa|Guru $user,
        string $userType,
        $pengaturan,
        string $today,
        string $jamSekarang
    ) {
        if ($userType === 'guru') {
            $lockedPresensi = Presensi::where('user_type', 'guru')
                ->where('user_id', $user->id)
                ->whereDate('tanggal', '<', $today)
                ->whereNotNull('jam_masuk')
                ->whereNull('jam_pulang')
                ->where('is_unlocked', false)
                ->orderBy('tanggal', 'desc')
                ->first();

            if ($lockedPresensi) {
                $tglLocked = Carbon::parse($lockedPresensi->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y');
                return response()->json([
                    'success' => false,
                    'is_locked' => true,
                    'message' => "🔒 Presensi Ditolak: {$user->nama_lengkap} tidak melakukan absen pulang pada hari {$tglLocked}. Hubungi Administrator untuk membuka kunci kehadiran.",
                ]);
            }
        }

        $status = $jamSekarang > $pengaturan->jam_terlambat ? 'Terlambat' : 'Hadir';

        Presensi::create([
            'user_type' => $userType,
            'user_id' => $user->id,
            'tanggal' => $today,
            'jam_masuk' => $jamSekarang,
            'status' => $status,
        ]);

        $this->notificationService->sendMasukNotification($user, $userType, $jamSekarang, $status);

        return response()->json([
            'success' => true,
            'type' => 'masuk',
            'status' => $status,
            'user_name' => $user->nama_lengkap,
            'message' => "Presensi MASUK berhasil. Selamat datang, {$user->nama_lengkap}!" . ($status === 'Terlambat' ? ' (Terlambat)' : ''),
        ]);
    }
}
