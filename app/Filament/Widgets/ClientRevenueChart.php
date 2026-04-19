<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ClientRevenueChart extends ChartWidget
{
    protected ?string $heading = '本月客戶請款排行';

    protected ?string $pollingInterval = null;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $now = Carbon::now();

        $clients = Invoice::where('year', $now->year)
            ->where('month', $now->month)
            ->with('client')
            ->get()
            ->groupBy('client_id')
            ->map(fn ($invoices) => [
                'name' => $invoices->first()->client?->name ?? '未知',
                'total' => (float) $invoices->sum('total_amount'),
            ])
            ->sortByDesc('total')
            ->take(10)
            ->values();

        return [
            'datasets' => [
                [
                    'label' => '請款金額',
                    'data' => $clients->pluck('total')->toArray(),
                    'backgroundColor' => [
                        '#f59e0b', '#ef4444', '#3b82f6', '#10b981', '#8b5cf6',
                        '#f97316', '#06b6d4', '#ec4899', '#84cc16', '#6366f1',
                    ],
                ],
            ],
            'labels' => $clients->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
