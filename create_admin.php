<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\User::firstOrCreate(
    ['email' => 'admin@sunber.com'],
    [
        'name' => 'Admin', 
        'password' => \Illuminate\Support\Facades\Hash::make('password'), 
        'role' => 'admin'
    ]
);
echo "Admin user created/updated.\n";
