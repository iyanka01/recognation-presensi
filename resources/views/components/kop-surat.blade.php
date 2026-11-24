{{-- Komponen Kop Surat Resmi Standar Sekolah / Lembaga --}}
<style>
    .kop-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 2.5px solid #000;
        padding-bottom: 10px;
        margin-bottom: 18px;
    }
    .kop-logo {
        width: 75px;
        height: 75px;
        object-fit: contain;
    }
    .kop-text {
        text-align: center;
        flex: 1;
        padding: 0 12px;
    }
    .kop-text h2 {
        font-size: 13px;
        margin: 1px 0;
        font-weight: bold;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: #111;
    }
    .kop-text h1 {
        font-size: 16px;
        margin: 2px 0;
        font-weight: 800;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        color: #000;
    }
    .kop-text p {
        margin: 3px 0 0 0;
        font-size: 11px;
        color: #222;
        line-height: 1.35;
    }
</style>

<div class="kop-container">
    {{-- Logo Kiri (Logo Yayasan / Dinas dari Profil Lembaga) --}}
    <div style="width: 80px; text-align: left; shrink-0;">
        @php
            $cfg = $pengaturan ?? $lembaga ?? null;
        @endphp
        @if($cfg && $cfg->logo)
            <img src="{{ Storage::url($cfg->logo) }}" alt="Logo Yayasan / Dinas" class="kop-logo">
        @else
            <div class="kop-logo" style="border: 1px dashed #cbd5e1; border-radius: 8px;"></div>
        @endif
    </div>

    {{-- Text Kop Surat --}}
    <div class="kop-text">
        @php
            $namaUnitSekolah = isset($activeDepartemen) && $activeDepartemen ? $activeDepartemen->nama_departemen : ($cfg->nama_lembaga ?? 'SMK LENTERA');
        @endphp
        @if($cfg && $cfg->nama_yayasan)
            <h2>{{ $cfg->nama_yayasan }}</h2>
        @else
            <h2>YAYASAN PENDIDIKAN</h2>
        @endif

        <h1>{{ $namaUnitSekolah }}</h1>

        <p>
            @if(isset($activeDepartemen) && $activeDepartemen && $activeDepartemen->alamat)
                {{ $activeDepartemen->alamat }}
            @else
                {{ $cfg->alamat ?? 'Jl. Pendidikan No. 01' }}
            @endif
            @if($cfg && $cfg->tempat)
                — {{ $cfg->tempat }}
            @endif
            @if(isset($activeDepartemen) && $activeDepartemen && $activeDepartemen->no_telp)
                • Telp: {{ $activeDepartemen->no_telp }}
            @elseif($cfg && $cfg->no_telp)
                • Telp: {{ $cfg->no_telp }}
            @endif
            @if($cfg && $cfg->website)
                • Website: {{ $cfg->website }}
            @endif
        </p>
    </div>

    {{-- Logo Kanan (Logo Khusus Unit Sekolah sesuai Departemen) --}}
    <div style="width: 80px; text-align: right; shrink-0;">
        @if(isset($activeDepartemen) && $activeDepartemen && $activeDepartemen->logo)
            <img src="{{ Storage::url($activeDepartemen->logo) }}" alt="Logo Departemen" class="kop-logo">
        @elseif($cfg && $cfg->logo)
            {{-- Jika departemen belum upload logo khusus, tampilkan logo lembaga sebagai penyeimbang --}}
            <img src="{{ Storage::url($cfg->logo) }}" alt="Logo" class="kop-logo">
        @else
            <div class="kop-logo" style="border: 1px dashed #cbd5e1; border-radius: 8px;"></div>
        @endif
    </div>
</div>
