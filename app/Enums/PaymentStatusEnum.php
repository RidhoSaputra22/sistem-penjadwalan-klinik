<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatusEnum: string implements HasColor, HasLabel
{
    case UNPAID = 'unpaid';
    case DP = 'dp';
    case PAID = 'paid';
    case REFUNDED = 'refunded';
    case FAILED = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::UNPAID => 'Belum Bayar',
            self::DP => 'DP',
            self::PAID => 'Lunas',
            self::REFUNDED => 'Refund',
            self::FAILED => 'Gagal',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::UNPAID => 'gray',
            self::DP => 'warning',
            self::PAID => 'success',
            self::REFUNDED => 'info',
            self::FAILED => 'danger',
        };
    }

    public static function asArray(): array
    {
        return array_map(
            fn (self $status) => [
                'value' => $status->value,
                'label' => $status->getLabel(),
            ],
            self::cases(),
        );
    }
}
