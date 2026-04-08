<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class OtpRequestsPage extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-device-phone-mobile';
    protected static string | UnitEnum | null $navigationGroup = 'Support';
    protected static ?string $title = 'OTP Requests';
    protected static ?string $slug = 'otp-requests';
    protected string $view = 'filament.pages.otp-requests';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public function getViewData(): array
    {
        return [
            'firebaseConfig' => [
                'projectId' => config('firebase.web.project_id'),
                'apiKey' => config('firebase.web.api_key'),
            ],
        ];
    }
}
