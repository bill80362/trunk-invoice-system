<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers\InvoiceTripsRelationManager;
use App\Models\Invoice;
use App\Models\Setting;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = '請款管理';

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
                Forms\Components\Placeholder::make('total_amount_display')
                    ->label('總金額')
                    ->content(fn (?Invoice $record) => $record ? '$'.number_format((float) $record->total_amount, 2) : '$0.00')
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

    public static function getRelations(): array
    {
        return [
            InvoiceTripsRelationManager::class,
        ];
    }
}
