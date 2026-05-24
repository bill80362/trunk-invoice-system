## ADDED Requirements

### Requirement: 顯示 MCP Token 狀態
Settings 頁面 SHALL 在「MCP AI Token 設定」Section 中顯示目前 Token 的狀態，包含是否已建立，以及最後使用時間（若存在）。

#### Scenario: Token 已存在
- **WHEN** 管理員開啟 Settings 頁面，且 `personal_access_tokens` 中存在名稱為 `mcp-system-token` 的記錄
- **THEN** 頁面顯示「已建立」狀態與 `last_used_at`（若為 null 則顯示「從未使用」）

#### Scenario: Token 不存在
- **WHEN** 管理員開啟 Settings 頁面，且不存在名稱為 `mcp-system-token` 的 Token
- **THEN** 頁面顯示「尚未建立」狀態

### Requirement: 管理 MCP Token Abilities
Settings 頁面 SHALL 提供 CheckboxList，允許管理員勾選或取消勾選以下 9 個 Ability，並與「儲存設定」按鈕整合。

可用 Abilities：`clients:read`、`invoices:read`、`invoices:write`、`trips:read`、`trips:write`、`rates:read`、`rates:write`、`settings:read`、`settings:write`

#### Scenario: 載入現有 Token Abilities
- **WHEN** 管理員開啟 Settings 頁面，且 Token 已存在
- **THEN** CheckboxList 的勾選狀態 SHALL 與 Token 目前的 `abilities` JSON 一致

#### Scenario: 儲存 Abilities 更新
- **WHEN** 管理員修改 CheckboxList 勾選並點擊「儲存設定」，且 Token 已存在
- **THEN** Token 的 `abilities` SHALL 更新為新的勾選清單，頁面顯示儲存成功通知

#### Scenario: 首次建立 Token
- **WHEN** 管理員點擊「儲存設定」，且目前不存在 `mcp-system-token` Token
- **THEN** 系統 SHALL 以選取的 Abilities 建立新 Token（歸屬於目前登入的管理員）
- **THEN** 頁面 SHALL 顯示新 Token 明文（僅此一次）

### Requirement: 一次性顯示 Token 明文
系統 SHALL 在 Token 建立或重置後，在頁面上顯示 Token 明文一次，提示使用者立即複製。頁面重整後明文 SHALL 消失。

#### Scenario: 顯示新 Token
- **WHEN** Token 被建立或重置
- **THEN** 頁面 SHALL 顯示可唯讀的 TextInput，內含完整明文 Token 值，並附帶複製說明提示

#### Scenario: 刷新後消失
- **WHEN** 頁面被重新整理或重新進入
- **THEN** Token 明文 SHALL 不再顯示

### Requirement: 重置 MCP Token
Settings 頁面 SHALL 提供「重置 Token」按鈕，讓管理員在忘記 Token 時產生新 Token。

#### Scenario: 重置確認流程
- **WHEN** 管理員點擊「重置 Token」
- **THEN** 系統 SHALL 顯示確認對話框，說明舊 Token 將立即失效

#### Scenario: 確認重置
- **WHEN** 管理員在確認對話框中確認
- **THEN** 舊 `mcp-system-token` Token SHALL 被刪除
- **THEN** 系統 SHALL 以當前 CheckboxList 的 Abilities 建立新 Token
- **THEN** 頁面 SHALL 顯示新 Token 明文（僅此一次）

#### Scenario: 取消重置
- **WHEN** 管理員在確認對話框中取消
- **THEN** 舊 Token SHALL 保持不變
