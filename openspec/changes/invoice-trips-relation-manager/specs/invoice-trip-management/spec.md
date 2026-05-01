## ADDED Requirements

### Requirement: 行程明細以 RelationManager table 顯示
InvoiceResource 編輯頁面 SHALL 以 RelationManager（獨立 table）顯示行程明細，而非將所有行程嵌入主 form 的 Repeater。

#### Scenario: 開啟請款單編輯頁面
- **WHEN** 使用者開啟任意行程數量的請款單編輯頁面
- **THEN** 頁面 SHALL 正常載入，不得因記憶體不足而崩潰

#### Scenario: 查看行程清單
- **WHEN** 使用者在編輯頁面查看行程明細區塊
- **THEN** 系統 SHALL 以 table 顯示所有行程，包含日期、起點、司機、托運方式、重量、運費欄位

### Requirement: 逐筆編輯行程
使用者 SHALL 能夠透過點擊行程列的編輯按鈕，開啟 panel（modal 或 slide-over）進行單筆行程編輯。

#### Scenario: 編輯既有行程
- **WHEN** 使用者點擊行程列的編輯按鈕
- **THEN** 系統 SHALL 開啟含該行程完整資料的編輯 panel，包含日期、起點、托運方式、司機、目的地（Repeater）、重量、運費

#### Scenario: 新增行程
- **WHEN** 使用者點擊「新增行程」按鈕
- **THEN** 系統 SHALL 開啟空白新增 panel

#### Scenario: 刪除行程
- **WHEN** 使用者點擊行程列的刪除按鈕並確認
- **THEN** 系統 SHALL 刪除該行程並重新計算請款單總金額

### Requirement: 行程 panel 內含目的地 Repeater 及運費自動計算
行程編輯 panel SHALL 包含目的地停靠站的 Repeater，及起點/托運方式/目的地異動時的運費自動計算。

#### Scenario: 自動計算運費
- **WHEN** 使用者在行程 panel 內選擇起點、托運方式、目的地後
- **THEN** 系統 SHALL 自動帶入對應運費費率，並計算多站附加費

### Requirement: 請款單總金額在行程異動後即時更新
任何行程的新增、修改、刪除操作完成後，請款單 SHALL 立即重新計算並更新 `total_amount`。

#### Scenario: 儲存行程後更新總金額
- **WHEN** 使用者在 panel 內儲存行程（新增或編輯）
- **THEN** 系統 SHALL 重新計算請款單 `total_amount` 並反映在頁面上

#### Scenario: 刪除行程後更新總金額
- **WHEN** 使用者刪除一筆行程
- **THEN** 系統 SHALL 重新計算請款單 `total_amount` 並反映在頁面上

### Requirement: 已確認請款單鎖定行程編輯
當請款單狀態為 `confirmed` 時，行程明細 SHALL 為唯讀，不得新增、編輯或刪除行程。

#### Scenario: 確認後隱藏操作按鈕
- **WHEN** 請款單狀態為 `confirmed`，使用者查看行程明細
- **THEN** 系統 SHALL 隱藏新增、編輯、刪除按鈕

### Requirement: 依日期排序行程
使用者 SHALL 能夠一鍵將所有行程依日期升冪重新排序。

#### Scenario: 執行排序
- **WHEN** 使用者點擊「依日期排序」按鈕並確認
- **THEN** 系統 SHALL 依日期升冪更新所有行程的 `sequence` 欄位，並刷新 table 顯示
