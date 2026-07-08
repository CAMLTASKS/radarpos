<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::all();
foreach ($users as $user) {
    echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}\n";
}

$admin = \App\Models\User::updateOrCreate(
    ['email' => 'admin@sunber.com'],
    [
        'name' => 'Admin',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
        'role' => 'admin'
    ]
);
echo "Password forced for admin@sunber.com\n";
