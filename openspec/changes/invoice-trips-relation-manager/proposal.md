## Why

編輯請款單（`/admin/invoices/{id}/edit`）時，頁面將所有行程的雙層嵌套 Repeater（`invoiceTrips` → `invoiceTripStops`）同時渲染到記憶體，導致 PHP 128MB per-process 上限被突破（OOM），行程數量多的請款單完全無法開啟編輯頁面。

## What Changes

- **移除** `InvoiceResource` form 中的 `invoiceTrips` Repeater
- **新增** `InvoiceTripsRelationManager`：以獨立 table 顯示行程清單，編輯透過 Modal 逐筆進行
- **新增** `InvoiceTripStopsRelationManager`（或在 Modal 內保留輕量 Repeater）處理目的地停靠站
- **修改** 「依日期排序」Action 改為資料庫操作更新 `sequence` 欄位
- **保留** 確認/解鎖/列印等 Header Actions 邏輯不變
- **保留** 運費自動計算邏輯，移至 RelationManager 的 Modal form 中

## Capabilities

### New Capabilities

- `invoice-trip-management`: 行程明細管理 — 以 RelationManager table 顯示行程清單，支援逐筆新增、編輯（Modal）、刪除，並在 Modal 內含目的地 Repeater 及運費自動計算

### Modified Capabilities

- （無需求層級變更，僅實作方式改變）

## Impact

- `app/Filament/Resources/InvoiceResource.php` — 移除 invoiceTrips Repeater schema
- `app/Filament/Resources/InvoiceResource/Pages/EditInvoice.php` — 修改 sortTripsByDate Action
- 新增 `app/Filament/Resources/InvoiceResource/RelationManagers/InvoiceTripsRelationManager.php`
- 記憶體消耗從 O(n) 降至 O(1)（每次只渲染 1 筆 Modal 資料）
