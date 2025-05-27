<?php

namespace App\Filament\User\Widgets;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class TransactionStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected function getCards(): array
    {
        $merchantId = auth()->user()->merchant->id;

        return [
            Card::make(
                'Balance PLN',
                number_format(Transaction::where('merchant_id', $merchantId)
                    ->where('currency', 'PLN')
                    ->sum('amount'), 2) . ' PLN'
            ),
            Card::make(
                'Balance USD',
                number_format(Transaction::where('merchant_id', $merchantId)
                    ->where('currency', 'USD')
                    ->sum('amount'), 2) . ' USD'
            ),
            Card::make(
                'Balance EUR',
                number_format(Transaction::where('merchant_id', $merchantId)
                    ->where('currency', 'EUR')
                    ->sum('amount'), 2) . ' EUR'
            ),
            Card::make(
                'Transactions today',
                Transaction::where('merchant_id', $merchantId)
                    ->whereDate('created_at', today())
                    ->count()
            ),
            Card::make(
                'Transactions last 30 days',
                Transaction::where('merchant_id', $merchantId)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count()
            ),
            Card::make(
                'Failed transactions',
                Transaction::where('merchant_id', $merchantId)
                    ->where('status', TransactionStatus::FAIL)
                    ->count()
            ),
        ];
    }
}
