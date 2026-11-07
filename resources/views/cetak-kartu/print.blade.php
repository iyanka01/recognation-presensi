<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu ID - Batch</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 2rem;
            min-height: 100vh;
        }

        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, 54mm);
            gap: 20px;
            justify-content: center;
        }

        /* ID Card Layout */
        .id-card {
            width: 54mm; /* Standard ID card size (CR80) */
            height: 86mm;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            position: relative;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-image: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            /* Important for printing layout */
            page-break-inside: avoid;
        }

        /* Header Accent */
        .id-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            border-radius: 0 0 50% 50% / 0 0 20px 20px;
            z-index: 1;
        }

        .id-content {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            height: 100%;
            padding: 1rem 0.5rem;
        }

        .school-name {
            color: white;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 20px;
            text-align: center;
            text-shadow: 0px 1px 2px rgba(0,0,0,0.3);
        }

        .photo-container {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background-color: white;
            padding: 3px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            margin-bottom: 15px;
        }

        .photo {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            background-color: #e2e8f0;
        }

        .user-name {
            font-size: 12px;
            font-weight: 700;
            color: #1e293b;
            text-align: center;
            line-height: 1.2;
            margin-bottom: 4px;
            padding: 0 5px;
        }

        .user-role {
            font-size: 9px;
            color: #4f46e5;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .user-id {
            font-size: 9px;
            color: #64748b;
            margin-bottom: 15px;
        }

        .qr-container {
            margin-top: auto;
            margin-bottom: 10px;
            background: white;
            padding: 5px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .footer {
            font-size: 7px;
            color: #94a3b8;
            margin-top: auto;
            text-align: center;
            width: 100%;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4f46e5;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 100;
        }

        .print-btn:hover {
            background-color: #4338ca;
        }

        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background-color: white;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 100;
            text-decoration: none;
        }

        .back-btn:hover {
            background-color: #f8fafc;
            color: #0f172a;
        }

        /* Print Specific CSS */
        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }
            body {
                background: none;
                padding: 0;
                margin: 0;
            }
            .print-btn, .back-btn {
                display: none;
            }
            .cards-container {
                display: flex;
                flex-wrap: wrap;
                gap: 5mm; /* Gap between cards in print */
                justify-content: flex-start;
            }
            .id-card {
                box-shadow: none;
                border: 1px dashed #cbd5e1; /* Dashed border as cutting guide */
                width: 54mm;
                height: 86mm;
                margin: 0;
                page-break-inside: avoid;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <a href="javascript:history.back()" class="back-btn">
        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Kembali
    </a>

    <button class="print-btn" onclick="window.print()">
        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
        Cetak Sekarang
    </button>

    <div class="cards-container">
        @foreach($users as $user)
        <div class="id-card">
            <div class="id-content">
                <div class="school-name">{{ $globalLembaga->nama_lembaga ?? 'Mayantara Terminal' }}</div>
                
                <div class="photo-container">
                    @php
                        $photoPath = '';
                        if ($type === 'siswa' && $user->foto_siswa) {
                            $photoPath = asset('storage/' . $user->foto_siswa);
                        } elseif ($type === 'guru' && $user->foto_guru) {
                            $photoPath = asset('storage/' . $user->foto_guru);
                        } else {
                            $photoPath = 'https://ui-avatars.com/api/?name='.urlencode($user->nama_lengkap).'&background=e2e8f0&color=475569';
                        }
                    @endphp
                    <img src="{{ $photoPath }}" alt="Foto" class="photo">
                </div>

                <div class="user-name">{{ $user->nama_lengkap }}</div>
                
                @if($type === 'siswa')
                    <div class="user-role">Siswa - Kelas {{ $user->kelas->nama_rombel ?? '' }}</div>
                    <div class="user-id">NIS: {{ $user->nis }}</div>
                @else
                    <div class="user-role">{{ $user->jabatan ?? 'Guru' }}</div>
                    <div class="user-id">NIP: {{ $user->nip ?? '-' }}</div>
                @endif

                <!-- ID khusus untuk tiap QR container -->
                <div class="qr-container" id="qrcode_{{ $user->id }}"></div>

                <div class="footer">
                    Kartu Identitas Resmi & Presensi<br>Harap dibawa setiap hari
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <script>
        window.onload = function() {
            // Data user ID untuk QR Code
            const qrData = [
                @foreach($users as $user)
                { id: "{{ $user->id }}", elementId: "qrcode_{{ $user->id }}" },
                @endforeach
            ];

            qrData.forEach(function(item) {
                new QRCode(document.getElementById(item.elementId), {
                    text: item.id,
                    width: 90,
                    height: 90,
                    colorDark : "#0f172a",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.H
                });
            });

            // Optional auto-print delay
            setTimeout(() => {
                // window.print();
            }, 800);
        };
    </script>
</body>
</html>
