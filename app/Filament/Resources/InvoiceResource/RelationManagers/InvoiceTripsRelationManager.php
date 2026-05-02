<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use App\Models\FreightRate;
use App\Models\Setting;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoiceTripsRelationManager extends RelationManager
{
    protected static string $relationship = 'invoiceTrips';

    protected static ?string $title = '行程明細';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\DatePicker::make('date')
                    ->label('日期')
                    ->required()
                    ->default(now()),
                Forms\Components\Select::make('origin_id')
                    ->label('起點')
                    ->relationship('origin', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Set $set) => static::calculateFreightFee($get, $set)),
                Forms\Components\Select::make('carrier_type_id')
                    ->label('托運方式')
                    ->relationship('carrierType', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Set $set) => static::calculateFreightFee($get, $set)),
                Forms\Components\Select::make('driver_id')
                    ->label('司機')
                    ->relationship('driver', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Repeater::make('invoiceTripStops')
                    ->label('目的地')
                    ->relationship()
                    ->orderColumn('sequence')
                    ->simple(
                        Forms\Components\Select::make('location_id')
                            ->label('目的地')
                            ->relationship('location', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    )
                    ->defaultItems(1)
                    ->minItems(1)
                    ->reorderable()
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Set $set) => static::calculateFreightFee($get, $set))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('weight')
                    ->label('重量')
                    ->maxLength(255),
                Forms\Components\TextInput::make('freight_fee')
                    ->label('運費')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->prefix('$'),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->defaultSort('sequence')
            ->paginated([5, 10, 25, 50, 80, 100, 150, 200])
            ->columns([
                TextColumn::make('date')
                    ->label('日期')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('origin.name')
                    ->label('起點'),
                TextColumn::make('driver.name')
                    ->label('司機'),
                TextColumn::make('carrierType.name')
                    ->label('托運方式'),
                TextColumn::make('weight')
                    ->label('重量'),
                TextColumn::make('freight_fee')
                    ->label('運費')
                    ->money('TWD'),
            ])
            ->filters([])
            ->headerActions([
                CreateAction::make()
                    ->label('新增行程')
                    ->slideOver()
                    ->visible(fn () => ! $this->getOwnerRecord()->isConfirmed())
                    ->after(fn () => $this->getOwnerRecord()->recalculateTotal()),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->visible(fn () => ! $this->getOwnerRecord()->isConfirmed())
                    ->after(fn () => $this->getOwnerRecord()->recalculateTotal()),
                DeleteAction::make()
                    ->visible(fn () => ! $this->getOwnerRecord()->isConfirmed())
                    ->after(fn () => $this->getOwnerRecord()->recalculateTotal()),
            ])
            ->toolbarActions([]);
    }

    public static function calculateFreightFee(Get $get, Set $set): void
    {
        $originId = $get('origin_id');
        $carrierTypeId = $get('carrier_type_id');
        $stops = $get('invoiceTripStops') ?? [];

        $firstStop = collect($stops)->first();
        $firstDestinationId = null;

        if (is_array($firstStop)) {
            $firstDestinationId = $firstStop['location_id'] ?? null;
        }

        if (! $originId || ! $firstDestinationId || ! $carrierTypeId) {
            return;
        }

        $rate = FreightRate::where('origin_id', $originId)
            ->where('destination_id', $firstDestinationId)
            ->where('carrier_type_id', $carrierTypeId)
            ->first();

        $basePrice = $rate ? (float) $rate->base_price : 0;
        $extraStops = max(0, count($stops) - 1);
        $additionalFee = (float) Setting::get('additional_stop_fee', 0);

        $set('freight_fee', $basePrice + ($extraStops * $additionalFee));
    }
}
