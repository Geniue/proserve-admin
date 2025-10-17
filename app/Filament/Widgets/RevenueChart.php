<?php

namespace App\Filament\Widgets;

use App\Models\ServiceBooking;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'Monthly Revenue';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $monthlyRevenue = ServiceBooking::where('payment_status', 'paid')
            ->select(
                DB::raw('EXTRACT(MONTH FROM created_at) as month'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $data = array_fill(0, 12, 0);
        
        foreach ($monthlyRevenue as $revenue) {
            $data[$revenue->month - 1] = (float) $revenue->revenue;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $data,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgb(75, 192, 192)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
