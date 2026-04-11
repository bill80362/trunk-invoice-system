<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarrierTypeResource\Pages;
use App\Models\CarrierType;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CarrierTypeResource extends Resource
{
    protected static ?string $model = CarrierType::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';

    protected static string | \UnitEnum | null $navigationGroup = '基礎資料';

    protected static ?string $modelLabel = '托運方式';

    protected static ?string $pluralModelLabel = '托運方式';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('名稱')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('名稱')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('建立時間')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make()
                    ->before(function (CarrierType $record) {
                        if ($record->invoiceTrips()->exists() || $record->freightRates()->exists()) {
                            throw new \Exception('此托運方式已被引用，無法刪除');
                        }
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarrierTypes::route('/'),
            'create' => Pages\CreateCarrierType::route('/create'),
            'edit' => Pages\EditCarrierType::route('/{record}/edit'),
        ];
    }
}
