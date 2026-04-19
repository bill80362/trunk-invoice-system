<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\InvoiceTrip;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $now = Carbon::now();
        $currentYear = $now->year;
        $currentMonth = $now->month;
        $lastMonth = $now->copy()->subMonth();

        $currentMonthInvoices = Invoice::where('year', $currentYear)
            ->where('month', $currentMonth)
            ->count();

        $lastMonthInvoices = Invoice::where('year', $lastMonth->year)
            ->where('month', $lastMonth->month)
            ->count();

        $pendingInvoices = Invoice::where('status', 'draft')->count();

        $currentMonthAmount = Invoice::where('year', $currentYear)
            ->where('month', $currentMonth)
            ->sum('total_amount');

        $lastMonthAmount = Invoice::where('year', $lastMonth->year)
            ->where('month', $lastMonth->month)
            ->sum('total_amount');

        $currentMonthTrips = InvoiceTrip::whereHas('invoice', function ($query) use ($currentYear, $currentMonth) {
            $query->where('year', $currentYear)->where('month', $currentMonth);
        })->count();

        $lastMonthTrips = InvoiceTrip::whereHas('invoice', function ($query) use ($lastMonth) {
            $query->where('year', $lastMonth->year)->where('month', $lastMonth->month);
        })->count();

        $invoiceDiff = $currentMonthInvoices - $lastMonthInvoices;
        $tripDiff = $currentMonthTrips - $lastMonthTrips;
        $amountDiff = $lastMonthAmount > 0
            ? round(($currentMonthAmount - $lastMonthAmount) / $lastMonthAmount * 100, 1)
            : null;

        return [
            Stat::make('本月請款單', $currentMonthInvoices)
                ->description($invoiceDiff >= 0 ? "+{$invoiceDiff} vs 上月" : "{$invoiceDiff} vs 上月")
                ->descriptionIcon($invoiceDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($invoiceDiff >= 0 ? 'success' : 'danger'),

            Stat::make('待確認請款單', $pendingInvoices)
                ->description($pendingInvoices > 0 ? '需處理' : '已全部確認')
                ->descriptionIcon($pendingInvoices > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($pendingInvoices > 0 ? 'warning' : 'success'),

            Stat::make('本月總金額', '$'.number_format($currentMonthAmount))
                ->description($amountDiff !== null ? ($amountDiff >= 0 ? "+{$amountDiff}%" : "{$amountDiff}%").' vs 上月' : '無上月資料')
                ->descriptionIcon($amountDiff === null || $amountDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($amountDiff === null || $amountDiff >= 0 ? 'success' : 'danger'),

            Stat::make('本月趟次', $currentMonthTrips)
                ->description($tripDiff >= 0 ? "+{$tripDiff} vs 上月" : "{$tripDiff} vs 上月")
                ->descriptionIcon($tripDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($tripDiff >= 0 ? 'success' : 'danger'),
        ];
    }
}
