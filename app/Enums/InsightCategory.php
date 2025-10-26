<?php

namespace App\Enums;

enum InsightCategory: string
{
    case PERFORMANCE = 'performance';
    case BUDGET = 'budget';
    case TARGETING = 'targeting';
    case CREATIVE = 'creative';
    case CONVERSION = 'conversion';

    /**
     * ラベルを取得
     */
    public function label(): string
    {
        return match ($this) {
            self::PERFORMANCE => 'パフォーマンス',
            self::BUDGET => '予算',
            self::TARGETING => 'ターゲティング',
            self::CREATIVE => 'クリエイティブ',
            self::CONVERSION => 'コンバージョン',
        };
    }

    /**
     * アイコンを取得
     */
    public function icon(): string
    {
        return match ($this) {
            self::PERFORMANCE => 'chart-bar',
            self::BUDGET => 'currency-yen',
            self::TARGETING => 'user-group',
            self::CREATIVE => 'photo',
            self::CONVERSION => 'check-circle',
        };
    }

    /**
     * 説明を取得
     */
    public function description(): string
    {
        return match ($this) {
            self::PERFORMANCE => '広告のパフォーマンスに関する洞察',
            self::BUDGET => '予算配分や費用効率に関する洞察',
            self::TARGETING => 'ターゲット設定や配信に関する洞察',
            self::CREATIVE => '広告クリエイティブに関する洞察',
            self::CONVERSION => 'コンバージョン最適化に関する洞察',
        };
    }
}
