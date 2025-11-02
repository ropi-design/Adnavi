<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== Running migrations for avatar and theme ===\n\n";

try {
    // avatarカラムの追加
    if (!Schema::hasColumn('users', 'avatar')) {
        echo "Adding avatar column...\n";
        $hasEmail = Schema::hasColumn('users', 'email');
        $hasEmailVerifiedAt = Schema::hasColumn('users', 'email_verified_at');

        if ($hasEmail) {
            if ($hasEmailVerifiedAt) {
                DB::statement("ALTER TABLE `users` ADD COLUMN `avatar` VARCHAR(255) NULL AFTER `email_verified_at`");
            } else {
                DB::statement("ALTER TABLE `users` ADD COLUMN `avatar` VARCHAR(255) NULL AFTER `email`");
            }
        } else {
            DB::statement("ALTER TABLE `users` ADD COLUMN `avatar` VARCHAR(255) NULL");
        }
        echo "✓ avatar column added\n\n";
    } else {
        echo "ℹ avatar column already exists\n\n";
    }

    // themeカラムの追加
    if (!Schema::hasColumn('users', 'theme')) {
        echo "Adding theme column...\n";
        $hasEmailVerifiedAt = Schema::hasColumn('users', 'email_verified_at');

        if ($hasEmailVerifiedAt) {
            DB::statement("ALTER TABLE `users` ADD COLUMN `theme` VARCHAR(255) DEFAULT 'dark' AFTER `email_verified_at`");
        } else {
            $hasEmail = Schema::hasColumn('users', 'email');
            if ($hasEmail) {
                DB::statement("ALTER TABLE `users` ADD COLUMN `theme` VARCHAR(255) DEFAULT 'dark' AFTER `email`");
            } else {
                DB::statement("ALTER TABLE `users` ADD COLUMN `theme` VARCHAR(255) DEFAULT 'dark'");
            }
        }
        echo "✓ theme column added\n\n";
    } else {
        echo "ℹ theme column already exists\n\n";
    }

    echo "✓ All migrations completed successfully!\n";
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nPlease run the following SQL manually:\n";
    echo "ALTER TABLE `users` ADD COLUMN `avatar` VARCHAR(255) NULL;\n";
    echo "ALTER TABLE `users` ADD COLUMN `theme` VARCHAR(255) DEFAULT 'dark';\n";
    exit(1);
}
