<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected \Filament\Support\Enums\Width|string|null $maxContentWidth = 'full';

    protected function getHeaderActions(): array
    {
        $actions = [];

        // 依日期排序行程
        $actions[] = Actions\Action::make('sortTripsByDate')
            ->label('依日期排序')
            ->icon('heroicon-o-arrows-up-down')
            ->color('gray')
            ->visible(fn () => ! $this->record->isConfirmed())
            ->action(function () {
                $trips = $this->data['invoiceTrips'] ?? [];

                uasort($trips, fn (array $a, array $b) => ($a['date'] ?? '') <=> ($b['date'] ?? ''));

                $this->data['invoiceTrips'] = $trips;
            });

        // 確認請款單 — 只在草稿時顯示
        $actions[] = Actions\Action::make('confirm')
            ->label('確認請款單')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('確認請款單')
            ->modalDescription('確認後請款單將被鎖定，如需修改需先解除鎖定。確定要確認嗎？')
            ->visible(fn () => ! $this->record->isConfirmed())
            ->action(function () {
                $this->record->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);
                $this->refreshFormData(['status', 'confirmed_at']);
                Notification::make()
                    ->title('請款單已確認')
                    ->success()
                    ->send();
            });

        // 解除鎖定 — 只在已確認時顯示
        $actions[] = Actions\Action::make('unlock')
            ->label('解除鎖定')
            ->icon('heroicon-o-lock-open')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('解除鎖定')
            ->modalDescription('解除鎖定後請款單將恢復為草稿，可以繼續編輯。確定要解除嗎？')
            ->visible(fn () => $this->record->isConfirmed())
            ->action(function () {
                $this->record->update([
                    'status' => 'draft',
                    'confirmed_at' => null,
                ]);
                $this->refreshFormData(['status', 'confirmed_at']);
                Notification::make()
                    ->title('請款單已解除鎖定')
                    ->warning()
                    ->send();
            });

        // 列印
        $actions[] = Actions\Action::make('print')
            ->label('列印')
            ->icon('heroicon-o-printer')
            ->url(fn () => route('invoices.print', $this->record))
            ->openUrlInNewTab();

        $actions[] = Actions\DeleteAction::make();

        return $actions;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->recalculateTotal();
    }
}
