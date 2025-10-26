<?php

namespace App\Enums;

enum Priority: string
{
    case HIGH = 'high';
    case MEDIUM = 'medium';
    case LOW = 'low';

    /**
     * ラベルを取得
     */
    public function label(): string
    {
        return match ($this) {
            self::HIGH => '高',
            self::MEDIUM => '中',
            self::LOW => '低',
        };
    }

    /**
     * バッジの色を取得
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::HIGH => 'danger',
            self::MEDIUM => 'warning',
            self::LOW => 'neutral',
        };
    }

    /**
     * スコアを取得（ソート用）
     */
    public function score(): int
    {
        return match ($this) {
            self::HIGH => 3,
            self::MEDIUM => 2,
            self::LOW => 1,
        };
    }
}
