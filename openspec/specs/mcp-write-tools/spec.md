## ADDED Requirements

### Requirement: create_invoice Tool
系統 SHALL 提供 `create_invoice` Tool，建立新的 draft 請款單。需要 `invoices:write` ability。建立時 MUST 自動帶入 `issuer_name`、`issuer_address`、`issuer_phone` 預設值。

#### Scenario: 建立請款單
- **WHEN** Agent 呼叫 `create_invoice` 帶入 `client_id`、`year`、`month`
- **THEN** 系統建立 draft 請款單，自動填入 issuer 預設值，返回新建請款單的 id 與資料

#### Scenario: 重複的客戶年月組合
- **WHEN** Agent 嘗試建立已存在（相同 client_id + year + month）的請款單
- **THEN** 返回 422 錯誤，說明該組合已存在

### Requirement: add_invoice_trip Tool
系統 SHALL 提供 `add_invoice_trip` Tool，為指定請款單新增行程明細。需要 `trips:write` ability。MUST 在執行前確認請款單非 confirmed 狀態，並在新增後呼叫 `recalculateTotal()`。

#### Scenario: 成功新增行程
- **WHEN** Agent 呼叫 `add_invoice_trip` 帶入 `invoice_id`、`date`、`origin_id`、`driver_id`、`carrier_type_id`、`freight_fee` 及 `stops`（location_id 陣列）
- **THEN** 系統新增行程與站點，重算請款單總金額，返回新行程資料

#### Scenario: 對 confirmed 請款單新增行程
- **WHEN** Agent 嘗試對 confirmed 狀態的請款單呼叫 `add_invoice_trip`
- **THEN** 返回 422 錯誤：「請款單已確認，無法新增行程」

### Requirement: update_invoice_trip Tool
系統 SHALL 提供 `update_invoice_trip` Tool，修改指定行程明細。需要 `trips:write` ability。MUST 確認所屬請款單非 confirmed 狀態，並在修改後呼叫 `recalculateTotal()`。

#### Scenario: 成功修改行程
- **WHEN** Agent 呼叫 `update_invoice_trip` 帶入 `trip_id` 及要修改的欄位
- **THEN** 系統更新行程資料，重算請款單總金額，返回更新後行程資料

#### Scenario: 對 confirmed 請款單修改行程
- **WHEN** Agent 嘗試修改屬於 confirmed 請款單的行程
- **THEN** 返回 422 錯誤：「請款單已確認，無法修改行程」

### Requirement: delete_invoice_trip Tool
系統 SHALL 提供 `delete_invoice_trip` Tool，刪除指定行程明細及其所有站點。需要 `trips:write` ability。MUST 確認所屬請款單非 confirmed 狀態，並在刪除後呼叫 `recalculateTotal()`。

#### Scenario: 成功刪除行程
- **WHEN** Agent 呼叫 `delete_invoice_trip` 帶入 `trip_id`
- **THEN** 系統刪除行程與所有關聯站點，重算請款單總金額，返回成功訊息

#### Scenario: 對 confirmed 請款單刪除行程
- **WHEN** Agent 嘗試刪除屬於 confirmed 請款單的行程
- **THEN** 返回 422 錯誤：「請款單已確認，無法刪除行程」

### Requirement: update_freight_rate Tool
系統 SHALL 提供 `update_freight_rate` Tool，依 `rate_id` 修改費率的 `base_price`。需要 `rates:write` ability。

#### Scenario: 修改費率
- **WHEN** Agent 呼叫 `update_freight_rate` 帶入 `rate_id` 與新的 `base_price`
- **THEN** 系統更新費率記錄，返回更新後資料

### Requirement: set_setting Tool
系統 SHALL 提供 `set_setting` Tool，以 `key`/`value` 方式修改系統設定。需要 `settings:write` ability。

#### Scenario: 修改設定
- **WHEN** Agent 呼叫 `set_setting` 帶入 `key` 與 `value`
- **THEN** 系統呼叫 `Setting::set($key, $value)`，返回成功訊息
