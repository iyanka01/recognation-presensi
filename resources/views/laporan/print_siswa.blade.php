<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judul }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
        }
        .metadata-table {
            width: 100%;
            border: none;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .metadata-table td {
            border: none;
            padding: 3px 0;
        }
        .metadata-table td.label {
            width: 120px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        td.center {
            text-align: center;
        }
        .summary-box {
            border: 1px solid #333;
            padding: 15px;
            margin-bottom: 30px;
            width: 300px;
        }
        .summary-box h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 16px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .summary-table {
            width: 100%;
            border: none;
        }
        .summary-table td {
            border: none;
            padding: 2px 0;
        }
        .footer {
            margin-top: 50px;
            width: 100%;
        }
        .signature {
            float: right;
            text-align: center;
            width: 240px;
            font-size: 12px;
        }
        .signature-space {
            height: 60px;
        }
        @media print {
            body { padding: 0; }
            @page { margin: 1.5cm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Cetak Laporan</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer;">Tutup</button>
    </div>

    @include('components.kop-surat', ['pengaturan' => $pengaturan])

    <div style="text-align: center; margin: 16px 0 20px 0;">
        <h3 style="font-size: 15px; margin: 0; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">{{ $judul }}</h3>
    </div>

    <table class="metadata-table">
        <tr>
            <td class="label">Nama Lengkap</td>
            <td>: {{ $user->nama_lengkap }}</td>
            <td class="label">Bulan Laporan</td>
            <td>: {{ $bulanStr }}</td>
        </tr>
        <tr>
            <td class="label">Identitas ({{ $tipe == 'siswa' ? 'NIS' : 'NIP' }})</td>
            <td>: {{ $tipe == 'siswa' ? $user->nis : $user->nip }}</td>
            <td class="label">Dicetak pada</td>
            <td>: {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</td>
        </tr>
        @if($tipe == 'siswa')
        <tr>
            <td class="label">Kelas</td>
            <td colspan="3">: {{ $user->kelas->nama_rombel ?? '-' }}</td>
        </tr>
        @endif
    </table>

    <div class="summary-box">
        <h3>Ringkasan Kehadiran</h3>
        <table class="summary-table">
            <tr>
                <td>Hadir Tepat Waktu</td>
                <td>: {{ $rekap['hadir'] }} Hari</td>
            </tr>
            <tr>
                <td>Terlambat</td>
                <td>: {{ $rekap['terlambat'] }} Hari</td>
            </tr>
            <tr>
                <td>Izin</td>
                <td>: {{ $rekap['izin'] }} Hari</td>
            </tr>
            <tr>
                <td>Sakit</td>
                <td>: {{ $rekap['sakit'] }} Hari</td>
            </tr>
            <tr>
                <td>Alpa (Tanpa Keterangan)</td>
                <td>: {{ $rekap['alpa'] }} Hari</td>
            </tr>
            <tr>
                <td colspan="2"><hr style="border-top:1px solid #ccc; border-bottom:none;"></td>
            </tr>
            <tr>
                <td><strong>Persentase Kehadiran</strong></td>
                <td><strong>: {{ $rekap['persen'] }}%</strong></td>
            </tr>
        </table>
    </div>

    <h3 style="font-size: 16px; margin-bottom: 10px;">Rincian Log Presensi</h3>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">Tanggal</th>
                <th width="15%">Jam Masuk</th>
                <th width="15%">Jam Pulang</th>
                <th width="15%">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($presensi as $idx => $row)
            <tr>
                <td class="center">{{ $idx + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($row->tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</td>
                <td class="center">{{ $row->jam_masuk ?? '-' }}</td>
                <td class="center">{{ $row->jam_pulang ?? '-' }}</td>
                <td class="center">{{ $row->status }}</td>
                <td>{{ $row->keterangan ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="center">Tidak ada log presensi pada bulan ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="signature">
            <p style="margin: 0;">{{ $pengaturan->tempat ?? $pengaturan->kabupaten ?? 'Tempat' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p style="margin: 4px 0 0 0; font-weight: bold;">Kepala Lembaga / Sekolah,</p>
            <div class="signature-space"></div>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $pengaturan->nama_kepala ?? '....................................' }}</p>
            <p style="margin: 2px 0 0 0; font-size: 11px;">NIP: {{ $pengaturan->nip ?? '' }}</p>
        </div>
    </div>

</body>
</html>
