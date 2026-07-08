<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\User::where('email', 'admin@sunber.com')->update(['role' => 'admin']);
\App\Models\User::where('email', 'admin@heladeria.com')->update(['role' => 'admin']);
echo "Roles updated.\n";
