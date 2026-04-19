<?php

namespace App\Filament\Widgets;

use App\Models\InvoiceTrip;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class DriverPerformanceChart extends ChartWidget
{
    protected ?string $heading = '本月司機績效';

    protected ?string $pollingInterval = null;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $now = Carbon::now();

        $drivers = InvoiceTrip::whereHas('invoice', function ($query) use ($now) {
            $query->where('year', $now->year)->where('month', $now->month);
        })
            ->with('driver')
            ->get()
            ->groupBy('driver_id')
            ->map(fn ($trips) => [
                'name' => $trips->first()->driver?->name ?? '未知',
                'trips' => $trips->count(),
                'revenue' => (float) $trips->sum('freight_fee'),
            ])
            ->sortByDesc('trips')
            ->take(10)
            ->values();

        return [
            'datasets' => [
                [
                    'label' => '趟次數',
                    'data' => $drivers->pluck('trips')->toArray(),
                    'backgroundColor' => '#3b82f6',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => '運費金額',
                    'data' => $drivers->pluck('revenue')->toArray(),
                    'backgroundColor' => '#f59e0b',
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $drivers->pluck('name')->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => '趟次數',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => '運費金額',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
