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
        <h3 style="font-size: 15px; margin: 0; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">{{ $judul }}</h3>
    </div>

    <div style="margin-bottom: 20px; text-align: left; font-size: 14px;">
        <table style="width: 100%; border: none;">
            <tr style="border: none;">
                <td style="border: none; padding: 2px 0; width: 100px;">Kelas</td>
                <td style="border: none; padding: 2px 0;">: {{ $kelasStr }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; padding: 2px 0;">Dicetak pada</td>
                <td style="border: none; padding: 2px 0;">: {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Lengkap</th>
                <th>Identitas (NIS/NIP)</th>
                @if($tipe == 'siswa')
                <th>Kelas</th>
                @endif
                <th width="7%">Hadir</th>
                <th width="7%">Telat</th>
                <th width="7%">Izin</th>
                <th width="7%">Sakit</th>
                <th width="7%">Alpa</th>
                <th width="8%">% Hadir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporan as $idx => $row)
            <tr>
                <td class="center">{{ $idx + 1 }}</td>
                <td>{{ $row['user']->nama_lengkap }}</td>
                <td class="center">{{ $tipe == 'siswa' ? $row['user']->nis : $row['user']->nip }}</td>
                @if($tipe == 'siswa')
                <td class="center">{{ $row['user']->kelas->nama_rombel ?? '-' }}</td>
                @endif
                <td class="center">{{ $row['hadir'] ?: '-' }}</td>
                <td class="center">{{ $row['terlambat'] ?: '-' }}</td>
                <td class="center">{{ $row['izin'] ?: '-' }}</td>
                <td class="center">{{ $row['sakit'] ?: '-' }}</td>
                <td class="center">{{ $row['alpa'] ?: '-' }}</td>
                <td class="center"><strong>{{ $row['persen'] }}%</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $tipe == 'siswa' ? 10 : 9 }}" class="center">Tidak ada data untuk ditampilkan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="signature">
            <p>{{ $pengaturan->tempat ?? $pengaturan->kabupaten ?? 'Tempat' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p style="font-weight: bold; margin-bottom: 60px;">Kepala Sekolah / Lembaga,</p>
            <p style="font-weight: bold; text-decoration: underline; margin-bottom: 2px;">{{ $pengaturan->nama_kepala ?? '....................................' }}</p>
            <span style="font-size: 11px; color: #555;">NIP: {{ $pengaturan->nip ?? '........................' }}</span>
        </div>
    </div>

</body>
</html>
