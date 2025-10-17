<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Service;
use App\Models\ServiceBooking;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalUsers = User::count();
        $usersThisMonth = User::whereMonth('created_at', now()->month)->count();
        
        $activeServices = Service::where('is_active', true)->count();
        $totalServices = Service::count();
        
        $pendingBookings = ServiceBooking::where('status', 'pending')->count();
        $completedBookings = ServiceBooking::where('status', 'completed')->count();
        
        $totalRevenue = ServiceBooking::where('payment_status', 'paid')
            ->sum('total_amount');
        $monthlyRevenue = ServiceBooking::where('payment_status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->sum('total_amount');
        
        return [
            Stat::make('Total Users', $totalUsers)
                ->description($usersThisMonth . ' new this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 12, 18, 15, 22, 28, $usersThisMonth]),
                
            Stat::make('Active Services', $activeServices)
                ->description($totalServices . ' total services')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('primary'),
                
            Stat::make('Pending Bookings', $pendingBookings)
                ->description($completedBookings . ' completed')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),
                
            Stat::make('Total Revenue', '$' . number_format($totalRevenue, 2))
                ->description('$' . number_format($monthlyRevenue, 2) . ' this month')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
}
