## Why

目前公司貨運請款作業仰賴人工製作 Excel，容易出錯且耗時。需要一套系統化的貨運請款單管理系統，讓每月的行程明細、運費計算、請款單產出自動化，以提升財務效率與正確性。

## What Changes

- 新增司機管理模組，可在系統中建立、編輯司機資料
- 新增托運方式管理模組，可建立各種托運類型（整車、零擔等）
- 新增地點管理模組，統一管理起點與目的地
- 新增貨主管理模組，每份請款單對應一個貨主
- 新增費率表管理模組，以「起點 + 第一目的地 + 托運方式」為 key 定義基礎運費
- 新增系統設定頁面，可設定附加站點費用與請款單抬頭預設值
- 新增請款單管理模組，支援在請款單內直接建立行程明細（方案Y）
- 請款單狀態分為草稿（可編輯）與已確認（鎖定），確認後無法再修改
- 運費根據起點 + 第一目的地 + 托運方式自動帶入，附加站點自動累加額外費用，最終均可手動調整
- 每份請款單可列印輸出，格式為月份表格，包含日期、行程明細、托運方式、重量、運費、司機欄位

## Capabilities

### New Capabilities

- `master-data`: 基礎資料管理，涵蓋司機、托運方式、地點、貨主四個主檔
- `freight-rate`: 費率表管理，以起點 + 第一目的地 + 托運方式為 key，設定基礎運費；系統設定附加站點費用
- `invoice-management`: 請款單建立與管理，含行程明細的新增/編輯，狀態管理（草稿/確認），以及列印輸出
- `invoice-print`: 請款單列印輸出，按月份、貨主產生格式化的請款單 PDF 或列印頁面

### Modified Capabilities

（無，此為全新系統）

## Impact

- **框架**：Laravel 13 + Filament v3（全新 Admin Panel）
- **新增 Migrations**：drivers, carrier_types, locations, clients, freight_rates, invoices, invoice_trips, invoice_trip_stops
- **新增 Filament Resources**：DriverResource, CarrierTypeResource, LocationResource, ClientResource, FreightRateResource, InvoiceResource, SettingsPage
- **列印功能**：需要 Blade view 或 PDF 套件（如 barryvdh/laravel-dompdf）渲染請款單
- **無 API 變更**：純後台管理系統，無公開 API
