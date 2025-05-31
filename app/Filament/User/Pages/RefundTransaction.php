<?php

namespace App\Filament\User\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class RefundTransaction extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-s-arrow-uturn-left';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.pages.refund-transaction';
    protected static ?string $navigationLabel = 'Payment refund (API)';
    protected static ?string $title = 'Payment refund via API';

    public $transactionUuid;

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('transactionUuid')->required(),
        ];
    }

    protected function getFormModel(): string|\Illuminate\Database\Eloquent\Model|null
    {
        return null;
    }

    public function refundTransaction(): void
    {
        $data = $this->form->getState();
        $apiKey = auth()->user()->merchant->api_key;

        $response = Http::withHeaders([
            'X-API-KEY' => $apiKey,
        ])->post(config('app.url') . '/api/refund-payment', $data);

        if ($response->successful()) {
            $json = $response->json();
            $uuid = $json['transactionUuid'] ?? 'No UUID';

            Notification::make()
                ->title($json['success'])
                ->body(<<<HTML
                        <div style="margin-top: 0.5rem;">
                            <strong>Transaction UUID:</strong><br>
                                {$uuid}       
                        </div>
                    HTML)
                ->success()
                ->persistent()
                ->send();
        } else {
            $status = $response->status();
            $errorMessage = $response->json('error') ?? $response->body();

            Notification::make()
                ->title("Error: {$status}")
                ->body("Failed to refund transaction: {$errorMessage}")
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
