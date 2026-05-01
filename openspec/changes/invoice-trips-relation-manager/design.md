## Context

`InvoiceResource` 的編輯頁面目前使用雙層嵌套 Repeater（`invoiceTrips` → `invoiceTripStops`）在同一個 Livewire 組件內渲染所有行程。Filament v5 的 Repeater 會為每個 item 建立完整的 Blade ComponentSlot 物件樹，行程數量多時記憶體消耗呈線性增長（O(n)），超過 PHP 128MB per-process 上限後頁面完全崩潰。

**現有相關檔案：**
- `app/Filament/Resources/InvoiceResource.php` — form schema 含 invoiceTrips Repeater
- `app/Filament/Resources/InvoiceResource/Pages/EditInvoice.php` — 含 sortTripsByDate、confirm、unlock、print Actions
- `app/Models/InvoiceTrip.php` — belongsTo Invoice，hasMany InvoiceTripStops
- `app/Models/InvoiceTripStop.php` — belongsTo InvoiceTrip，belongsTo Location

## Goals / Non-Goals

**Goals:**
- 消除 OOM 錯誤，無論行程數量多少都能正常開啟編輯頁面
- 保留所有現有功能：新增/編輯/刪除行程、目的地停靠站、運費自動計算、拖拉排序、確認/解鎖/列印
- 維持現有 UX 流暢度

**Non-Goals:**
- 不變更資料庫 schema 或 Model 關聯
- 不修改列印功能
- 不調整 PHP memory_limit（治本而非治標）

## Decisions

### 決策 1：使用 RelationManager 取代 invoiceTrips Repeater

**選擇**：建立 `InvoiceTripsRelationManager`，以 table 顯示行程清單，編輯透過 slide-over panel（或 modal）逐筆進行。

**理由**：RelationManager 的 table 只渲染靜態資料列，點擊編輯才載入單筆 form 組件，記憶體從 O(n) 降至 O(1)。

**替代方案考慮：**
- *調高 memory_limit*：治標，下個月行程更多還是會爆
- *Repeater + lazy load*：Filament v5 Repeater 不支援 virtual scroll/lazy load
- *分頁顯示 Repeater*：Repeater 不支援分頁

### 決策 2：目的地停靠站在 Modal 內保留輕量 Repeater

**選擇**：在 InvoiceTripsRelationManager 的 form（slide-over）內，`invoiceTripStops` 保留 Repeater，但移除所有 `->preload()`，改為純 `->searchable()`。

**理由**：每次 Modal 只載入 1 筆行程的 stops（通常 1-3 個），記憶體完全可控。若改成第二層 RelationManager 則 UX 變得過於複雜。

### 決策 3：sortTripsByDate 改為資料庫操作

**選擇**：Action 改為查詢該 Invoice 的所有 InvoiceTrip，依 date 排序後批次更新 `sequence` 欄位。

**理由**：RelationManager 沒有 form state 可操作，必須直接操作資料庫。這實際上更可靠（不依賴 Livewire state）。

### 決策 4：recalculateTotal 觸發點

**選擇**：在 RelationManager 的 `afterCreate`、`afterSave`、`afterDelete` 回呼中呼叫 `$this->getOwnerRecord()->recalculateTotal()`。

**理由**：確保任何行程異動都即時反映總金額，與現有 EditInvoice 的 `afterSave` 邏輯一致。

### 決策 5：鎖定狀態下隱藏新增/編輯/刪除

**選擇**：RelationManager 的 headerActions 和 table actions 依 `$this->getOwnerRecord()->isConfirmed()` 動態顯示/隱藏。

**理由**：與現有 form 的 `->disabled(fn () => $record?->isConfirmed())` 行為保持一致。

## Risks / Trade-offs

| 風險 | 緩解措施 |
|------|----------|
| UX 改變：使用者需點擊才能編輯行程，無法同時看所有行程欄位 | 在 table 顯示關鍵摘要欄（日期、起點、司機、運費），減少需要開 Modal 的情境 |
| 批次新增行程變麻煩（原本可一次填多筆） | 接受此取捨；可未來考慮「快速新增」功能 |
| Filament v5 RelationManager API 細節需驗證 | 參考文件及現有 RelationManager 範例 |

## Migration Plan

1. 建立 `InvoiceTripsRelationManager`
2. 在 `InvoiceResource::getRelations()` 中註冊
3. 從 `InvoiceResource::form()` 移除 `invoiceTrips` Repeater
4. 修改 `EditInvoice::sortTripsByDate` Action
5. 上傳至伺服器，執行 `php artisan view:clear`

## Open Questions

- Filament v5 RelationManager 是否支援 slide-over（side panel）模式？若不支援則用 modal。
