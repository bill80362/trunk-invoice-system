<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\FreightRate;
use App\Models\Invoice;
use App\Models\Setting;
use Filament\Forms;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string | \UnitEnum | null $navigationGroup = '請款管理';

    protected static ?string $modelLabel = '請款單';

    protected static ?string $pluralModelLabel = '請款單';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('請款單資訊')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('貨主')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),
                        Forms\Components\TextInput::make('year')
                            ->label('年份')
                            ->numeric()
                            ->required()
                            ->default(now()->year)
                            ->minValue(2000)
                            ->maxValue(2100)
                            ->live(),
                        Forms\Components\TextInput::make('month')
                            ->label('月份')
                            ->numeric()
                            ->required()
                            ->default(now()->month)
                            ->minValue(1)
                            ->maxValue(12)
                            ->live()
                            ->rules([
                                fn (Get $get, ?Invoice $record) => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                    $clientId = $get('client_id');
                                    $year = $get('year');
                                    $month = $value;

                                    if (! $clientId || ! $year || ! $month) {
                                        return;
                                    }

                                    $exists = Invoice::where('client_id', $clientId)
                                        ->where('year', $year)
                                        ->where('month', $month)
                                        ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                        ->exists();

                                    if ($exists) {
                                        $fail('此貨主在該年月已有請款單，不可重複建立。');
                                    }
                                },
                            ]),
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('單號')
                            ->maxLength(255),
                    ])->columns(4)
                    ->collapsed(fn (?Invoice $record) => $record !== null)
                    ->collapsible(),
                Section::make('請款方資訊')
                    ->schema([
                        Forms\Components\TextInput::make('issuer_name')
                            ->label('公司名稱')
                            ->default(fn () => Setting::get('issuer_name', ''))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('issuer_address')
                            ->label('地址')
                            ->default(fn () => Setting::get('issuer_address', ''))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('issuer_phone')
                            ->label('電話')
                            ->default(fn () => Setting::get('issuer_phone', ''))
                            ->maxLength(255),
                    ])->columns(3)
                    ->collapsed(fn (?Invoice $record) => $record !== null)
                    ->collapsible(),
                Forms\Components\Repeater::make('invoiceTrips')
                    ->label('行程明細')
                    ->relationship()
                    ->orderColumn('sequence')
                    ->reorderable()
                    ->defaultItems(0)
                    ->schema([
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
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        // 需要往上層找到 trip 層級的 get/set
                                    }),
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
                    ->columns(3)
                    ->columnSpanFull()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string =>
                        ($state['date'] ?? '') . ' ' .
                        (isset($state['origin_id']) ? \App\Models\Location::find($state['origin_id'])?->name ?? '' : '')
                    )
                    ->visible(fn (?Invoice $record) => $record !== null),
                Forms\Components\Placeholder::make('total_amount_display')
                    ->label('總金額')
                    ->content(fn (?Invoice $record) => $record ? '$' . number_format((float) $record->total_amount, 2) : '$0.00')
                    ->visible(fn (?Invoice $record) => $record !== null),
            ])
            ->disabled(fn (?Invoice $record) => $record?->isConfirmed() ?? false);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('貨主')
                    ->searchable(),
                Tables\Columns\TextColumn::make('year')
                    ->label('年份')
                    ->sortable(),
                Tables\Columns\TextColumn::make('month')
                    ->label('月份')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('單號'),
                Tables\Columns\TextColumn::make('status')
                    ->label('狀態')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'draft' => '草稿',
                        'confirmed' => '已確認',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'draft' => 'warning',
                        'confirmed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('總金額')
                    ->money('TWD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('建立時間')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function calculateFreightFee(Get $get, Set $set): void
    {
        $originId = $get('origin_id');
        $carrierTypeId = $get('carrier_type_id');
        $stops = $get('invoiceTripStops') ?? [];

        // Get first destination
        $firstStop = collect($stops)->first();
        $firstDestinationId = null;

        if (is_array($firstStop)) {
            // Simple repeater stores location_id directly
            $firstDestinationId = $firstStop['location_id'] ?? $firstStop ?? null;
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

        $totalFee = $basePrice + ($extraStops * $additionalFee);

        $set('freight_fee', $totalFee);
    }
}
