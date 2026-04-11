## ADDED Requirements

### Requirement: 建立請款單
系統 SHALL 允許管理員建立請款單（Invoice）。建立時必填欄位為：貨主、年份、月份。請款方資訊（公司名稱、地址、電話）自動帶入系統設定預設值，可在該張請款單上覆寫。新建請款單狀態為「草稿（draft）」。

#### Scenario: 建立草稿請款單
- **WHEN** 管理員選擇貨主、年份、月份並儲存
- **THEN** 系統建立草稿請款單，請款方資訊帶入預設值，可開始新增行程明細

#### Scenario: 同貨主同月份重複防止
- **WHEN** 管理員嘗試建立已存在的「貨主 + 年份 + 月份」組合
- **THEN** 系統 SHALL 阻止並顯示錯誤訊息

### Requirement: 行程明細管理
系統 SHALL 在請款單表單內（草稿狀態）提供行程明細（InvoiceTrip）的新增、編輯、刪除功能。每筆行程明細包含：
- 日期（必填）
- 起點（必填，選擇 Location）
- 目的地清單（至少一個，選擇 Location，可多個，含順序）
- 托運方式（必填，選擇 CarrierType）
- 司機（必填，選擇 Driver）
- 重量（選填，文字欄位）
- 運費（必填，數字，可手動覆寫）

#### Scenario: 新增行程明細
- **WHEN** 管理員在草稿請款單中新增行程並填入所有必填欄位後儲存
- **THEN** 系統儲存行程明細，並更新請款單總金額

#### Scenario: 運費自動帶入
- **WHEN** 管理員選擇起點、第一目的地、托運方式後
- **THEN** 系統 SHALL 自動查詢費率表並帶入運費：base_price + (額外目的地數量 × additional_stop_fee)。若費率表無對應資料則帶入 0

#### Scenario: 目的地增加時運費自動更新
- **WHEN** 管理員在已有基礎運費的行程上新增第二個（或更多）目的地
- **THEN** 系統 SHALL 自動重新計算運費，加上新增的附加站點費用（用戶仍可手動覆寫）

#### Scenario: 確認狀態不可編輯行程
- **WHEN** 請款單狀態為已確認（confirmed）
- **THEN** 系統 SHALL 將行程明細顯示為唯讀，禁止新增、編輯、刪除

### Requirement: 請款單總金額計算
系統 SHALL 自動計算請款單的總金額（total_amount），為所有行程明細運費之總和。

#### Scenario: 新增行程後總金額更新
- **WHEN** 管理員新增或編輯行程明細的運費後儲存
- **THEN** 系統重新計算並儲存最新的 total_amount

### Requirement: 請款單確認（鎖定）
系統 SHALL 提供「確認請款單」的操作。確認後，請款單狀態改為 confirmed，記錄 confirmed_at 時間，行程明細進入唯讀。確認後若基礎資料（司機、地點、托運方式等）被修改，不影響該請款單的內容（因表單已鎖定）。

#### Scenario: 確認請款單
- **WHEN** 管理員點擊「確認請款單」並確認操作
- **THEN** 系統將狀態改為 confirmed，記錄時間，表單進入唯讀模式

#### Scenario: 已確認請款單不可再確認
- **WHEN** 請款單已為 confirmed 狀態
- **THEN** 系統 SHALL 隱藏確認按鈕

### Requirement: 請款單解除鎖定
系統 SHALL 提供「解除鎖定」操作，將已確認的請款單狀態從 confirmed 改回 draft，清除 confirmed_at，表單恢復可編輯。

#### Scenario: 解除鎖定
- **WHEN** 管理員在已確認請款單上點擊「解除鎖定」並確認操作
- **THEN** 系統將狀態改為 draft，清除 confirmed_at，表單恢復可編輯

#### Scenario: 草稿請款單不顯示解除鎖定
- **WHEN** 請款單狀態為 draft
- **THEN** 系統 SHALL 隱藏解除鎖定按鈕

### Requirement: 請款單刪除
系統 SHALL 允許刪除請款單，無論狀態為 draft 或 confirmed。刪除時需二次確認。刪除請款單時 SHALL 同時刪除其下所有行程明細及站點資料。

#### Scenario: 刪除請款單
- **WHEN** 管理員刪除請款單並確認
- **THEN** 系統刪除該請款單及其所有行程明細與站點

### Requirement: 行程明細排序
請款單內的行程明細 SHALL 預設以日期升序（小的在前）排列。使用者可在 Repeater 中手動拖曳調整順序。列印時以儲存的順序顯示。

#### Scenario: 預設日期排序
- **WHEN** 管理員開啟請款單編輯頁
- **THEN** 行程明細 SHALL 以日期升序顯示

#### Scenario: 手動排序
- **WHEN** 使用者拖曳行程明細調整順序後儲存
- **THEN** 系統 SHALL 儲存新的順序，後續開啟及列印時以新順序顯示
