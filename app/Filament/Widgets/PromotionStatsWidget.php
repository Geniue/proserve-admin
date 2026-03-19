<?php

namespace App\Filament\Widgets;

use App\Models\Offer;
use App\Models\PromoCode;
use App\Models\PromoCodeRedemption;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PromotionStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $activeOffers = Offer::active()->count();
        $totalOffers = Offer::count();

        $activePromoCodes = PromoCode::active()->count();
        $totalPromoCodes = PromoCode::count();

        $todayRedemptions = PromoCodeRedemption::today()->count();
        $monthRedemptions = PromoCodeRedemption::thisMonth()->count();

        $monthDiscount = PromoCodeRedemption::thisMonth()->sum('discount_applied');

        return [
            Stat::make('Active Offers', $activeOffers)
                ->description($totalOffers . ' total offers')
                ->descriptionIcon('heroicon-m-gift')
                ->color('success'),

            Stat::make('Active Promo Codes', $activePromoCodes)
                ->description($totalPromoCodes . ' total codes')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('primary'),

            Stat::make("Today's Redemptions", $todayRedemptions)
                ->description($monthRedemptions . ' this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),

            Stat::make('Month Discounts', number_format($monthDiscount, 2) . ' SAR')
                ->description('Total discounts given')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('danger'),
        ];
    }
}
