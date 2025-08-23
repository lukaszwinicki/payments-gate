<?php

namespace App\Filament\User\Resources;

use App\Enums\TransactionStatus;
use App\Filament\User\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Transactions';
    protected static ?string $pluralModelLabel = 'Transactions';
    protected static ?string $slug = 'transactions';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        return $query->where('merchant_id', auth()->user()->merchant->id);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('row_number')
                    ->label('#')
                    ->sortable(false)
                    ->searchable(false),
                Tables\Columns\TextColumn::make('transaction_uuid')->label('UUID')->copyable()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Full name')->copyable()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->copyable()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn($state, $record) => number_format($state, 2, ',', ' ') . ' ' . $record->currency),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->color(fn($state) => match ($state) {
                        TransactionStatus::SUCCESS, TransactionStatus::REFUND_SUCCESS => 'success',
                        TransactionStatus::PENDING, TransactionStatus::REFUND_PENDING => 'info',
                        TransactionStatus::FAIL, TransactionStatus::REFUND_FAIL => 'danger',
                        default => null,
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'success' => TransactionStatus::SUCCESS,
                        'info' => TransactionStatus::PENDING,
                        'danger' => TransactionStatus::FAIL,
                        default => ucfirst($state->value),
                    }),
                Tables\Columns\TextColumn::make('payment_method')->label('Metoda'),
                Tables\Columns\TextColumn::make('notification_url')->label('Notification URL')->copyable()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('return_url')->label('Return URL')->copyable()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->sortable()->searchable()->label('Date')->since(),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
        ];
    }
}
