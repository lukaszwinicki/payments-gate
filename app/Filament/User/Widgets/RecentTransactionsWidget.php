<?php

namespace App\Filament\User\Widgets;

use App\Enums\TransactionStatus;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;

class RecentTransactionsWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 4;

    protected function getTableQuery(): Builder
    {
        return Transaction::query()
            ->where('merchant_id', auth()->user()->merchant->id)
            ->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')->label('Full name'),
            Tables\Columns\TextColumn::make('email')->label('Email'),
            Tables\Columns\TextColumn::make('amount')->label('Amount')->formatStateUsing(function ($state, $record) {
                return number_format($state, 2, ',', ' ') . ' ' . $record->currency;
            }),
            Tables\Columns\BadgeColumn::make('status')
                ->label('Status')
                ->color(fn($state) => match ($state) {
                    TransactionStatus::SUCCESS, TransactionStatus::REFUND_SUCCESS => 'success',
                    TransactionStatus::PENDING, TransactionStatus::REFUND_PENDING => 'info',
                    TransactionStatus::FAIL, TransactionStatus::REFUND_FAIL => 'danger',
                    default => null,
                }),
            Tables\Columns\TextColumn::make('payment_method')->label('Payment Method'),
            Tables\Columns\TextColumn::make('created_at')->label('Date')->since(),
        ];
    }
}
