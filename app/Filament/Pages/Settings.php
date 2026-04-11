<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = '系統設定';

    protected static ?string $title = '系統設定';

    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'additional_stop_fee' => Setting::get('additional_stop_fee', '0'),
            'issuer_name' => Setting::get('issuer_name', ''),
            'issuer_address' => Setting::get('issuer_address', ''),
            'issuer_phone' => Setting::get('issuer_phone', ''),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('附加費用設定')
                    ->schema([
                        Forms\Components\TextInput::make('additional_stop_fee')
                            ->label('附加站點費用')
                            ->helperText('每增加一個附加目的地所加計的固定費用')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                    ]),
                Section::make('請款單抬頭預設值')
                    ->schema([
                        Forms\Components\TextInput::make('issuer_name')
                            ->label('公司名稱')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('issuer_address')
                            ->label('地址')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('issuer_phone')
                            ->label('電話')
                            ->tel()
                            ->maxLength(255),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('additional_stop_fee', $data['additional_stop_fee'] ?? '0');
        Setting::set('issuer_name', $data['issuer_name'] ?? '');
        Setting::set('issuer_address', $data['issuer_address'] ?? '');
        Setting::set('issuer_phone', $data['issuer_phone'] ?? '');

        Notification::make()
            ->title('設定已儲存')
            ->success()
            ->send();
    }
}
