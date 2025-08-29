<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    public function getRedirectUrl(): string
    {
        // Ambil dan hapus URL sebelumnya dari session
        return session()->pull('previous_url', filament()->getHomeUrl());
    }
}
