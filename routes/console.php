<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pengecekan otomatis presensi guru & kirim peringatan WhatsApp jika alpa melebihi ambang batas
Illuminate\Support\Facades\Schedule::command('guru:cek-peringatan-absen')->dailyAt('18:00');

