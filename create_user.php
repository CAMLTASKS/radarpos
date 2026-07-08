
\App\Models\User::firstOrCreate(
    ['email' => 'admin@sunber.com'],
    ['name' => 'Admin', 'password' => bcrypt('password')]
);
echo "User created";
