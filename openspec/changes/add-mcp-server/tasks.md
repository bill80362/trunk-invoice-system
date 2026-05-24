## 1. 安裝依賴套件

- [x] 1.1 執行 `composer require laravel/mcp` 安裝 MCP Server 套件
- [x] 1.2 執行 `php artisan install:api` 安裝 Sanctum 並建立 `personal_access_tokens` migration
- [x] 1.3 執行 `php artisan migrate` 建立 personal_access_tokens 資料表
- [x] 1.4 在 `app/Models/User.php` 加入 `use Laravel\Sanctum\HasApiTokens` trait

## 2. MCP Server 基礎設定

- [x] 2.1 建立 `app/Mcp/Servers/InvoiceServer.php` MCP Server 類別
- [x] 2.2 在 `routes/ai.php` 新增 MCP endpoint，套用 `auth:sanctum` middleware
- [x] 2.3 確認 `config/sanctum.php` 的 `expiration` 設定合理（43200 分鐘 = 30 天）
- [x] 2.4 建立 `app/Mcp/Tools/` 目錄結構

## 3. Read Tools

- [x] 3.1 建立 `app/Mcp/Tools/ListClientsTool.php`，返回所有 Client，需 `clients:read` ability
- [x] 3.2 建立 `app/Mcp/Tools/ListInvoicesTool.php`，支援 `client_id`、`year`、`month`、`status` 篩選，需 `invoices:read` ability
- [x] 3.3 建立 `app/Mcp/Tools/GetInvoiceTool.php`，返回含 invoiceTrips 完整資料，需 `invoices:read` ability
- [x] 3.4 建立 `app/Mcp/Tools/ListInvoiceTripsTool.php`，依 sequence/date 排序，需 `trips:read` ability
- [x] 3.5 建立 `app/Mcp/Tools/ListFreightRatesTool.php`，支援 `origin_id`、`carrier_type_id` 篩選，需 `rates:read` ability
- [x] 3.6 建立 `app/Mcp/Tools/ListLocationsTool.php`，需 `clients:read` ability
- [x] 3.7 建立 `app/Mcp/Tools/ListDriversTool.php`，需 `clients:read` ability
- [x] 3.8 建立 `app/Mcp/Tools/ListCarrierTypesTool.php`，需 `clients:read` ability
- [x] 3.9 建立 `app/Mcp/Tools/GetSettingTool.php`，呼叫 `Setting::get()`，需 `settings:read` ability
- [x] 3.10 在 MCP Server 中註冊所有 Read Tools

## 4. Write Tools

- [x] 4.1 建立 `app/Mcp/Tools/CreateInvoiceTool.php`，自動帶入 issuer 預設值，需 `invoices:write` ability
- [x] 4.2 建立 `app/Mcp/Tools/AddInvoiceTripTool.php`，含 `isConfirmed()` 防護 + `recalculateTotal()` 回呼，需 `trips:write` ability
- [x] 4.3 建立 `app/Mcp/Tools/UpdateInvoiceTripTool.php`，含 `isConfirmed()` 防護 + `recalculateTotal()` 回呼，需 `trips:write` ability
- [x] 4.4 建立 `app/Mcp/Tools/DeleteInvoiceTripTool.php`，含 `isConfirmed()` 防護 + `recalculateTotal()` 回呼，需 `trips:write` ability
- [x] 4.5 建立 `app/Mcp/Tools/UpdateFreightRateTool.php`，需 `rates:write` ability
- [x] 4.6 建立 `app/Mcp/Tools/SetSettingTool.php`，呼叫 `Setting::set()`，需 `settings:write` ability
- [x] 4.7 在 MCP Server 中註冊所有 Write Tools

## 5. Action Tools

- [x] 5.1 建立 `app/Mcp/Tools/ConfirmInvoiceTool.php`，設定 `status = confirmed` + 記錄 `confirmed_at`，需 `invoices:write` ability
- [x] 5.2 建立 `app/Mcp/Tools/UnlockInvoiceTool.php`，設定 `status = draft` + 清除 `confirmed_at`，需 `invoices:write` ability
- [x] 5.3 建立 `app/Mcp/Tools/CalculateFreightFeeTool.php`，封裝 base_price + additional_stop_fee 計算邏輯，不寫入 DB，需 `rates:read` ability
- [x] 5.4 建立 `app/Mcp/Tools/RecalculateInvoiceTotalTool.php`，呼叫 `Invoice::recalculateTotal()`，需 `invoices:write` ability
- [x] 5.5 在 MCP Server 中註冊所有 Action Tools

## 6. 測試

- [x] 6.1 建立 `tests/Feature/Mcp/McpAuthTest.php`，測試無 Token 返回 401、Token 缺 ability 返回 MCP error
- [x] 6.2 建立 `tests/Feature/Mcp/McpReadToolsTest.php`，測試所有 Read Tools 的正常路徑與篩選邏輯
- [x] 6.3 建立 `tests/Feature/Mcp/McpWriteToolsTest.php`，測試 Write Tools 的正常路徑、confirmed 唯讀防護、recalculateTotal 回呼
- [x] 6.4 建立 `tests/Feature/Mcp/McpActionToolsTest.php`，測試 confirm/unlock 狀態切換、calculate_freight_fee 計算公式、錯誤狀態防護
- [x] 6.5 執行測試：40 個測試全部通過

## 7. 程式碼格式化

- [x] 7.1 執行 `vendor/bin/pint --format agent` 格式化所有 PHP 檔案
