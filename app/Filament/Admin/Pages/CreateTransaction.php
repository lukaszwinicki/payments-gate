<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Illuminate\Support\Facades\Http;

class CreateTransaction extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.create-transaction';
    protected static ?string $navigationLabel = 'Create transaction (API)';
    protected static ?string $title = 'New transaction via API';

    public $amount;
    public $email;
    public $name;
    public $currency;
    public $paymentMethod;
    public $notificationUrl;
    public $returnUrl;

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('amount')->required(),
            Forms\Components\TextInput::make('email')->required(),
            Forms\Components\TextInput::make('name')->label('Full name')->required(),
            Forms\Components\TextInput::make('currency')->required(),
            Forms\Components\Select::make('paymentMethod')
                ->options([
                    'TPAY' => 'TPAY',
                    'PAYNOW' => 'PAYNOW',
                    'NODA' => 'NODA',
                ])
                ->required(),
            Forms\Components\TextInput::make('notificationUrl')->required(),
            Forms\Components\TextInput::make('returnUrl')->required(),
        ];
    }

    protected function getFormModel(): string|\Illuminate\Database\Eloquent\Model|null
    {
        return null;
    }

    public function createTransaction(): void
    {
        $data = $this->form->getState();
        $apiKey = auth()->user()->merchant->api_key;

        $response = Http::withHeaders([
            'X-API-KEY' => $apiKey,
        ])->post(config('app.url') . '/api/create-transaction', $data);

        if ($response->successful()) {
            $json = $response->json();
            $link = $json['link'] ?? null;
            $uuid = $json['transactionUuid'] ?? 'No UUID';

            Notification::make()
                ->title('Transaction created')
                ->body(<<<HTML
                        <div style="margin-top: 0.5rem;">
                            <strong>Transaction UUID:</strong><br>
                                {$uuid}       
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
                $errorText = "Failed to create transaction:\n";
                foreach ($errorMessage as $fieldErrors) {
                    foreach ((array) $fieldErrors as $error) {
                        $errorText .= "{$error}\n";
                    }
                }
                $errorMessage = $errorText;
            } else {
                $errorMessage = "Failed to create transaction: {$errorMessage}";
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
