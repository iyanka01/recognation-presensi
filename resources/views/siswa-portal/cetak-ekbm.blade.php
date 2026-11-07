<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kehadiran EKBM {{ strtoupper($siswa->nama_lengkap) }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.2cm 1.5cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 15px;
            font-size: 12px;
            line-height: 1.4;
        }
        .no-print {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #cbd5e1;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.2s;
        }
        .btn-primary {
            background-color: #0284c7;
            color: #fff;
        }
        .btn-primary:hover {
            background-color: #0369a1;
        }
        .btn-secondary {
            background-color: #f1f5f9;
            color: #334155;
            border-color: #cbd5e1;
        }
        .btn-secondary:hover {
            background-color: #e2e8f0;
        }

        /* Kop Surat */
        .kop-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2.5px solid #000;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .kop-logo {
            width: 75px;
            height: 75px;
            object-fit: contain;
        }
        .kop-text {
            text-align: center;
            flex: 1;
            padding: 0 10px;
        }
        .kop-text h2 {
            font-size: 13px;
            margin: 1px 0;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .kop-text h1 {
            font-size: 15px;
            margin: 2px 0;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .kop-text p {
            margin: 2px 0 0 0;
            font-size: 10.5px;
            color: #222;
        }

        /* Judul Dokumen */
        .doc-title {
            text-align: center;
            margin: 18px 0 22px 0;
        }
        .doc-title h3 {
            font-size: 14px;
            margin: 0;
            font-weight: 900;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }
        .doc-title p {
            font-size: 11.5px;
            margin: 4px 0 0 0;
            font-weight: bold;
            color: #333;
        }

        /* Tabel Presensi */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 40px;
        }
        table.data-table th, 
        table.data-table td {
            border: 1px solid #444;
            padding: 6px 8px;
        }
        table.data-table th {
            background-color: #d1d5db;
            color: #000;
            font-weight: bold;
            text-align: center;
            text-transform: capitalize;
        }
        table.data-table td.center {
            text-align: center;
        }

        /* Tanda Tangan */
        .signature-section {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .sig-col {
            width: 40%;
            font-size: 11.5px;
        }
        .sig-space {
            height: 70px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
            table.data-table th {
                background-color: #e5e7eb !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body onload="window.print()">

    {{-- Tombol Navigasi / Cetak Layar --}}
    <div class="no-print">
        <a href="javascript:history.back()" class="btn btn-secondary">
            ← Kembali
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Cetak Dokumen
        </button>
    </div>

    {{-- KOP SURAT STANDAR --}}
    @include('components.kop-surat', ['pengaturan' => $lembaga])

    {{-- JUDUL DOKUMEN --}}
    <div class="doc-title">
        <h3>LAPORAN KEHADIRAN EKBM {{ strtoupper($siswa->nama_lengkap) }}</h3>
        <p>{{ \Carbon\Carbon::parse($dari)->format('d-m-Y') }} s.d {{ \Carbon\Carbon::parse($sampai)->format('d-m-Y') }}</p>
    </div>

    {{-- TABEL PRESENSI EKBM --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35px;">No</th>
                <th>Nama Siswa</th>
                <th style="width: 95px;">NISN</th>
                <th>Pelajaran</th>
                <th>Pengajar</th>
                <th style="width: 60px;">Kelas</th>
                <th style="width: 80px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataMapel as $idx => $m)
            @php
                // Format singkatan keterangan: H = Hadir, I = Izin, S = Sakit, A = Alpa
                $kode = match($m->status) {
                    'Hadir' => 'H',
                    'Izin' => 'I',
                    'Sakit' => 'S',
                    'Alpa' => 'A',
                    default => substr($m->status, 0, 1)
                };
                $kelasNama = $m->jadwal->kelas->nama_rombel ?? ($siswa->kelas->nama_rombel ?? '-');
                $guruNama = $m->jadwal->guru->nama_lengkap ?? ($m->jadwal->guru->nama ?? '-');
                $mapelNama = $m->jadwal->mataPelajaran->nama_mapel ?? '-';
            @endphp
            <tr>
                <td class="center">{{ $idx + 1 }}</td>
                <td>{{ $siswa->nama_lengkap }}</td>
                <td class="center">{{ $siswa->nisn ?? $siswa->nis ?? '-' }}</td>
                <td>{{ $mapelNama }}</td>
                <td>{{ $guruNama }}</td>
                <td class="center">{{ $kelasNama }}</td>
                <td class="center font-bold">{{ $kode }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="center" style="padding: 20px; color: #666; font-style: italic;">
                    Saat ini, data kehadiran E-KBM pada rentang tanggal ini belum ada!
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($dataMapel->count() > 0)
        @php
            $totalHadir = $dataMapel->where('status', 'Hadir')->count();
            $totalIzin  = $dataMapel->where('status', 'Izin')->count();
            $totalSakit = $dataMapel->where('status', 'Sakit')->count();
            $totalAlpa  = $dataMapel->where('status', 'Alpa')->count();
        @endphp
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="6" style="text-align: right; padding: 6px 10px;">TOTAL PERTEMUAN:</td>
                <td class="center">{{ $dataMapel->count() }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- KOTAK REKAPITULASI TOTAL KEHADIRAN (HADIR, IZIN, SAKIT, ALPA) --}}
    @if($dataMapel->count() > 0)
    <div style="margin-top: -25px; margin-bottom: 25px;">
        <table style="width: auto; border-collapse: collapse; font-size: 11px;">
            <tr>
                <td style="border: 1px solid #444; background: #e5e7eb; font-weight: bold; padding: 5px 12px; text-align: center;">Hadir (H)</td>
                <td style="border: 1px solid #444; background: #e5e7eb; font-weight: bold; padding: 5px 12px; text-align: center;">Izin (I)</td>
                <td style="border: 1px solid #444; background: #e5e7eb; font-weight: bold; padding: 5px 12px; text-align: center;">Sakit (S)</td>
                <td style="border: 1px solid #444; background: #e5e7eb; font-weight: bold; padding: 5px 12px; text-align: center;">Alpa (A)</td>
                <td style="border: 1px solid #444; background: #cbd5e1; font-weight: bold; padding: 5px 12px; text-align: center;">Total</td>
            </tr>
            <tr>
                <td style="border: 1px solid #444; text-align: center; font-weight: bold; padding: 5px 12px;">{{ $totalHadir }}</td>
                <td style="border: 1px solid #444; text-align: center; font-weight: bold; padding: 5px 12px;">{{ $totalIzin }}</td>
                <td style="border: 1px solid #444; text-align: center; font-weight: bold; padding: 5px 12px;">{{ $totalSakit }}</td>
                <td style="border: 1px solid #444; text-align: center; font-weight: bold; padding: 5px 12px;">{{ $totalAlpa }}</td>
                <td style="border: 1px solid #444; text-align: center; font-weight: bold; padding: 5px 12px; background: #f8fafc;">{{ $dataMapel->count() }}</td>
            </tr>
        </table>
    </div>
    @endif

    {{-- TANDA TANGAN --}}
    <div class="signature-section">
        <table style="width: 100%; border: none;">
            <tr style="border: none;">
                {{-- Kolom Siswa (Kiri) --}}
                <td class="sig-col" style="border: none; vertical-align: top; text-align: left; padding-left: 20px;">
                    <p style="margin: 0; font-weight: 500;">Siswa</p>
                    <div class="sig-space"></div>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $siswa->nama_lengkap }}</p>
                </td>

                <td style="border: none;"></td>

                {{-- Kolom Wali Kelas (Kanan) --}}
                <td class="sig-col" style="border: none; vertical-align: top; text-align: left; padding-left: 30px;">
                    @php
                        $wali = $siswa->kelas->waliKelas ?? ($siswa->kelas->wali_kelas ?? null);
                        $waliNama = $wali->nama_lengkap ?? ($wali->nama ?? 'Widodo, S.kom');
                        $waliNip = $wali->nip ?? '47821646';
                    @endphp
                    <p style="margin: 0;">Bandar Lampung, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}</p>
                    <p style="margin: 4px 0 0 0; font-weight: 500;">Wali Kelas</p>
                    <div class="sig-space"></div>
                    <p style="margin: 0; font-weight: bold;">{{ $waliNama }}</p>
                    <p style="margin: 2px 0 0 0; font-size: 11px;">NIP. {{ $waliNip }}</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
