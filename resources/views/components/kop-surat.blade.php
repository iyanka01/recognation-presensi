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
    {{-- Logo Kiri --}}
    <div>
        @if(isset($pengaturan) && $pengaturan && $pengaturan->logo)
            <img src="{{ Storage::url($pengaturan->logo) }}" alt="Logo" class="kop-logo">
        @elseif(isset($lembaga) && $lembaga && $lembaga->logo)
            <img src="{{ Storage::url($lembaga->logo) }}" alt="Logo" class="kop-logo">
        @else
            <div class="kop-logo" style="border: 1px dashed #cbd5e1; border-radius: 8px;"></div>
        @endif
    </div>

    {{-- Text Kop Surat --}}
    <div class="kop-text">
        @php
            $cfg = $pengaturan ?? $lembaga ?? null;
        @endphp
        @if($cfg && $cfg->nama_yayasan)
            <h2>{{ $cfg->nama_yayasan }}</h2>
        @else
            <h2>KEMENTERIAN AGAMA REPUBLIK INDONESIA</h2>
            <h2>KANTOR KEMENTERIAN AGAMA KABUPATEN / KOTA</h2>
        @endif

        <h1>{{ $cfg->nama_lembaga ?? 'MTsN 1 MAYANTARA' }}</h1>

        <p>
            {{ $cfg->alamat ?? 'Jl. Pendidikan No. 01, Wonogiri' }}
            @if($cfg && $cfg->no_telp)
                • Telp: {{ $cfg->no_telp }}
            @endif
            @if($cfg && $cfg->website)
                • Website: {{ $cfg->website }}
            @endif
        </p>
    </div>

    {{-- Logo Kanan (Mirror) --}}
    <div>
        @if(isset($pengaturan) && $pengaturan && $pengaturan->logo)
            <img src="{{ Storage::url($pengaturan->logo) }}" alt="Logo" class="kop-logo">
        @elseif(isset($lembaga) && $lembaga && $lembaga->logo)
            <img src="{{ Storage::url($lembaga->logo) }}" alt="Logo" class="kop-logo">
        @else
            <div class="kop-logo" style="border: 1px dashed #cbd5e1; border-radius: 8px;"></div>
        @endif
    </div>
</div>
