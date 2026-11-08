<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi Mata Pelajaran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
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
        .info {
            margin-bottom: 20px;
            font-size: 14px;
        }
        .footer {
            margin-top: 50px;
            width: 100%;
        }
        .signature {
            float: right;
            text-align: center;
            width: 200px;
        }
        .signature p {
            margin: 0 0 60px 0;
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
        <h3 style="font-size: 15px; margin: 0; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">Rekap Absensi Mata Pelajaran</h3>
    </div>

    <div class="info">
        <table style="width: auto; border: none;">
            <tr style="border: none;">
                <td style="border: none; padding: 2px 10px 2px 0; font-weight: bold;">Kelas</td>
                <td style="border: none; padding: 2px 0;">: {{ $kelas ? 'Kelas ' . $kelas->tingkat . ' ' . $kelas->nama_rombel : '-' }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; padding: 2px 10px 2px 0; font-weight: bold;">Mata Pelajaran</td>
                <td style="border: none; padding: 2px 0;">: {{ $mapel ? $mapel->nama_mapel : 'Semua Mapel' }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; padding: 2px 10px 2px 0; font-weight: bold;">Periode</td>
                <td style="border: none; padding: 2px 0;">: {{ \Carbon\Carbon::parse($dari)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->translatedFormat('d F Y') }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Nama Siswa</th>
                <th style="width: 60px;">Hadir</th>
                <th style="width: 60px;">Izin</th>
                <th style="width: 60px;">Sakit</th>
                <th style="width: 60px;">Alpa</th>
                <th style="width: 90px;">% Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekap as $i => $r)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $r->siswa->nama_lengkap }}</td>
                <td class="center">{{ $r->hadir }}</td>
                <td class="center">{{ $r->izin }}</td>
                <td class="center">{{ $r->sakit }}</td>
                <td class="center">{{ $r->alpa }}</td>
                <td class="center">{{ $r->persen !== null ? $r->persen . '%' : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="center">Tidak ada data.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="signature">
            <p>{{ $pengaturan?->tempat ?? 'Tempat' }}, {{ now()->translatedFormat('d F Y') }}<br>Kepala Sekolah</p>
            <p><br><br><br></p>
            <p><strong>{{ $pengaturan?->nama_kepala ?? '(............................)' }}</strong>@if($pengaturan && $pengaturan->nip)<br>NIP. {{ $pengaturan->nip }}@endif</p>
        </div>
    </div>
</body>
</html>
