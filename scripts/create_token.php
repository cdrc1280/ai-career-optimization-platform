<?php
// Bootstrap Laravel and create a personal access token for testing
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$email = 'apitest-token@example.com';
$password = 'secret123';

$user = User::where('email', $email)->first();
if (!$user) {
    $user = User::create([
        'name' => 'API Token Test',
        'email' => $email,
        'password' => bcrypt($password),
    ]);
    echo "Created user: {$email}\n";
} else {
    echo "Found user: {$email}\n";
}
$token = $user->createToken('cli-test-token')->plainTextToken;
echo "TOKEN:" . $token . "\n";
