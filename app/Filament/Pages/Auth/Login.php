<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Halaman masuk panel admin.
 *
 * Tampilan dua kolom: panel bergambar desa di kiri (filament.auth.aside,
 * dipasang lewat render hook di AdminPanelProvider) dan formulir di kanan.
 * Gayanya ada di resources/css/admin.css bagian "Halaman login".
 */
class Login extends BaseLogin
{
    public function getTitle(): string|Htmlable
    {
        return 'Masuk';
    }

    public function getHeading(): string|Htmlable
    {
        return 'Masuk ke panel desa';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Gunakan akun yang diberikan admin desa.';
    }

    /** @return array<string, mixed> */
    public function getExtraBodyAttributes(): array
    {
        return ['class' => 'lr-auth-page'];
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Alamat email')
            ->email()
            ->required()
            ->autocomplete('username')
            ->autofocus()
            ->placeholder('nama@email.com')
            ->prefixIcon('heroicon-m-envelope')
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Kata sandi')
            ->hint('Lupa? Hubungi admin desa')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->placeholder('••••••••')
            ->prefixIcon('heroicon-m-lock-closed')
            ->extraInputAttributes(['tabindex' => 2]);
    }

    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()->label('Tetap masuk di perangkat ini');
    }

    protected function getAuthenticateFormAction(): \Filament\Actions\Action
    {
        return parent::getAuthenticateFormAction()
            ->label('Masuk')
            ->icon('heroicon-m-arrow-right')
            ->iconPosition('after');
    }
}
