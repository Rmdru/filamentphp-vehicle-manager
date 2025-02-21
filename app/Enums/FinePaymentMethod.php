<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Enums\IconPosition;

enum FinePaymentMethod: string implements HasLabel, HasIcon
{
    case Cash = 'cash';
    case BankCard = 'bank_card';
    case Online = 'online';
    case DirectDebit = 'direct_debit';
    case BankTransfer = 'bank_transfer';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Cash => __('Cash'),
            self::BankCard => __('Bank card'),
            self::Online => __('Online'),
            self::DirectDebit => __('Direct debit'),
            self::BankTransfer => __('Bank transfer'),
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Cash => 'mdi-hand-coin-outline',
            self::BankCard => 'gmdi-credit-card',
            self::Online => 'gmdi-qr-code',
            self::DirectDebit => 'fas-file-invoice-dollar',
            self::BankTransfer => 'mdi-bank-transfer',
        };
    }
}
