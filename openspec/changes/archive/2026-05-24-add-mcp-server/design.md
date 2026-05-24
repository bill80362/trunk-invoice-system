## Context

系統目前以 Filament v5 管理介面操作資料，無程式化 API 供 AI Agent 使用。需要引入 MCP (Model Context Protocol) Server，讓 AI 工具可透過標準化的 Tool 呼叫方式安全存取系統資料。

現有技術棧：PHP 8.4 / Laravel 13 / Octane (RoadRunner) / Apache 反向代理。

## Goals / Non-Goals

**Goals:**
- 安裝 `laravel/mcp` 與 `laravel/sanctum`，提供 MCP HTTP endpoint
- 以 Sanctum Token Abilities 控制各 Tool 的讀寫權限
- 實作 Read / Write / Action 三類 Tool，涵蓋核心業務實體
- 所有 Write/Action Tools 內部強制執行業務規則（如 confirmed 唯讀）

**Non-Goals:**
- 不支援 SSE Streaming（僅 Streamable HTTP 純 JSON 模式）
- 不實作 OAuth 流程，僅使用 Personal Access Token
- 不修改現有 Filament 介面
- 不開放 User 模型的 CRUD 給 AI

## Decisions

### 1. MCP Transport：Streamable HTTP（純 JSON）

**決定**：使用 Streamable HTTP 純 POST/JSON 模式，不開啟 SSE Streaming。

**理由**：系統所有 Tool Call 皆為短暫操作（查詢、寫入），無需 streaming。純 JSON 模式與 Apache + Octane 完全相容，不需修改現有反向代理設定。

**捨棄**：HTTP+SSE 模式需要 Apache `flushpackets=on` 設定，且 RoadRunner worker 會被長連線佔用。

---

### 2. 授權：Sanctum Token Abilities（非 OAuth）

**決定**：使用 Laravel Sanctum Personal Access Token，搭配 Abilities（細粒度 scope）。

**理由**：
- 系統為內部使用，不需要 OAuth 授權流程
- Token Abilities 可精確控制每個 Tool 的存取（`invoices:read`、`trips:write` 等）
- Sanctum 已是 Laravel 生態系標準，整合成本低

**捨棄**：Laravel Passport（OAuth）複雜度過高；自訂 API Key 缺乏 ability 管理機制。

---

### 3. Tool 組織：依功能分類的獨立 PHP 類別

**決定**：每個 Tool 為 `app/Mcp/Tools/` 下的獨立類別，繼承共用基底。

**理由**：
- 單一職責，便於測試與維護
- 業務規則（`isConfirmed()` 檢查、`recalculateTotal()` 呼叫）封裝在各 Tool 內
- 與 `laravel/mcp` 的 Tool 註冊方式一致

---

### 4. Write Tools 的業務規則防護

**決定**：Write/Action Tools 不依賴前端驗證，在 Tool `handle()` 內部強制檢查。

**理由**：AI Agent 可能繞過參數驗證，業務規則（`confirmed` 唯讀、運費計算邏輯）必須在 Tool 層強制執行，不能只在 Filament 層防護。

## Risks / Trade-offs

| 風險 | 緩解措施 |
|---|---|
| Token 洩漏導致未授權操作 | Token 設定過期時間（30 天），寫入型 Token 單獨發行 |
| AI 發出大量查詢拖慢 DB | 初期僅開放 Read Tools，Write Tools 另行審核後開放 |
| `laravel/mcp` 套件升級破壞 Tool 介面 | Tool 基底類別封裝 MCP SDK 依賴，升級時只改一處 |

## Migration Plan

1. `composer require laravel/mcp laravel/sanctum`
2. 執行 `php artisan install:api` 建立 Sanctum migration
3. 在 `User` Model 加入 `HasApiTokens` trait
4. 建立 `app/Mcp/Tools/` 目錄與各 Tool 類別
5. 在 `MCP Server` 中註冊所有 Tools
6. 新增 `routes/api.php` MCP endpoint，套用 `auth:sanctum` middleware
7. 執行 migration，手動建立測試用 Token
8. Apache 設定不需修改

## Open Questions

- `laravel/mcp` v0 的 Tool 註冊 API 是否穩定？需確認後決定基底類別設計方式。
- 是否需要 Rate Limiting（`throttle` middleware）防止 AI 過度呼叫？
