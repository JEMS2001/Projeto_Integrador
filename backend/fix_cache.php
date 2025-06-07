<?php

// Script to manually create package manifest
require_once __DIR__ . '/vendor/autoload.php';

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

// Manually create packages.php
$packages = [];
$composerData = json_decode(file_get_contents(__DIR__ . '/composer.json'), true);

if (isset($composerData['require'])) {
    foreach ($composerData['require'] as $package => $version) {
        if (strpos($package, '/') !== false) {
            $packages[$package] = [];
        }
    }
}

file_put_contents(__DIR__ . '/bootstrap/cache/packages.php', '<?php return ' . var_export($packages, true) . ';');
file_put_contents(__DIR__ . '/bootstrap/cache/services.php', '<?php return [];');

echo "Cache files created successfully\n";
