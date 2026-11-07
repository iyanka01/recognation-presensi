<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rincian Jurnal & Jam Mengajar - {{ $guru->nama_lengkap }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #222;
            margin: 0;
            padding: 24px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
            border-bottom: 2px solid #222;
            padding-bottom: 14px;
        }
        .header img {
            height: 65px;
            object-fit: contain;
            margin-bottom: 6px;
        }
        .header h1 {
            margin: 0;
            font-size: 17px;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 4px 0 0 0;
            font-size: 13px;
            font-weight: normal;
            color: #444;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 3px 6px;
            border: none;
            font-size: 12px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 20px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #777;
            padding: 6px 8px;
            vertical-align: top;
        }
        table.data-table th {
            background-color: #f0f4f8;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 10.5px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .signature-box {
            float: right;
            text-align: center;
            width: 230px;
            font-size: 12px;
        }
        .signature-space {
            height: 65px;
        }
        @media print {
            body { padding: 0; }
            @page {
                size: A4 portrait;
                margin: 1.2cm;
            }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 18px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0284c7; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak Rincian
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #64748b; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; margin-left: 6px;">
            Tutup
        </button>
    </div>

    <!-- Kop Surat Lembaga Standar -->
    @include('components.kop-surat', ['pengaturan' => $pengaturan])

    <div style="text-align: center; margin: 16px 0 20px 0;">
        <h3 style="font-size: 15px; margin: 0; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">RINCIAN JURNAL & JAM MENGAJAR GURU</h3>
    </div>

    <!-- Info Guru -->
    <table class="meta-table">
        <tr>
            <td style="width: 140px; font-weight: bold;">Nama Guru</td>
            <td style="width: 10px;">:</td>
            <td><strong>{{ $guru->nama_lengkap }}</strong></td>
            <td style="width: 120px; font-weight: bold;">Periode</td>
            <td style="width: 10px;">:</td>
            <td>{{ \Carbon\Carbon::parse($dari)->translatedFormat('d F Y') }} – {{ \Carbon\Carbon::parse($sampai)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">NIP</td>
            <td>:</td>
            <td>{{ $guru->nip ?? '-' }}</td>
            <td style="font-weight: bold;">Total Jam Mengajar</td>
            <td>:</td>
            <td><strong style="font-size: 13px;">{{ $totalJam }} JP</strong> ({{ $totalPertemuan }} Pertemuan)</td>
        </tr>
    </table>

    <!-- Tabel Rincian Jurnal Mengajar -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 13%;">Tanggal</th>
                <th style="width: 18%; text-align: left;">Mata Pelajaran</th>
                <th style="width: 13%;">Kelas</th>
                <th style="width: 8%;">Jam (JP)</th>
                <th style="text-align: left;">Materi Pembelajaran</th>
                <th style="text-align: left; width: 22%;">Keterangan / Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jurnals as $i => $j)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td class="text-center font-bold">
                    {{ \Carbon\Carbon::parse($j->tanggal)->translatedFormat('d/m/Y') }}<br>
                    <small style="color: #666; font-weight: normal;">{{ \Carbon\Carbon::parse($j->tanggal)->locale('id')->dayName }}</small>
                </td>
                <td>{{ $j->jadwal->mataPelajaran->nama_mapel ?? '-' }}</td>
                <td class="text-center">{{ $j->jadwal->kelas ? 'Kelas ' . $j->jadwal->kelas->tingkat . ' ' . $j->jadwal->kelas->nama_rombel : '-' }}</td>
                <td class="text-center font-bold" style="font-size: 12px; background: #fafafa;">{{ $j->jumlah_jam }} JP</td>
                <td>{{ $j->materi }}</td>
                <td>{{ $j->keterangan ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 20px; color: #777;">Belum ada riwayat jurnal mengajar pada rentang tanggal ini.</td>
            </tr>
            @endforelse
        </tbody>
        @if($jurnals->count() > 0)
        <tfoot>
            <tr style="background: #e2e8f0; font-weight: bold;">
                <td colspan="4" class="text-right">TOTAL JAM PELAJARAN:</td>
                <td class="text-center" style="font-size: 12px;">{{ $totalJam }} JP</td>
                <td colspan="2">({{ $totalPertemuan }} Kali Tatap Muka)</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Tanda Tangan Guru & Kepala Sekolah -->
    <div style="width: 100%; margin-top: 36px; clear: both;">
        <div style="float: left; text-align: center; width: 220px; font-size: 12px;">
            <p style="margin: 0;">Mengetahui,</p>
            <p style="margin: 4px 0 0 0; font-weight: bold;">Kepala Sekolah / Lembaga</p>
            <div class="signature-space"></div>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $pengaturan->nama_kepala ?? '....................................' }}</p>
            <p style="margin: 2px 0 0 0; font-size: 11px; color: #555;">NIP: {{ $pengaturan->nip_kepala ?? '........................' }}</p>
        </div>

        <div class="signature-box">
            <p style="margin: 0;">{{ $pengaturan->kabupaten ?? 'Tempat' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p style="margin: 4px 0 0 0; font-weight: bold;">Guru Yang Bersangkutan,</p>
            <div class="signature-space"></div>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $guru->nama_lengkap }}</p>
            <p style="margin: 2px 0 0 0; font-size: 11px; color: #555;">NIP: {{ $guru->nip ?? '........................' }}</p>
        </div>
    </div>

</body>
</html>
