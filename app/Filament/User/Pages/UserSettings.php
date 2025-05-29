<?php

namespace App\Filament\User\Pages;

use Filament\Forms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class UserSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationLabel = 'Settings';
    protected static ?string $navigationGroup = 'Profile';

    protected static string $view = 'filament.pages.user-settings';

    public $name;
    public $email;
    public $password;
    public $password_confirmation;

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->label('Full name')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('password')
                ->label('Password')
                ->password()
                ->minLength(8)
                ->same('password_confirmation')
                ->dehydrateStateUsing(fn ($state) => $state ? Hash::make($state) : null)
                ->nullable(),

            Forms\Components\TextInput::make('password_confirmation')
                ->label('Confirm password')
                ->password()
                ->nullable(),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $user = Auth::user();

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        Notification::make()
        ->title('Saved')
        ->success()
        ->send();
    }
}