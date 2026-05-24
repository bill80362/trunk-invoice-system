<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Laravel\Sanctum\PersonalAccessToken;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = '系統設定';

    protected static ?string $title = '系統設定';

    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    private const MCP_TOKEN_NAME = 'mcp-system-token';

    public function mount(): void
    {
        $mcpToken = $this->findMcpToken();

        $this->form->fill([
            'additional_stop_fee' => Setting::get('additional_stop_fee', '0'),
            'issuer_name' => Setting::get('issuer_name', ''),
            'issuer_address' => Setting::get('issuer_address', ''),
            'issuer_phone' => Setting::get('issuer_phone', ''),
            'mcp_abilities' => $mcpToken ? ($mcpToken->abilities ?? []) : [],
            'newly_generated_token' => null,
        ]);
    }

    private function findMcpToken(): ?PersonalAccessToken
    {
        return auth()->user()->tokens()
            ->where('name', self::MCP_TOKEN_NAME)
            ->first();
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
                Section::make('MCP AI Token 設定')
                    ->description('管理供 AI 助理（如 Claude）使用的存取 Token 與授權範圍')
                    ->schema([
                        Forms\Components\Placeholder::make('token_status')
                            ->label('Token 狀態')
                            ->content(function (): string {
                                $token = $this->findMcpToken();
                                if (! $token) {
                                    return '尚未建立';
                                }
                                $lastUsed = $token->last_used_at
                                    ? '最後使用：'.$token->last_used_at->diffForHumans()
                                    : '從未使用';

                                return '已建立（'.$lastUsed.'）';
                            }),
                        Forms\Components\CheckboxList::make('mcp_abilities')
                            ->label('授權範圍')
                            ->options([
                                'clients:read' => '客戶資料：讀取',
                                'invoices:read' => '請款單：讀取',
                                'invoices:write' => '請款單：建立 / 確認 / 解鎖',
                                'trips:read' => '行程明細：讀取',
                                'trips:write' => '行程明細：新增 / 修改 / 刪除',
                                'rates:read' => '費率表：讀取',
                                'rates:write' => '費率表：修改',
                                'settings:read' => '系統設定：讀取',
                                'settings:write' => '系統設定：修改',
                            ])
                            ->bulkToggleable()
                            ->columns(2),
                        Forms\Components\TextInput::make('newly_generated_token')
                            ->label('新 Token（請立即複製，離開後將無法再次查看）')
                            ->helperText('此 Token 只顯示一次，請立即複製並貼到 Claude Desktop 或其他 AI 工具的設定中。')
                            ->readOnly()
                            ->visible(fn (): bool => filled($this->data['newly_generated_token'] ?? null))
                            ->extraAttributes(['class' => 'font-mono text-sm']),
                    ])
                    ->footerActions([
                        Action::make('regenerateToken')
                            ->label('重置 Token')
                            ->icon('heroicon-o-arrow-path')
                            ->color('danger')
                            ->requiresConfirmation()
                            ->modalHeading('重置 MCP Token')
                            ->modalDescription('重置後，目前的 Token 將立即失效。所有使用此 Token 的 AI 工具（如 Claude Desktop）都需要重新設定新 Token。確定要繼續嗎？')
                            ->action(fn () => $this->regenerateToken()),
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

        $abilities = $data['mcp_abilities'] ?? [];
        $token = $this->findMcpToken();

        if ($token) {
            $token->update(['abilities' => $abilities]);
        } else {
            $newToken = auth()->user()->createToken(self::MCP_TOKEN_NAME, $abilities);
            $this->data['newly_generated_token'] = $newToken->plainTextToken;
        }

        Notification::make()
            ->title('設定已儲存')
            ->success()
            ->send();
    }

    public function regenerateToken(): void
    {
        $data = $this->form->getState();
        $abilities = $data['mcp_abilities'] ?? [];

        auth()->user()->tokens()
            ->where('name', self::MCP_TOKEN_NAME)
            ->delete();

        $newToken = auth()->user()->createToken(self::MCP_TOKEN_NAME, $abilities);
        $this->data['newly_generated_token'] = $newToken->plainTextToken;

        Notification::make()
            ->title('Token 已重置')
            ->body('新 Token 已建立，請立即複製儲存。')
            ->success()
            ->send();
    }
}
