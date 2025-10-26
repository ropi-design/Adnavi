<?php

namespace App\Enums;

enum ReportStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    /**
     * ラベルを取得
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => '待機中',
            self::PROCESSING => '処理中',
            self::COMPLETED => '完了',
            self::FAILED => '失敗',
        };
    }

    /**
     * バッジの色を取得
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::PENDING => 'neutral',
            self::PROCESSING => 'info',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
        };
    }

    /**
     * 完了しているかチェック
     */
    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * 処理中かチェック
     */
    public function isProcessing(): bool
    {
        return $this === self::PROCESSING;
    }
}
