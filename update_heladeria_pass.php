<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\User::where('email', 'admin@heladeria.com')->update(['password' => \Illuminate\Support\Facades\Hash::make('password')]);
echo "Updated heladeria password\n";
