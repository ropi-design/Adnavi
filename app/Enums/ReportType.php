<?php

namespace App\Enums;

enum ReportType: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case CUSTOM = 'custom';

    /**
     * ラベルを取得
     */
    public function label(): string
    {
        return match ($this) {
            self::DAILY => '日次レポート',
            self::WEEKLY => '週次レポート',
            self::MONTHLY => '月次レポート',
            self::CUSTOM => 'カスタムレポート',
        };
    }

    /**
     * 説明を取得
     */
    public function description(): string
    {
        return match ($this) {
            self::DAILY => '1日分のデータを分析',
            self::WEEKLY => '1週間分のデータを分析',
            self::MONTHLY => '1ヶ月分のデータを分析',
            self::CUSTOM => '指定期間のデータを分析',
        };
    }

    /**
     * すべての選択肢を取得
     */
    public static function options(): array
    {
        return array_map(
            fn(self $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ],
            self::cases()
        );
    }
}
