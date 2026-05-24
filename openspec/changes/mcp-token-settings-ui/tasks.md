## 1. Settings 頁面擴充

- [x] 1.1 在 `Settings.php` 新增 `public ?string $newlyGeneratedToken = null` Livewire property
- [x] 1.2 在 `mount()` 中查找 `mcp-system-token` Token，將其 abilities 載入 `$this->data['mcp_abilities']`
- [x] 1.3 在 `form()` 的 `Section::make('MCP AI Token 設定')` 中加入 `Placeholder` 顯示 Token 狀態（已建立 / 尚未建立 + last_used_at）
- [x] 1.4 在同一 Section 加入 `CheckboxList::make('mcp_abilities')` 列出 9 個 Ability 選項（含中文標籤）
- [x] 1.5 在同一 Section 加入條件顯示的 `TextInput::make('newlyGeneratedToken')`（`visible(fn() => filled($this->newlyGeneratedToken))`，readonly，helperText 提示立即複製）
- [x] 1.6 在同一 Section 加入 `Actions` + `Action::make('regenerateToken')`（danger 色、requiresConfirmation、呼叫 `regenerateToken()` method）

## 2. 儲存邏輯

- [x] 2.1 在 `save()` 中取得 `$data['mcp_abilities']`，查找 `mcp-system-token` Token
- [x] 2.2 若 Token 存在：更新其 `abilities` 欄位（JSON array）
- [x] 2.3 若 Token 不存在：以 `auth()->user()->createToken('mcp-system-token', $abilities)` 建立，並設定 `$this->newlyGeneratedToken`

## 3. 重置 Token 邏輯

- [x] 3.1 新增 `regenerateToken()` public method
- [x] 3.2 在方法中刪除現有 `mcp-system-token` Token（若存在）
- [x] 3.3 以目前 `$this->data['mcp_abilities']` 建立新 Token，設定 `$this->newlyGeneratedToken`
- [x] 3.4 發送成功 Notification

## 4. 測試

- [x] 4.1 建立 `tests/Feature/Settings/McpTokenSettingsTest.php`
- [x] 4.2 測試 Token 不存在時 `mount()` 正確載入空 abilities
- [x] 4.3 測試 Token 存在時 `mount()` 正確載入現有 abilities
- [x] 4.4 測試首次儲存建立新 Token 並回傳明文
- [x] 4.5 測試更新 abilities 儲存後 Token abilities 正確更新（不重建 Token）
- [x] 4.6 測試 `regenerateToken()` 刪除舊 Token 並建立新 Token，回傳新明文
- [x] 4.7 測試 Token 狀態 Placeholder 顯示正確（已建立 / 尚未建立）

## 5. 程式碼格式化

- [x] 5.1 執行 `vendor/bin/pint --dirty --format agent` 格式化異動的 PHP 檔案
