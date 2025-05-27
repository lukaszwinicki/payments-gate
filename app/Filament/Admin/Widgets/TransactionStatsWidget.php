<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\Http;

class TransactionStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected function getCards(): array
    {
        return [
            Card::make('Balance PLN', number_format(Transaction::where('currency', 'PLN')
                ->sum('amount'), 2) . ' PLN'),
            Card::make('Balance USD', number_format(Transaction::where('currency', 'USD')
                ->sum('amount'), 2) . ' USD'),
            Card::make('Balance EUR', number_format(Transaction::where('currency', 'EUR')
                ->sum('amount'), 2) . ' EUR'),
            Card::make('Transactions today', Transaction::whereDate('created_at', today())->count()),
            Card::make('Transactions last 30 days', Transaction::where('created_at', '>=', now()->subDays(30))->count()),
            Card::make('Failed transactions', Transaction::where('status', TransactionStatus::FAIL)->count()),
        ];
    }
}
