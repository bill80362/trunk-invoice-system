<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingInvoicesTable extends BaseWidget
{
    protected static ?string $heading = '待確認請款單';

    protected ?string $pollingInterval = null;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->where('status', 'draft')
                    ->with('client')
                    ->latest('updated_at')
            )
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('請款編號'),
                TextColumn::make('client.name')
                    ->label('客戶'),
                TextColumn::make('year')
                    ->label('年')
                    ->formatStateUsing(fn ($state, $record) => "{$record->year}/{$record->month}"),
                TextColumn::make('total_amount')
                    ->label('總金額')
                    ->money('TWD')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('最後更新')
                    ->since(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginated([5]);
    }
}
