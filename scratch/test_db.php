<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $users = DB::connection('central')->table('users')->where('name', 'like', '%John%')->orWhere('name', 'like', '%Gil%')->get();
    echo "Users matching John or Gil:\n";
    print_r($users);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
