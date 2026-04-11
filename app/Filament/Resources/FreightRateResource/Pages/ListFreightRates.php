<?php

namespace App\Filament\Resources\FreightRateResource\Pages;

use App\Filament\Resources\FreightRateResource;
use Filament\Resources\Pages\ListRecords;

class ListFreightRates extends ListRecords
{
    protected static string $resource = FreightRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
