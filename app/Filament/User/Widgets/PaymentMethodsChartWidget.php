<?php

namespace App\Filament\User\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Enums\PaymentMethod;

class PaymentMethodsChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Share of payment methods';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $rawData = Transaction::select('payment_method', DB::raw('COUNT(*) as total'))
            ->where('merchant_id', auth()->user()->merchant->id)
            ->groupBy('payment_method')
            ->get();

        $labels = $rawData->pluck('payment_method')->map(function (PaymentMethod  $value) {
            try {
                $name = $value->name;
                $label = str_replace('PAYMENT_METHOD_', '', $name);
                return ucfirst(strtolower($label)); 
            } catch (\ValueError) {
                return $value; 
            }
        });

        return [
            'datasets' => [
                [
                    'label' => 'Total',
                    'data' => $rawData->pluck('total'),
                    'backgroundColor' => [
                        '#3B82F6',
                        '#F59E0B',
                        '#10B981',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}