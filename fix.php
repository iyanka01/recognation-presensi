<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\AdminUser::where('username', 'admin')->first();
$user->password = \Illuminate\Support\Facades\Hash::make('admin123');
$user->save();
echo "Password Reset OK!";
