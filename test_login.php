<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'admin@sunber.com')->first();
if (!$user) {
    echo "User not found\n";
    exit;
}

$check = \Illuminate\Support\Facades\Hash::check('password', $user->password);
echo "Password match? " . ($check ? 'YES' : 'NO') . "\n";
