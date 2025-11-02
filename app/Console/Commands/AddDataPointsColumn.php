<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddDataPointsColumn extends Command
{
    protected $signature = 'insights:add-data-points-column';
    protected $description = 'Add data_points column to insights table';

    public function handle()
    {
        try {
            $this->info('Checking if data_points column exists...');

            // カラムが存在しない場合のみ追加
            if (!Schema::hasColumn('insights', 'data_points')) {
                $this->info('Adding data_points column to insights table...');

                // statusカラムが存在するかチェック
                $hasStatus = Schema::hasColumn('insights', 'status');

                if ($hasStatus) {
                    // statusカラムの後に追加
                    DB::statement("ALTER TABLE `insights` ADD COLUMN `data_points` LONGTEXT NULL AFTER `status`");
                    $this->info('✓ Added data_points column after status column.');
                } else {
                    // confidence_scoreカラムの後に追加
                    DB::statement("ALTER TABLE `insights` ADD COLUMN `data_points` LONGTEXT NULL AFTER `confidence_score`");
                    $this->info('✓ Added data_points column after confidence_score column.');
                }

                $this->info('✓ Successfully added data_points column to insights table!');
            } else {
                $this->info('ℹ data_points column already exists. No action needed.');
            }

            // 確認
            if (Schema::hasColumn('insights', 'data_points')) {
                $this->info('✓ Verification: data_points column now exists in insights table.');
            } else {
                $this->error('✗ Error: Failed to verify column creation.');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("✗ Error: " . $e->getMessage());
            $this->info('Trying alternative method...');

            try {
                // 代替方法：afterを指定しない
                if (!Schema::hasColumn('insights', 'data_points')) {
                    DB::statement("ALTER TABLE `insights` ADD COLUMN `data_points` LONGTEXT NULL");
                    $this->info('✓ Added data_points column (alternative method).');
                }
            } catch (\Exception $e2) {
                $this->error("✗ Alternative method also failed: " . $e2->getMessage());
                return 1;
            }
        }

        $this->info('✓ Done!');
        return 0;
    }
}
