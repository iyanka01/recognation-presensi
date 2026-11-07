<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Jumlah Jam Mengajar Guru</title>
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
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h2 {
            margin: 4px 0 0 0;
            font-size: 14px;
            font-weight: normal;
            color: #444;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 11px;
            color: #666;
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
            vertical-align: middle;
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
        .tag {
            display: inline-block;
            background: #eef2f6;
            border: 1px solid #ccd6e0;
            border-radius: 3px;
            padding: 1px 5px;
            margin: 1px 2px;
            font-size: 10px;
        }
        .footer {
            margin-top: 36px;
            width: 100%;
            display: flex;
            justify-content: flex-end;
        }
        .signature-box {
            float: right;
            text-align: center;
            width: 230px;
            font-size: 12px;
        }
        .signature-space {
            height: 70px;
        }
        .summary-box {
            display: inline-block;
            padding: 8px 12px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            margin-bottom: 14px;
            border-radius: 4px;
            font-size: 11.5px;
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
            🖨️ Cetak Dokumen
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #64748b; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; margin-left: 6px;">
            Tutup
        </button>
    </div>

    <!-- Kop Surat Lembaga -->
    <div class="header">
        @if($pengaturan && $pengaturan->logo)
            <img src="{{ Storage::url($pengaturan->logo) }}" alt="Logo">
        @endif
        <h1>LAPORAN REKAPITULASI JUMLAH JAM MENGAJAR GURU</h1>
        <h2>{{ $pengaturan->nama_lembaga ?? 'SISTEM PRESENSI & JURNAL KBM' }}</h2>
        @if($pengaturan && $pengaturan->alamat)
            <p>{{ $pengaturan->alamat }}</p>
        @endif
    </div>

    <!-- Informasi Dokumen -->
    <table class="meta-table">
        <tr>
            <td style="width: 140px; font-weight: bold;">Periode Rekap</td>
            <td style="width: 10px;">:</td>
            <td><strong>{{ \Carbon\Carbon::parse($dari)->translatedFormat('d F Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($sampai)->translatedFormat('d F Y') }}</strong></td>
            <td style="text-align: right; color: #555;">Dicetak pada: {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }} WIB</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Dasar Perhitungan</td>
            <td>:</td>
            <td colspan="2">Jurnal Kegiatan Belajar Mengajar (KBM) yang telah diisi & divalidasi guru</td>
        </tr>
    </table>

    <!-- Tabel Data Rekap Jam Guru -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 24%; text-align: left;">Nama Guru</th>
                <th style="width: 14%;">NIP</th>
                <th style="text-align: left;">Rincian Mapel yang Diajar</th>
                <th style="width: 10%;">Jadwal Rutin</th>
                <th style="width: 12%;">Tatap Muka</th>
                <th style="width: 13%;">Total Jam (JP)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekap as $idx => $r)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>
                    <strong>{{ $r->guru->nama_lengkap }}</strong>
                </td>
                <td class="text-center">{{ $r->guru->nip ?? '-' }}</td>
                <td>
                    @if(count($r->mapel_summary) > 0)
                        @foreach($r->mapel_summary as $ms)
                            <span class="tag">{{ $ms['nama_mapel'] }}: <strong>{{ $ms['total_jam'] }} JP</strong> ({{ $ms['total_pertemuan'] }}x)</span>
                        @endforeach
                    @else
                        <span style="color: #888; font-style: italic;">Tidak ada jurnal mengajar</span>
                    @endif
                </td>
                <td class="text-center">{{ $r->total_jadwal_mingguan }} kelas/mgg</td>
                <td class="text-center">{{ $r->total_pertemuan }} pertemuan</td>
                <td class="text-center font-bold" style="font-size: 12px; background: #fafafa;">{{ $r->total_jam }} JP</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 20px; color: #777;">Tidak ada data guru yang ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #e2e8f0; font-weight: bold;">
                <td colspan="5" class="text-right">TOTAL KESELURUHAN:</td>
                <td class="text-center">{{ $totalSemuaPertemuan }} Pertemuan</td>
                <td class="text-center" style="font-size: 13px;">{{ $totalSemuaJam }} JP</td>
            </tr>
        </tfoot>
    </table>

    <!-- Tanda Tangan -->
    <div style="width: 100%; margin-top: 30px; clear: both;">
        <div class="signature-box">
            <p style="margin: 0;">{{ $pengaturan->kabupaten ?? 'Tempat' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p style="margin: 4px 0 0 0; font-weight: bold;">Kepala Lembaga / Sekolah,</p>
            <div class="signature-space"></div>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $pengaturan->nama_kepala ?? '....................................' }}</p>
            <p style="margin: 2px 0 0 0; font-size: 11px; color: #555;">NIP: