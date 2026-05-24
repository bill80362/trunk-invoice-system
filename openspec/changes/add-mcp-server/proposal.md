## Why

目前系統僅有 Filament 管理介面，AI Agent 無法透過程式化方式查詢或操作資料。透過實作 MCP (Model Context Protocol) Server，讓 AI 工具（如 Claude、Copilot）能安全地讀取請款單資料與執行業務操作，提升工作流程自動化能力。

## What Changes

- **新增** `laravel/mcp` 套件，提供 MCP HTTP endpoint
- **新增** Read Tools：查詢客戶、請款單、行程明細、費率、地點、司機、托運方式、系統設定
- **新增** Write Tools：建立請款單、新增/修改/刪除行程明細、修改費率、修改系統設定
- **新增** Action Tools：確認請款單、解除鎖定、計算預估運費、重新計算總金額
- **新增** Sanctum API Token 授權，以 Token Abilities 控制每個 Tool 的存取權限
- **新增** MCP 路由，掛載 `auth:sanctum` middleware 保護

## Capabilities

### New Capabilities

- `mcp-auth`: Sanctum API token 發行與 ability 管理，控制 AI Agent 對各工具的存取權限
- `mcp-read-tools`: 查詢類 MCP Tools（clients、invoices、trips、rates、locations、drivers、carrier-types、settings）
- `mcp-write-tools`: 修改類 MCP Tools（create invoice、add/update/delete trip、update rate、set setting）
- `mcp-action-tools`: 業務操作類 MCP Tools（confirm invoice、unlock invoice、calculate freight fee、recalculate total）

### Modified Capabilities

## Impact

- 新增 `laravel/mcp` 依賴（composer require）
- 新增 `laravel/sanctum` 依賴（composer require）
- 新增 `app/Mcp/Tools/` 目錄，存放所有 Tool 類別
- `routes/api.php`：新增 MCP endpoint，套用 `auth:sanctum` middleware
- `app/Models/User.php`：加入 `HasApiTokens` trait
- 新增 `database/migrations/` for personal_access_tokens table（Sanctum）
- 不影響現有 Filament 介面與業務邏輯
