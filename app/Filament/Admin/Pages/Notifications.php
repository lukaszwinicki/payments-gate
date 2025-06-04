<?php

namespace App\Filament\Admin\Pages;

use App\Enums\TransactionStatus;
use App\Models\Notification;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;

class Notifications extends Page implements HasTable
{
    use InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.pages.notifications';

    public function table(Table $table): Table
    {
        return $table
            ->query(Notification::query())
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID'),
                Tables\Columns\TextColumn::make('transaction_id')->label('ID transaction'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status notification')
                    ->color(fn($state) => match ($state) {
                        TransactionStatus::SUCCESS, TransactionStatus::REFUND_SUCCESS => 'success',
                        TransactionStatus::PENDING, TransactionStatus::REFUND_PENDING => 'info',
                        TransactionStatus::FAIL, TransactionStatus::REFUND_FAIL => 'danger',
                        default => null,
                    }),
                Tables\Columns\TextColumn::make('type_status')
                    ->label('Status transaction')
                    ->color(fn($state) => match ($state) {
                        TransactionStatus::SUCCESS, TransactionStatus::REFUND_SUCCESS => 'success',
                        TransactionStatus::PENDING, TransactionStatus::REFUND_PENDING => 'info',
                        TransactionStatus::FAIL, TransactionStatus::REFUND_FAIL => 'danger',
                        default => null,
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Date created'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}