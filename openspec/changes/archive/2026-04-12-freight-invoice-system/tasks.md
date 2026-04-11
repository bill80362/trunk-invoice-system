## 1. 環境安裝與初始設定

- [x] 1.1 安裝 Filament v3：`composer require filament/filament:"^3.2" -W` 並執行 `php artisan filament:install --panels`
- [x] 1.2 設定 User model 實作 `FilamentUser` interface，允許登入 Admin Panel
- [x] 1.3 建立初始管理員帳號（Seeder 或 tinker）

## 2. 基礎資料 Migrations & Models

- [x] 2.1 建立 `drivers` migration（id, name, phone, timestamps）
- [x] 2.2 建立 `carrier_types` migration（id, name, timestamps）
- [x] 2.3 建立 `locations` migration（id, name, timestamps）
- [x] 2.4 建立 `clients` migration（id, name, contact, phone, address, timestamps）
- [x] 2.5 建立 Driver、CarrierType、Location、Client Model
- [x] 2.6 執行 migrations

## 3. 費率表與設定 Migrations & Models

- [x] 3.1 建立 `freight_rates` migration（id, origin_id, destination_id, carrier_type_id, base_price, timestamps；unique index on origin+destination+carrier_type）
- [x] 3.2 建立 `settings` migration（id, key, value, timestamps）
- [x] 3.3 建立 FreightRate Model（belongsTo origin/destination/carrierType）
- [x] 3.4 建立 Setting Model 及 `Setting::get($key, $default)` / `Setting::set($key, $value)` helper

## 4. 請款單 Migrations & Models

- [x] 4.1 建立 `invoices` migration（id, client_id, year, month, invoice_number, issuer_name, issuer_address, issuer_phone, total_amount, status, confirmed_at, timestamps；unique index on client+year+month）
- [x] 4.2 建立 `invoice_trips` migration（id, invoice_id, date, origin_id, driver_id, carrier_type_id, freight_fee, weight, sequence, timestamps）
- [x] 4.3 建立 `invoice_trip_stops` migration（id, invoice_trip_id, location_id, sequence, timestamps）
- [x] 4.4 建立 Invoice Model（hasMany invoiceTrips、belongsTo client；`isConfirmed()` method；`recalculateTotal()` method；刪除時 cascade 刪除 invoiceTrips 及 invoiceTripStops）
- [x] 4.5 建立 InvoiceTrip Model（hasMany invoiceTripStops、belongsTo invoice/origin/driver/carrierType）
- [x] 4.6 建立 InvoiceTripStop Model（belongsTo invoiceTrip、location）
- [x] 4.7 執行 migrations

## 5. 基礎資料 Filament Resources

- [x] 5.1 建立 DriverResource（列表含姓名/電話；表單含 name/phone；刪除保護）
- [x] 5.2 建立 CarrierTypeResource（列表/表單 name；刪除保護）
- [x] 5.3 建立 LocationResource（列表/表單 name；刪除保護）
- [x] 5.4 建立 ClientResource（列表/表單含 name/contact/phone/address；刪除保護）

## 6. 費率表 Filament Resource 與系統設定

- [x] 6.1 建立 FreightRateResource（列表含起點/目的地/托運方式/基礎運費；表單含三個 Select + base_price；unique 驗證）
- [x] 6.2 建立 SettingsPage（Filament Page with form）：欄位 additional_stop_fee、issuer_name、issuer_address、issuer_phone；讀寫 settings 資料表

## 7. 請款單 Filament Resource — 基本結構

- [x] 7.1 建立 InvoiceResource（列表含貨主/年月/狀態/總金額）
- [x] 7.2 建立 Invoice 表單：client_id、year、month、issuer_name、issuer_address、issuer_phone（預帶系統設定值）
- [x] 7.3 加入 unique 驗證（貨主+年+月）
- [x] 7.4 確認狀態時整個表單改為唯讀（`disabled` based on `isConfirmed()`）

## 8. 請款單行程明細 Repeater

- [x] 8.1 在 InvoiceResource 表單加入 InvoiceTrips Repeater（欄位：date、origin_id、carrier_type_id、driver_id、weight、freight_fee），預設以日期升序排列，支援拖曳排序
- [x] 8.2 在 Repeater 內加入 stops 嵌套 Repeater（location_id、sequence 自動遞增）
- [x] 8.3 實作運費自動計算：`afterStateUpdated` on origin_id、stops[0].location_id、carrier_type_id，呼叫 Livewire action 查詢 FreightRate，回填 freight_fee
- [x] 8.4 實作 stops 數量變動時附加費用重新計算（附加站點數 × additional_stop_fee + base_price）
- [x] 8.5 儲存時重新計算並更新 Invoice.total_amount

## 9. 請款單確認與解除鎖定功能

- [x] 9.1 加入「確認請款單」Filament Action（HeaderAction），加入二次確認對話框
- [x] 9.2 Action 執行後將 status 改為 confirmed，記錄 confirmed_at
- [x] 9.3 已確認狀態隱藏確認按鈕
- [x] 9.4 加入「解除鎖定」Filament Action（HeaderAction），加入二次確認對話框
- [x] 9.5 解除鎖定 Action 執行後將 status 改為 draft，清除 confirmed_at，表單恢復可編輯
- [x] 9.6 草稿狀態隱藏解除鎖定按鈕，已確認狀態顯示解除鎖定按鈕

## 10. 請款單列印

- [x] 10.1 建立列印 Blade view（`resources/views/invoices/print.blade.php`）：標題、請款方資訊、貨主名稱、月份、行程明細表格（日期、行程明細、托運方式、重量、運費、司機）、合計
- [x] 10.2 加入 print-friendly CSS（@media print 隱藏非列印元素）
- [x] 10.3 草稿請款單列印時顯示「草稿」浮水印
- [x] 10.4 在 InvoiceResource 加入「列印」Action，開新視窗至列印路由
- [x] 10.5 建立列印路由（`routes/web.php`）與 Controller action，需驗證登入

## 11. 收尾與測試

- [x] 11.1 建立基礎測試資料 Seeder（地點、司機、托運方式、費率）
- [x] 11.2 手動測試完整流程：建立請款單 → 新增行程 → 運費自動計算 → 確認 → 列印
- [x] 11.3 確認刪除保護（司機/地點/貨主被引用時無法刪除）
- [x] 11.4 確認唯一性驗證（費率表重複、請款單重複）
