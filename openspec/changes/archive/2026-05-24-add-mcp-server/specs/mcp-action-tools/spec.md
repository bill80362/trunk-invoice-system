## ADDED Requirements

### Requirement: confirm_invoice Tool
系統 SHALL 提供 `confirm_invoice` Tool，將 draft 請款單狀態改為 confirmed。需要 `invoices:write` ability。MUST 同時記錄 `confirmed_at` 時間戳。

#### Scenario: 確認草稿請款單
- **WHEN** Agent 呼叫 `confirm_invoice` 帶入有效的 `invoice_id`
- **THEN** 系統將 `status` 設為 `confirmed`，記錄 `confirmed_at`，返回更新後請款單資料

#### Scenario: 確認已確認的請款單
- **WHEN** Agent 對已是 confirmed 狀態的請款單呼叫 `confirm_invoice`
- **THEN** 返回 422 錯誤：「請款單已處於確認狀態」

### Requirement: unlock_invoice Tool
系統 SHALL 提供 `unlock_invoice` Tool，將 confirmed 請款單解除鎖定回 draft 狀態。需要 `invoices:write` ability。MUST 清除 `confirmed_at`。

#### Scenario: 解除鎖定請款單
- **WHEN** Agent 呼叫 `unlock_invoice` 帶入有效的 `invoice_id`
- **THEN** 系統將 `status` 設回 `draft`，清除 `confirmed_at`，返回更新後請款單資料

#### Scenario: 解除未確認的請款單
- **WHEN** Agent 對 draft 狀態的請款單呼叫 `unlock_invoice`
- **THEN** 返回 422 錯誤：「請款單尚未確認，無需解除鎖定」

### Requirement: calculate_freight_fee Tool
系統 SHALL 提供 `calculate_freight_fee` Tool，依給定的 `origin_id`、`carrier_type_id`、`stop_ids` 計算預估運費，不寫入資料庫。需要 `trips:read` ability。

計算規則：
- `base_price` 查 FreightRate（origin + 第一目的地 + CarrierType），找不到則為 0
- `additional_fee` = (站點數 - 1) × `Setting::get('additional_stop_fee', 0)`
- `freight_fee` = `base_price` + `additional_fee`

#### Scenario: 計算單一目的地運費
- **WHEN** Agent 呼叫 `calculate_freight_fee` 帶入 `origin_id`、`carrier_type_id`、`stop_ids: [A]`
- **THEN** 返回 `base_price`（查 FreightRate）、`additional_fee: 0`、`freight_fee` 合計

#### Scenario: 計算多目的地運費
- **WHEN** Agent 呼叫 `calculate_freight_fee` 帶入 3 個 `stop_ids`
- **THEN** 返回 `additional_fee` = 2 × additional_stop_fee，`freight_fee` = base_price + additional_fee

#### Scenario: 無對應費率
- **WHEN** 查無對應 FreightRate 記錄
- **THEN** `base_price` 為 0，`freight_fee` 僅為 `additional_fee`

### Requirement: recalculate_invoice_total Tool
系統 SHALL 提供 `recalculate_invoice_total` Tool，重新計算並更新指定請款單的 `total_amount`。需要 `invoices:write` ability。MUST 呼叫 `Invoice::recalculateTotal()`。

#### Scenario: 重新計算總金額
- **WHEN** Agent 呼叫 `recalculate_invoice_total` 帶入 `invoice_id`
- **THEN** 系統呼叫 `recalculateTotal()`，返回更新後的 `total_amount`
