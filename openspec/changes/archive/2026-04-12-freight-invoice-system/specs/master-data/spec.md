## ADDED Requirements

### Requirement: 司機管理
系統 SHALL 提供司機（Driver）的 CRUD 管理功能。司機資料包含姓名與電話號碼。

#### Scenario: 建立司機
- **WHEN** 管理員填入司機姓名並儲存
- **THEN** 系統建立司機記錄，並可在行程明細中選擇此司機

#### Scenario: 編輯司機
- **WHEN** 管理員修改司機資料並儲存
- **THEN** 系統更新司機記錄

#### Scenario: 刪除司機
- **WHEN** 管理員刪除司機
- **THEN** 若該司機已被行程明細引用，系統 SHALL 阻止刪除並顯示錯誤訊息

### Requirement: 托運方式管理
系統 SHALL 提供托運方式（CarrierType）的 CRUD 管理功能，欄位為名稱。

#### Scenario: 建立托運方式
- **WHEN** 管理員填入托運方式名稱並儲存
- **THEN** 系統建立記錄，可在行程明細及費率表中選擇

#### Scenario: 刪除托運方式
- **WHEN** 管理員刪除托運方式
- **THEN** 若已被引用，系統 SHALL 阻止刪除並顯示錯誤訊息

### Requirement: 地點管理
系統 SHALL 提供地點（Location）的 CRUD 管理功能，欄位為名稱。地點同時作為行程起點、目的地與費率表地點使用。

#### Scenario: 建立地點
- **WHEN** 管理員填入地點名稱並儲存
- **THEN** 系統建立地點，可在行程起點、目的地、費率表中選用

#### Scenario: 刪除地點
- **WHEN** 管理員刪除地點
- **THEN** 若已被引用，系統 SHALL 阻止刪除並顯示錯誤訊息

### Requirement: 貨主管理
系統 SHALL 提供貨主（Client）的 CRUD 管理功能。欄位包含名稱、聯絡人、電話、地址（選填）。

#### Scenario: 建立貨主
- **WHEN** 管理員填入貨主名稱並儲存
- **THEN** 系統建立貨主，可在建立請款單時選擇

#### Scenario: 刪除貨主
- **WHEN** 管理員刪除貨主
- **THEN** 若已被請款單引用，系統 SHALL 阻止刪除並顯示錯誤訊息
