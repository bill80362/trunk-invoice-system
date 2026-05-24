## ADDED Requirements

### Requirement: Token 發行
系統 SHALL 允許管理員透過 Artisan tinker 或程式碼為 User 發行 Personal Access Token，並指定 Abilities。

支援的 Abilities：
- `invoices:read`、`invoices:write`
- `trips:read`、`trips:write`
- `rates:read`、`rates:write`
- `settings:read`、`settings:write`
- `clients:read`
- `*`（全部）

#### Scenario: 發行 Read-only Token
- **WHEN** 管理員呼叫 `$user->createToken('ai-readonly', ['invoices:read', 'trips:read', 'clients:read'])`
- **THEN** 系統返回包含 `plainTextToken` 的 Token 物件

#### Scenario: 發行限時 Token
- **WHEN** 管理員指定過期時間 `now()->addDays(30)` 作為第三參數
- **THEN** Token 在 30 天後失效，MCP endpoint 返回 401

### Requirement: MCP Endpoint 授權保護
MCP endpoint SHALL 要求有效的 Sanctum Bearer Token，未授權請求 MUST 返回 401。

#### Scenario: 有效 Token 存取
- **WHEN** 請求 Header 包含 `Authorization: Bearer {valid_token}`
- **THEN** 系統允許請求通過並執行對應 Tool

#### Scenario: 無效 Token 存取
- **WHEN** 請求不含 Token 或 Token 無效
- **THEN** 系統返回 HTTP 401 Unauthorized

### Requirement: Tool 層 Ability 檢查
Write/Action 類 Tool SHALL 在執行前驗證 Token 具備對應的 Ability，不符合時 MUST 返回 403 錯誤。

#### Scenario: Token 缺少必要 Ability
- **WHEN** 持有 `invoices:read` Token 的 Agent 呼叫需要 `trips:write` 的 Tool
- **THEN** Tool 返回 403 錯誤，不執行任何寫入操作
