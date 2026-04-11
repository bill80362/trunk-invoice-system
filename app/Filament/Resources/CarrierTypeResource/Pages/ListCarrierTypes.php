<?php

namespace App\Filament\Resources\CarrierTypeResource\Pages;

use App\Filament\Resources\CarrierTypeResource;
use Filament\Resources\Pages\ListRecords;

class ListCarrierTypes extends ListRecords
{
    protected static string $resource = CarrierTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
