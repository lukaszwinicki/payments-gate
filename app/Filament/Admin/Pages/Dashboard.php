<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\PaymentMethodsChartWidget;
use App\Filament\Admin\Widgets\RecentTransactionsWidget;
use App\Filament\Admin\Widgets\TransactionStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.dashboard';

     protected function getHeaderWidgets(): array
    {
        return [
            TransactionStatsWidget::class,
            PaymentMethodsChartWidget::class,
            RecentTransactionsWidget::class
        ];
    }

}
