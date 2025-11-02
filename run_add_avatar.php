#!/usr/bin/env php
<?php

define('LARAVEL_START', microtime(true));

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== Adding avatar column ===\n";

try {
    if (Schema::hasColumn('users', 'avatar')) {
        echo "✓ Avatar column already exists.\n";
        exit(0);
    }

    echo "Adding avatar column...\n";

    if (Schema::hasColumn('users', 'email_verified_at')) {
        DB::statement("ALTER TABLE `users` ADD COLUMN `avatar` VARCHAR(255) NULL AFTER `email_verified_at`");
        echo "✓ Added avatar column after email_verified_at\n";
    } else {
        DB::statement("ALTER TABLE `users` ADD COLUMN `avatar` VARCHAR(255) NULL");
        echo "✓ Added avatar column\n";
    }

    echo "\n✓ Successfully added avatar column!\n";
    exit(0);
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
