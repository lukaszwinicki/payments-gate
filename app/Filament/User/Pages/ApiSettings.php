<?php

namespace App\Filament\User\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;

class ApiSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static string $view = 'filament.user.pages.api-settings';
    protected static ?string $navigationGroup = 'Profile';
    protected static ?string $navigationLabel = ' Access Keys';
    protected static ?string $title = 'Access Keys';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = [
            'api_key' => auth()->user()->merchant->api_key,
            'secret_key' => auth()->user()->merchant->secret_key,
        ];

        $this->form->fill($this->data);
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    protected function getFormSchema(): array
    {
        return [
            Card::make()
                ->schema([
                    TextInput::make('api_key')
                        ->label('API Key')
                        ->disabled()
                        ->columnSpanFull(),

                    TextInput::make('secret_key')
                        ->label('Secret Key')
                        ->disabled()
                        ->columnSpanFull(),
                ])
                ->columns(1),
        ];
    }

    protected function getFormModel(): string
    {
        return 'data';
    }
}
