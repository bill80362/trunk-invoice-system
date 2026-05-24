# 貨運請款單系統 MCP Server 使用說明

本文件說明如何讓 AI 助理（Claude、Cursor、GitHub Copilot 等）透過 MCP 協定操作貨運請款單系統。

---

## 目錄

1. [架構概覽](#架構概覽)
2. [步驟一：建立 API Token](#步驟一建立-api-token)
3. [步驟二：設定 AI 用戶端](#步驟二設定-ai-用戶端)
4. [Token Abilities 權限說明](#token-abilities-權限說明)
5. [工具清單（Tools）](#工具清單tools)
6. [常見操作流程範例](#常見操作流程範例)
7. [錯誤處理](#錯誤處理)

---

## 架構概覽

```
AI 助理（Claude / Cursor / Copilot）
         │ MCP 協定 (JSON-RPC 2.0 over HTTP)
         ▼
POST /mcp  ──── auth:sanctum ──── InvoiceServer
                                      ├── Read Tools    (查詢)
                                      ├── Write Tools   (建立 / 修改 / 刪除)
                                      └── Action Tools  (業務操作)
```

- **傳輸協定**：HTTP Streamable（Streamable HTTP Transport）
- **認證方式**：Laravel Sanctum Bearer Token，搭配 Token Abilities 授權

---

## 步驟一：建立 API Token

### 方法一：使用 Tinker（開發環境）

```bash
php artisan tinker
```

在 Tinker 中執行以下指令，依需求選擇 abilities：

```php
// 建立「唯讀」Token（AI 只能查詢，不能修改）
$user = \App\Models\User::first();
$token = $user->createToken('ai-readonly', [
    'clients:read',
    'invoices:read',
    'trips:read',
    'rates:read',
    'settings:read',
]);
echo $token->plainTextToken;

// 建立「完整操作」Token
$token = $user->createToken('ai-full', [
    'clients:read',
    'invoices:read', 'invoices:write',
    'trips:read',    'trips:write',
    'rates:read',    'rates:write',
    'settings:read', 'settings:write',
]);
echo $token->plainTextToken;
```

> 輸出格式：`1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`
> 請妥善保存，Token 只顯示一次。

### 方法二：建立 Artisan 指令（正式環境建議）

若需要定期輪換 Token，可建立 Artisan 指令：

```bash
php artisan make:command GenerateMcpToken
```

---

## 步驟二：設定 AI 用戶端

### Claude Desktop

編輯 `~/Library/Application Support/Claude/claude_desktop_config.json`：

```json
{
  "mcpServers": {
    "invoice-system": {
      "type": "http",
      "url": "http://localhost:8000/mcp",
      "headers": {
        "Authorization": "Bearer 1|your-token-here"
      }
    }
  }
}
```

重新啟動 Claude Desktop 後，工具列會出現 **Invoice Server** 的 19 個工具。

### Cursor

在 Cursor 設定中新增 MCP Server：

```json
{
  "mcpServers": {
    "invoice-system": {
      "url": "http://localhost:8000/mcp",
      "headers": {
        "Authorization": "Bearer 1|your-token-here"
      }
    }
  }
}
```

### 通用 HTTP MCP 用戶端

| 欄位 | 值 |
|---|---|
| URL | `http://your-domain/mcp` |
| Method | `POST` |
| Header | `Authorization: Bearer <token>` |
| Content-Type | `application/json` |
| Protocol | JSON-RPC 2.0 |

手動呼叫範例：

```bash
curl -X POST http://localhost:8000/mcp \
  -H "Authorization: Bearer 1|your-token" \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "id": "1",
    "method": "tools/call",
    "params": {
      "name": "list-clients-tool",
      "arguments": {}
    }
  }'
```

---

## Token Abilities 權限說明

| Ability | 允許操作 |
|---|---|
| `clients:read` | 查詢客戶、地點、司機、托運方式 |
| `invoices:read` | 查詢請款單清單、請款單詳情 |
| `invoices:write` | 建立請款單、確認、解除鎖定、重算總金額 |
| `trips:read` | 查詢行程明細、試算運費 |
| `trips:write` | 新增、修改、刪除行程明細 |
| `rates:read` | 查詢費率表、試算運費 |
| `rates:write` | 修改費率 |
| `settings:read` | 讀取系統設定 |
| `settings:write` | 修改系統設定 |

建議依照 AI 用途分配最小必要權限：

| 使用情境 | 建議 Abilities |
|---|---|
| 報表查詢 AI | `clients:read`, `invoices:read`, `trips:read`, `rates:read` |
| 請款單建立助理 | 上述全部 + `invoices:write`, `trips:write` |
| 系統管理 AI | 所有 abilities |

---

## 工具清單（Tools）

### Read Tools — 查詢資料

#### `list-clients-tool`
列出所有客戶。需要：`clients:read`

| 參數 | 說明 |
|---|---|
| （無） | |

回傳：`[{ id, name, contact, phone, address }, ...]`

---

#### `list-invoices-tool`
查詢請款單清單，支援多條件篩選。需要：`invoices:read`

| 參數 | 型別 | 說明 |
|---|---|---|
| `client_id` | integer（選填） | 篩選特定客戶 |
| `year` | integer（選填） | 篩選年份 |
| `month` | integer（選填） | 篩選月份 |
| `status` | string（選填） | `draft` 或 `confirmed` |

---

#### `get-invoice-tool`
取得單張請款單完整資料（含行程明細與目的地）。需要：`invoices:read`

| 參數 | 型別 | 說明 |
|---|---|---|
| `invoice_id` | integer（必填） | 請款單 ID |

---

#### `list-invoice-trips-tool`
查詢指定請款單的行程明細列表。需要：`trips:read`

| 參數 | 型別 | 說明 |
|---|---|---|
| `invoice_id` | integer（必填） | 請款單 ID |

---

#### `list-freight-rates-tool`
查詢費率表。需要：`rates:read`

| 參數 | 型別 | 說明 |
|---|---|---|
| `origin_id` | integer（選填） | 篩選起點 |
| `carrier_type_id` | integer（選填） | 篩選托運方式 |

---

#### `list-locations-tool`
列出所有地點。需要：`clients:read`

#### `list-drivers-tool`
列出所有司機。需要：`clients:read`

#### `list-carrier-types-tool`
列出所有托運方式。需要：`clients:read`

---

#### `get-setting-tool`
讀取系統設定值。需要：`settings:read`

| 參數 | 型別 | 說明 |
|---|---|---|
| `key` | string（必填） | 設定鍵值（如 `issuer_name`、`additional_stop_fee`） |

---

### Write Tools — 寫入資料

#### `create-invoice-tool`
建立新的草稿請款單，自動帶入系統設定的發票抬頭。需要：`invoices:write`

| 參數 | 型別 | 說明 |
|---|---|---|
| `client_id` | integer（必填） | 客戶 ID |
| `year` | integer（必填） | 年份（2000–2100） |
| `month` | integer（必填） | 月份（1–12） |
| `invoice_number` | string（選填） | 請款單號 |

> 注意：同一客戶的同年月只能有一張請款單。

---

#### `add-invoice-trip-tool`
為請款單新增行程明細。需要：`trips:write`

> confirmed 狀態的請款單無法新增行程。

| 參數 | 型別 | 說明 |
|---|---|---|
| `invoice_id` | integer（必填） | 請款單 ID |
| `date` | date（必填） | 行程日期（`YYYY-MM-DD`） |
| `origin_id` | integer（必填） | 起點地點 ID |
| `driver_id` | integer（必填） | 司機 ID |
| `carrier_type_id` | integer（必填） | 托運方式 ID |
| `freight_fee` | number（必填） | 運費金額 |
| `weight` | string（選填） | 貨物重量/說明 |
| `stops` | array（必填） | 目的地地點 ID 陣列，至少 1 個 |

新增後自動重算請款單總金額。

---

#### `update-invoice-trip-tool`
修改現有行程明細。需要：`trips:write`

> confirmed 狀態的請款單無法修改行程。

| 參數 | 型別 | 說明 |
|---|---|---|
| `trip_id` | integer（必填） | 行程 ID |
| `date` | date（選填） | |
| `origin_id` | integer（選填） | |
| `driver_id` | integer（選填） | |
| `carrier_type_id` | integer（選填） | |
| `freight_fee` | number（選填） | |
| `weight` | string（選填） | |
| `stops` | array（選填） | 完整取代所有目的地 |

---

#### `delete-invoice-trip-tool`
刪除行程明細。需要：`trips:write`

> confirmed 狀態的請款單無法刪除行程。

| 參數 | 型別 | 說明 |
|---|---|---|
| `trip_id` | integer（必填） | 行程 ID |

---

#### `update-freight-rate-tool`
修改費率的基本價格。需要：`rates:write`

| 參數 | 型別 | 說明 |
|---|---|---|
| `rate_id` | integer（必填） | 費率 ID |
| `base_price` | number（必填） | 新的基本價格 |

---

#### `set-setting-tool`
修改系統設定值。需要：`settings:write`

| 參數 | 型別 | 說明 |
|---|---|---|
| `key` | string（必填） | 設定鍵值 |
| `value` | string（必填） | 新的設定值 |

重要系統設定鍵值：

| 鍵值 | 說明 |
|---|---|
| `issuer_name` | 請款抬頭名稱 |
| `issuer_address` | 請款抬頭地址 |
| `issuer_phone` | 請款抬頭電話 |
| `additional_stop_fee` | 每個額外目的地的附加費用 |

---

### Action Tools — 業務操作

#### `confirm-invoice-tool`
確認請款單（`draft` → `confirmed`）。需要：`invoices:write`

> 確認後行程明細變為唯讀，無法新增/修改/刪除。

| 參數 | 型別 | 說明 |
|---|---|---|
| `invoice_id` | integer（必填） | 請款單 ID |

---

#### `unlock-invoice-tool`
解除鎖定請款單（`confirmed` → `draft`）。需要：`invoices:write`

| 參數 | 型別 | 說明 |
|---|---|---|
| `invoice_id` | integer（必填） | 請款單 ID |

---

#### `calculate-freight-fee-tool`
試算運費（不寫入 DB）。需要：`rates:read`

公式：`運費 = base_price + (額外目的地數量 × additional_stop_fee)`

| 參數 | 型別 | 說明 |
|---|---|---|
| `origin_id` | integer（必填） | 起點地點 ID |
| `carrier_type_id` | integer（必填） | 托運方式 ID |
| `destination_ids` | array（必填） | 目的地 ID 陣列，第一個為主目的地 |

回傳：

```json
{
  "base_price": 1000,
  "additional_stops": 2,
  "additional_stop_fee": 200,
  "total_additional_fee": 400,
  "freight_fee": 1400,
  "rate_found": true
}
```

---

#### `recalculate-invoice-total-tool`
重新計算請款單總金額（= 所有行程運費總和）。需要：`invoices:write`

| 參數 | 型別 | 說明 |
|---|---|---|
| `invoice_id` | integer（必填） | 請款單 ID |

---

## 常見操作流程範例

### 流程一：為客戶建立月份請款單

```
1. list-clients-tool           → 取得客戶 ID
2. list-locations-tool         → 取得起點/目的地 ID
3. list-drivers-tool           → 取得司機 ID
4. list-carrier-types-tool     → 取得托運方式 ID
5. create-invoice-tool         → 建立草稿請款單（帶入 client_id, year, month）
6. calculate-freight-fee-tool  → 試算每趟運費
7. add-invoice-trip-tool       → 逐筆新增行程明細
8. confirm-invoice-tool        → 確認請款單
```

### 流程二：查詢並修正已確認的請款單

```
1. list-invoices-tool          → 查詢（status=confirmed）
2. get-invoice-tool            → 取得完整明細
3. unlock-invoice-tool         → 解除鎖定（改為 draft）
4. update-invoice-trip-tool    → 修改行程
5. confirm-invoice-tool        → 重新確認
```

### 流程三：批次更新費率

```
1. list-freight-rates-tool     → 取得所有費率（含 rate_id）
2. update-freight-rate-tool    → 更新每筆費率的 base_price
3. recalculate-invoice-total-tool → 重算受影響的草稿請款單總金額
```

---

## 錯誤處理

### 認證失敗（HTTP 401）

```json
{ "error": "Unauthenticated." }
```

原因：Token 不存在、已過期或格式錯誤。

### 權限不足（MCP Error）

```json
{
  "result": {
    "isError": true,
    "content": [{ "type": "text", "text": "Token does not have invoices:write ability." }]
  }
}
```

原因：Token 缺少對應的 Ability，請重新建立具備所需 Ability 的 Token。

### 業務邏輯錯誤（MCP Error）

```json
{
  "result": {
    "isError": true,
    "content": [{ "type": "text", "text": "請款單已確認，無法新增行程。" }]
  }
}
```

常見情境：
- 對 `confirmed` 請款單執行寫入操作
- 嘗試再次確認已是 `confirmed` 的請款單
- 嘗試解除鎖定已是 `draft` 的請款單

### 驗證錯誤

```json
{
  "result": {
    "isError": true,
    "content": [{ "type": "text", "text": "The invoice_id field is required." }]
  }
}
```

原因：傳入的參數不符合驗證規則。

---

## Token 安全建議

1. **最小權限原則**：依使用情境只授予必要的 Abilities
2. **定期輪換**：正式環境建議定期重新產生 Token
3. **不同用途使用不同 Token**：查詢用、寫入用、管理用各自建立獨立 Token
4. **Token 過期設定**：預設 30 天（`config/sanctum.php` 的 `expiration: 43200`），可依需求調整
