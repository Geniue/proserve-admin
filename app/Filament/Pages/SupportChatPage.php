<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class SupportChatPage extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static string | UnitEnum | null $navigationGroup = 'Support';
    protected static ?string $title = 'Live Chat';
    protected static ?string $slug = 'support-chat';
    protected string $view = 'filament.pages.support-chat';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public function getViewData(): array
    {
        return [
            'firebaseConfig' => [
                'projectId' => config('firebase.web.project_id'),
                'apiKey' => config('firebase.web.api_key'),
            ],
            'adminName' => auth()->user()?->name ?? 'Support',
        ];
    }
}
