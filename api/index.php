<?php

// Forward Vercel serverless requests to Laravel's public/index.php
$storagePath = '/tmp/storage';
$subDirs = [
    $storagePath . '/framework/views',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/sessions',
    $storagePath . '/logs',
    $storagePath . '/app',
    '/tmp/bootstrap/cache',
];

foreach ($subDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

putenv('APP_STORAGE=' . $storagePath);
putenv('VIEW_COMPILED_PATH=' . $storagePath . '/framework/views');
putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes.php');
putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');

$_ENV['APP_STORAGE'] = $storagePath;
$_ENV['VIEW_COMPILED_PATH'] = $storagePath . '/framework/views';

require __DIR__ . '/../public/index.php';

