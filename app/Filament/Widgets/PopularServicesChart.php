<?php

namespace App\Filament\Widgets;

use App\Models\Service;
use App\Models\ServiceBooking;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PopularServicesChart extends ChartWidget
{
    protected ?string $heading = 'Most Popular Services';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $popularServices = ServiceBooking::select('service_id', DB::raw('count(*) as total'))
            ->groupBy('service_id')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->with('service')
            ->get();
        
        $labels = [];
        $data = [];
        
        foreach ($popularServices as $booking) {
            if ($booking->service) {
                $labels[] = $booking->service->name;
                $data[] = $booking->total;
            }
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Bookings',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
