<?php

namespace App\Enums;

enum RecommendationStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case IMPLEMENTED = 'implemented';
    case DISMISSED = 'dismissed';

    /**
     * ラベルを取得
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => '未着手',
            self::IN_PROGRESS => '実施中',
            self::IMPLEMENTED => '実施済み',
            self::DISMISSED => '却下',
        };
    }

    /**
     * バッジの色を取得
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::PENDING => 'neutral',
            self::IN_PROGRESS => 'info',
            self::IMPLEMENTED => 'success',
            self::DISMISSED => 'ghost',
        };
    }
}
