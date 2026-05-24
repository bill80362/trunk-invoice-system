## ADDED Requirements

### Requirement: list_clients Tool
系統 SHALL 提供 `list_clients` Tool，返回所有客戶清單（id、name、contact、phone、address）。需要 `clients:read` ability。

#### Scenario: 列出客戶
- **WHEN** Agent 呼叫 `list_clients`（無參數）
- **THEN** 返回所有 Client 記錄的陣列

### Requirement: list_invoices Tool
系統 SHALL 提供 `list_invoices` Tool，支援依 `client_id`、`year`、`month`、`status` 篩選。需要 `invoices:read` ability。

#### Scenario: 依客戶篩選請款單
- **WHEN** Agent 呼叫 `list_invoices` 並帶入 `client_id`
- **THEN** 返回該客戶的所有請款單，每筆含 id、client_id、year、month、status、total_amount

#### Scenario: 依狀態篩選請款單
- **WHEN** Agent 呼叫 `list_invoices` 並帶入 `status: "confirmed"`
- **THEN** 僅返回 confirmed 狀態的請款單

### Requirement: get_invoice Tool
系統 SHALL 提供 `get_invoice` Tool，返回單張請款單的完整資料，含行程明細與站點。需要 `invoices:read` ability。

#### Scenario: 取得完整請款單
- **WHEN** Agent 呼叫 `get_invoice` 帶入有效的 `invoice_id`
- **THEN** 返回請款單資料，含 client、invoiceTrips（含 origin、driver、carrierType、invoiceTripStops）

#### Scenario: 請款單不存在
- **WHEN** Agent 呼叫 `get_invoice` 帶入不存在的 `invoice_id`
- **THEN** 返回 404 錯誤訊息

### Requirement: list_invoice_trips Tool
系統 SHALL 提供 `list_invoice_trips` Tool，返回特定請款單的所有行程明細，依 sequence、date 排序。需要 `trips:read` ability。

#### Scenario: 列出行程明細
- **WHEN** Agent 呼叫 `list_invoice_trips` 帶入 `invoice_id`
- **THEN** 返回行程陣列，每筆含 date、origin、driver、carrierType、freight_fee、weight 及目的地站點清單

### Requirement: list_freight_rates Tool
系統 SHALL 提供 `list_freight_rates` Tool，支援依 `origin_id`、`carrier_type_id` 篩選費率。需要 `rates:read` ability。

#### Scenario: 查詢費率
- **WHEN** Agent 呼叫 `list_freight_rates`
- **THEN** 返回費率記錄陣列，每筆含 origin、destination、carrierType、base_price

### Requirement: list_locations Tool
系統 SHALL 提供 `list_locations` Tool，返回所有地點（id、name）。需要 `clients:read` ability。

#### Scenario: 列出地點
- **WHEN** Agent 呼叫 `list_locations`
- **THEN** 返回所有 Location 記錄

### Requirement: list_drivers Tool
系統 SHALL 提供 `list_drivers` Tool，返回所有司機（id、name、phone）。需要 `clients:read` ability。

#### Scenario: 列出司機
- **WHEN** Agent 呼叫 `list_drivers`
- **THEN** 返回所有 Driver 記錄

### Requirement: list_carrier_types Tool
系統 SHALL 提供 `list_carrier_types` Tool，返回所有托運方式（id、name）。需要 `clients:read` ability。

#### Scenario: 列出托運方式
- **WHEN** Agent 呼叫 `list_carrier_types`
- **THEN** 返回所有 CarrierType 記錄

### Requirement: get_setting Tool
系統 SHALL 提供 `get_setting` Tool，依 `key` 返回系統設定值。需要 `settings:read` ability。

#### Scenario: 取得設定值
- **WHEN** Agent 呼叫 `get_setting` 帶入有效的 `key`
- **THEN** 返回對應的設定值

#### Scenario: 取得不存在的設定值
- **WHEN** Agent 呼叫 `get_setting` 帶入不存在的 `key`
- **THEN** 返回 null 或預設值
