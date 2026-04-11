<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FreightRateResource\Pages;
use App\Models\FreightRate;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FreightRateResource extends Resource
{
    protected static ?string $model = FreightRate::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static string | \UnitEnum | null $navigationGroup = '費率設定';

    protected static ?string $modelLabel = '費率表';

    protected static ?string $pluralModelLabel = '費率表';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('origin_id')
                    ->label('起點')
                    ->relationship('origin', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('destination_id')
                    ->label('目的地')
                    ->relationship('destination', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('carrier_type_id')
                    ->label('托運方式')
                    ->relationship('carrierType', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('base_price')
                    ->label('基礎運費')
                    ->numeric()
                    ->required()
                    ->prefix('$'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('origin.name')
                    ->label('起點')
                    ->searchable(),
                Tables\Columns\TextColumn::make('destination.name')
                    ->label('目的地')
                    ->searchable(),
                Tables\Columns\TextColumn::make('carrierType.name')
                    ->label('托運方式'),
                Tables\Columns\TextColumn::make('base_price')
                    ->label('基礎運費')
                    ->money('TWD')
                    ->sortable(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFreightRates::route('/'),
            'create' => Pages\CreateFreightRate::route('/create'),
            'edit' => Pages\EditFreightRate::route('/{record}/edit'),
        ];
    }
}
