<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RunAllMigrations extends Command
{
    protected $signature = 'migrate:all-now';
    protected $description = 'Run migrations for avatar and theme columns';

    public function handle()
    {
        $this->info('=== Running migrations for avatar and theme ===');
        $this->newLine();

        try {
            // avatarカラムの追加
            if (!Schema::hasColumn('users', 'avatar')) {
                $this->info('Adding avatar column...');
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
                $this->info('✓ avatar column added');
                $this->newLine();
            } else {
                $this->info('ℹ avatar column already exists');
                $this->newLine();
            }

            // themeカラムの追加
            if (!Schema::hasColumn('users', 'theme')) {
                $this->info('Adding theme column...');
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
                $this->info('✓ theme column added');
                $this->newLine();
            } else {
                $this->info('ℹ theme column already exists');
                $this->newLine();
            }

            // data_pointsカラムの追加（insightsテーブル）
            if (!Schema::hasColumn('insights', 'data_points')) {
                $this->info('Adding data_points column to insights table...');
                $hasStatus = Schema::hasColumn('insights', 'status');

                if ($hasStatus) {
                    DB::statement("ALTER TABLE `insights` ADD COLUMN `data_points` LONGTEXT NULL AFTER `status`");
                    $this->info('✓ Added data_points column after status column');
                } else {
                    $hasConfidenceScore = Schema::hasColumn('insights', 'confidence_score');
                    if ($hasConfidenceScore) {
                        DB::statement("ALTER TABLE `insights` ADD COLUMN `data_points` LONGTEXT NULL AFTER `confidence_score`");
                        $this->info('✓ Added data_points column after confidence_score column');
                    } else {
                        DB::statement("ALTER TABLE `insights` ADD COLUMN `data_points` LONGTEXT NULL");
                        $this->info('✓ Added data_points column');
                    }
                }
                $this->newLine();
            } else {
                $this->info('ℹ data_points column already exists in insights table');
                $this->newLine();
            }

            $this->info('✓ All migrations completed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());
            $this->newLine();
            $this->info('Please run the following SQL manually:');
            $this->line('ALTER TABLE `users` ADD COLUMN `avatar` VARCHAR(255) NULL;');
            $this->line('ALTER TABLE `users` ADD COLUMN `theme` VARCHAR(255) DEFAULT \'dark\';');
            $this->line('ALTER TABLE `insights` ADD COLUMN `data_points` LONGTEXT NULL;');
            return 1;
        }
    }
}
