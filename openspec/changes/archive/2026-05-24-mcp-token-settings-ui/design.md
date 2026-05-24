## Context

Settings 頁面（`app/Filament/Pages/Settings.php`）目前管理發票抬頭與附加費用，是系統唯一的設定入口。MCP AI Token 目前只能透過 Tinker 命令列建立，且 abilities 無法在後台調整。本次在既有頁面新增一個 Section，利用 Sanctum 已有的 `personal_access_tokens` 表與 `PersonalAccessToken` model，不引入任何新依賴。

## Goals / Non-Goals

**Goals:**
- Settings 頁面新增 MCP AI Token 管理 Section
- CheckboxList 顯示 9 個 Ability，與既有「儲存設定」按鈕整合
- 「重置 Token」按鈕（有確認對話框），重置後一次性顯示明文
- 若無 Token，首次儲存時自動建立並顯示明文

**Non-Goals:**
- 不支援多 Token 或 per-user Token
- 不新增資料表或 Eloquent Model
- 不新增獨立 Filament Resource

## Decisions

### 1. Token 以固定名稱識別
用 `personal_access_tokens.name = 'mcp-system-token'` 定位系統唯一 Token，無需在 `settings` 表另存 ID。刪除再建即為「重置」。

**備選方案**: 在 `settings` 表儲存 `mcp_token_id` → 多一層間接且無額外好處。

### 2. Abilities 整合到既有 form()
`CheckboxList::make('mcp_abilities')` 加入現有 `$this->data`，`mount()` 從 Token 讀取，`save()` 寫回 Token。Token 不存在時 `save()` 負責建立。

**備選方案**: 獨立第二個 form + 獨立存檔按鈕 → 多餘的 UX 複雜度。

### 3. 明文 Token 用獨立 Livewire property 顯示
`public ?string $newlyGeneratedToken = null`，不進 form state。`TextInput` 以 `visible(fn() => filled(...))` 條件顯示，頁面重整後自動消失。

**備選方案**: Notification body → 不方便複製長字串。

### 4. 「重置 Token」為 Section 內 Action
`Filament\Schemas\Components\Actions` + `Action::make('regenerateToken')->requiresConfirmation()`，呼叫 `regenerateToken()` Livewire method，刪除舊 Token、建立新 Token、設定 `$this->newlyGeneratedToken`。

## Risks / Trade-offs

- **Token 一次性顯示**：頁面刷新後明文消失，如果使用者沒複製需再次重置。這是標準 PAT 安全設計，屬預期行為。
- **Abilities 存放在 personal_access_tokens**：若 Sanctum 的 `abilities` 欄位結構未來變動，需同步調整。風險極低（Sanctum 已 stable）。
- **Settings.php 職責微增**：同一個頁面同時管理 key-value 設定與 Token，兩者耦合低，可接受。
