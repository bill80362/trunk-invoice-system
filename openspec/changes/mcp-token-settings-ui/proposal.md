## Why

目前 MCP AI Token 只能透過 Tinker 命令列建立與管理，技術門檻高且無法隨時調整 Token 的授權範圍。需要在後台提供圖形介面，讓管理員能夠透過 Settings 頁面直接管理系統唯一的 MCP AI Token 及其權限。

## What Changes

- 在「系統設定」頁面新增「MCP AI Token 設定」區塊
- 以 CheckboxList 顯示 9 個 Ability 的開關，與「儲存設定」按鈕整合，一併儲存
- 提供「重置 Token」按鈕（含確認對話框），重置後以明文顯示新 Token 一次
- 以 Placeholder 顯示 Token 目前狀態（已建立 / 尚未建立）及最後使用時間
- 若無 Token，首次「儲存設定」時自動建立並顯示明文

## Capabilities

### New Capabilities

- `mcp-token-management`: 在 Settings 頁面管理系統唯一 MCP AI Token 的建立、Abilities 更新與重置

### Modified Capabilities

- `system-settings`: Settings 頁面新增 MCP Token 區塊（UI 擴充，不影響既有 fee/issuer 設定邏輯）

## Impact

- `app/Filament/Pages/Settings.php`：新增 MCP Token Section、mount/save 邏輯、regenerateToken 方法
- `resources/views/filament/pages/settings.blade.php`：不需修改（form 自動渲染）
- 依賴：`Laravel\Sanctum\PersonalAccessToken` model（已安裝）
- Token 以固定名稱 `mcp-system-token` 識別，不新增資料表
