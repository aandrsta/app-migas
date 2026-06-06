<?php

// Vercel is read-only. We override storage paths to /tmp so Laravel can write compiled views, cache, and sessions.
$storagePath = '/tmp/storage';
$neededDirs = [
    $storagePath . '/framework/views',
    $storagePath . '/framework/cache',
    $storagePath . '/framework/sessions',
    $storagePath . '/bootstrap/cache',
];

foreach ($neededDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Override storage paths for Laravel config
putenv("VIEW_COMPILED_PATH={$storagePath}/framework/views");

// Register autoload
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap the application
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Set storage path to /tmp/storage dynamically on Vercel
$app->useStoragePath($storagePath);

// Run the application
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
)->send();

$kernel->terminate($request, $response);
