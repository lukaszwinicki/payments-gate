<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Illuminate\Support\Facades\Http;

class CreatePaymentLink extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.create-payment-link';
    protected static ?string $navigationLabel = 'Create payment link (API)';
    protected static ?string $title = 'New payment link via API';

    public float $amount;
    public string $currency;

    public string $expiresAt;
    public string $notificationUrl;
    public string $returnUrl;


    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('amount')->required(),
            Forms\Components\TextInput::make('currency')->required(),
            Forms\Components\DateTimePicker::make('expiresAt')
                ->required()
                ->displayFormat('Y-m-d H:i')
                ->timezone('Europe/Warsaw')
                ->native(false)
                ->seconds(false),
            Forms\Components\TextInput::make('notificationUrl')->required(),
            Forms\Components\TextInput::make('returnUrl')->required(),
        ];
    }

    protected function getFormModel(): string|\Illuminate\Database\Eloquent\Model|null
    {
        return null;
    }

    public function createPaymentLink(): void
    {
        $data = $this->form->getState();
        $apiKey = auth()->user()->merchant->api_key;

        $response = Http::withHeaders([
            'X-API-KEY' => $apiKey,
        ])->post(config('app.url') . '/api/create-payment-link', $data);

        if ($response->successful()) {
            $json = $response->json();
            $link = $json['paymentLink'] ?? null;

            Notification::make()
                ->title('Payment link created')
                ->body(<<<HTML
                        <div style="margin-top: 0.5rem;">
                            <strong>Payment Link:</strong><br>
                                {$link}       
                        </div>
                    HTML)
                ->success()
                ->persistent()
                ->when(
                    $link,
                    fn($notification) => $notification->actions([
                        Action::make('open')
                            ->label('Payment link')
                            ->button()
                            ->url($link)
                            ->openUrlInNewTab(),
                    ])
                )
                ->send();
        } else {
            $status = $response->status();
            $errorMessage = $response->json('error') ?? $response->body();

            if (is_array($errorMessage)) {
                $errorText = "Failed to create payment link:\n";
                foreach ($errorMessage as $fieldErrors) {
                    foreach ((array) $fieldErrors as $error) {
                        $errorText .= "{$error}\n";
                    }
                }
                $errorMessage = $errorText;
            } else {
                $errorMessage = "Failed to create payment link: {$errorMessage}";
            }

            Notification::make()
                ->title("Error: {$status}")
                ->body($errorMessage)
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
