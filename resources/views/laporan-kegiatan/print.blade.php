<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan - {{ $kegiatan->nama_kegiatan }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 20px;
            font-size: 13px;
        }
        .info-table {
            width: auto;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 12px;
        }
        .info-table td {
            border: none;
            padding: 3px 10px 3px 0;
            vertical-align: top;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 10px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 10px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
            color: #0f172a;
        }
        td.center {
            text-align: center;
        }
        .footer {
            margin-top: 40px;
            width: 100%;
            page-break-inside: avoid;
        }
        .signature {
            float: right;
            text-align: center;
            width: 250px;
            font-size: 12px;
        }
        .signature-space {
            height: 60px;
        }
        @media print {
            body { padding: 0; }
            @page { margin: 1.5cm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Print / Close Controls (Hidden when printing) -->
    <div class="no-print" style="margin-bottom: 20px; text-align: right; display: flex; justify-content: flex-end; gap: 8px;">
        <button onclick="window.print()" style="padding: 8px 18px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 13px;">Cetak Laporan</button>
        <button onclick="window.close()" style="padding: 8px 18px; background: #64748b; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 13px;">Tutup</button>
    </div>

    <!-- Kop Surat Resmi (Sama persis dengan cetak laporan lain) -->
    @include('components.kop-surat', ['pengaturan' => $pengaturan])

    <!-- Judul Dokumen -->
    <div style="text-align: center; margin: 14px 0 18px 0;">
        <h3 style="font-size: 15px; margin: 0; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
            Rekapitulasi Presensi Kegiatan Khusus
        </h3>
    </div>

    <!-- Metadata Kegiatan -->
    <table class="info-table">
        <tr>
            <td style="font-weight: bold; width: 140px;">Nama Kegiatan</td>
            <td>: <strong>{{ $kegiatan->nama_kegiatan }}</strong> ({{ $kegiatan->kode_kegiatan }})</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Hari, Tanggal</td>
            <td>: {{ $tanggalIndo }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Waktu Pelaksanaan</td>
            <td>: {{ $kegiatan->jam_range }} WIB</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Sasaran Peserta</td>
            <td>: {{ $sasaranText }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Mode Presensi</td>
            <td>: {{ $kegiatan->mode_absen }}</td>
        </tr>
    </table>

    <!-- Ringkasan Statistik -->
    <div style="margin-bottom: 12px; padding: 6px 12px; background: #f8fafc; border: 1px solid #cbd5e1; font-size: 11px; border-radius: 4px;">
        Target Peserta: <strong>{{ $rekap['total'] }}</strong> &bull;
        Hadir: <strong>{{ $rekap['hadir'] }}</strong> &bull;
        Terlambat: <strong>{{ $rekap['terlambat'] }}</strong> &bull;
        Izin / Sakit: <strong>{{ $rekap['izin'] + $rekap['sakit'] }}</strong> &bull;
        Belum Absen: <strong>{{ $rekap['belum_absen'] }}</strong>
    </div>

    <!-- Tabel Daftar Hadir Peserta (Kolom TTD dihilangkan sesuai permintaan) -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35px;">No</th>
                <th style="width: 100px;">NIS / NIP</th>
                <th>Nama Lengkap</th>
                <th style="width: 60px;">Tipe</th>
                <th style="width: 130px;">Kelas / Jabatan</th>
                <th style="width: 75px;">Masuk</th>
                @if($kegiatan->mode_absen === 'Datang & Pulang')
                    <th style="width: 75px;">Pulang</th>
                @endif
                <th style="width: 80px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pesertaList as $index => $item)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td class="center font-mono">{{ $item['nomor_induk'] ?: '-' }}</td>
                <td><strong>{{ $item['nama'] }}</strong></td>
                <td class="center">{{ $item['tipe'] }}</td>
                <td>{{ $item['info'] }}</td>
                <td class="center">{{ $item['jam_masuk'] }}</td>
                @if($kegiatan->mode_absen === 'Datang & Pulang')
                    <td class="center">{{ $item['jam_pulang'] }}</td>
                @endif
                <td class="center">
                    <strong>{{ $item['status'] }}</strong>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $kegiatan->mode_absen === 'Datang & Pulang' ? 8 : 7 }}" class="center" style="padding: 16px;">
                    Tidak ada peserta terdaftar.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan (Sama persis dengan cetak laporan lain) -->
    <div class="footer">
        <div class="signature">
            <p style="margin: 0;">{{ $pengaturan?->tempat ?? 'Tempat' }}, {{ now()->translatedFormat('d F Y') }}</p>
            <p style="margin: 4px 0 0 0; font-weight: bold;">Kepala Lembaga / Sekolah,</p>
            <div class="signature-space"></div>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">
                {{ $pengaturan?->nama_kepala ?? $pengaturan?->nama_kepala_sekolah ?? '....................................' }}
            </p>
            <p style="margin: 2px 0 0 0; font-size: 11px;">
                NIP: {{ $pengaturan?->nip ?? $pengaturan?->nip_kepala_sekolah ?? '-' }}
            </p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
