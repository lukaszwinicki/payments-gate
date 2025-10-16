<?php

namespace App\Filament\Admin\Resources;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Filament\Admin\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Transactions';
    protected static ?string $pluralModelLabel = 'Transactions';
    protected static ?string $slug = 'transactions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('transaction_uuid')->required(),
                Forms\Components\TextInput::make('transaction_id')->required(),
                Forms\Components\TextInput::make('merchant_id')->required(),
                Forms\Components\TextInput::make('refund_code'),
                Forms\Components\TextInput::make('amount')->numeric()->required(),
                Forms\Components\TextInput::make('name')->label('Full name')->required(),
                Forms\Components\TextInput::make('email')->required(),
                Forms\Components\TextInput::make('currency')->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        TransactionStatus::SUCCESS->value => 'SUCCESS',
                        TransactionStatus::PENDING->value => 'PENDING',
                        TransactionStatus::FAIL->value => 'FAIL',
                        TransactionStatus::REFUND_SUCCESS->value => 'REFUND_SUCCESS',
                        TransactionStatus::REFUND_PENDING->value => 'REFUND_PENDING',
                        TransactionStatus::REFUND_FAIL->value => 'REFUND_FAIL',
                    ])
                    ->required(),
                Forms\Components\Select::make('payment_method')
                    ->options([
                        PaymentMethod::PAYMENT_METHOD_TPAY->value => 'TPAY',
                        PaymentMethod::PAYMENT_METHOD_PAYNOW->value => 'PAYNOW',
                        PaymentMethod::PAYMENT_METHOD_NODA->value => 'NODA',
                    ])->required(),
                Forms\Components\TextInput::make('notification_url')->required(),
                Forms\Components\TextInput::make('return_url')->required(),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('transaction_uuid')->label('UUID')->copyable()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('transaction_id')->label('Transactions ID')->copyable()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('merchant_id')->limit(5)->label('Merchant ID')->copyable()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('refund_code')->label('Refund Code')->copyable()->sortable()->searchable(),
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
                Tables\Columns\TextColumn::make('payment_method')->label('Method'),
                Tables\Columns\TextColumn::make('notification_url')->label('Webhook')->copyable()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('return_url')->label('Return URL')->copyable()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->sortable()->searchable()->label('Date')->since(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
