<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BaseLogin;

/**
 * The back office accepts a username or an email address.
 * Only accounts with a back-office role can proceed past this screen.
 */
class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email or username')
            ->placeholder('admin')
            ->required()
            ->autocomplete('username')
            ->autofocus();
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $identifier = trim((string) ($data['email'] ?? ''));

        if ($identifier !== '' && ! str_contains($identifier, '@')) {
            $identifier = User::query()->where('username', $identifier)->value('email') ?? $identifier;
        }

        return [
            'email' => $identifier,
            'password' => $data['password'],
        ];
    }

    public function getHeading(): string
    {
        return 'Sign in to the console';
    }
}
