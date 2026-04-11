## Context

全新建置的貨運請款單後台系統，使用 Laravel 13 + Filament v3。目前無任何既有程式碼，從 Laravel skeleton 開始開發。系統為內部使用的單租戶後台，主要使用者為公司財務/行政人員，使用者數量少（5人以下）。

## Goals / Non-Goals

**Goals:**
- 建立完整的基礎資料管理（司機、托運方式、地點、貨主）
- 費率表管理（起點 + 第一目的地 + 托運方式 → 基礎運費）
- 系統設定：附加站點費用、請款單抬頭預設值
- 請款單建立與管理：在請款單內直接新增/編輯行程明細（Filament Repeater）
- 運費自動計算：基礎運費 + 附加站點費用 × 附加站點數量，最終可手動覆寫
- 請款單狀態管理：草稿（可編輯）→ 確認（鎖定）
- 請款單列印：產生 Blade 列印頁面（print-friendly CSS）

**Non-Goals:**
- 多租戶（Multi-tenancy）
- 公開 API / 行動 APP
- 複雜用戶角色權限
- PDF 產生（使用瀏覽器列印即可）
- 跨月份行程資料分析報表

## Decisions

### D1. 行程資料模型：嵌入請款單，不獨立存在

**決定**：行程記錄（InvoiceTrip）的父層是 Invoice，沒有獨立的 Trip 資源頁面。

**理由**：使用者操作流程為「建立請款單 → 填入當月行程」，行程不在請款單外單獨管理。這簡化了資料模型與 UI。

**替代方案**：獨立 Trip 資源（月底匯入請款單）→ 被排除，Because 使用者明確選擇方案 Y。

---

### D2. 多目的地設計：TripStop 子表

**決定**：每筆行程（InvoiceTrip）有一個起點欄位（origin_id），目的地們用 `invoice_trip_stops` 子表儲存，含 sequence 排序。

**資料結構**：
```
invoices
  └─ invoice_trips (行程明細)
       ├─ origin_id → locations
       ├─ driver_id → drivers
       ├─ carrier_type_id → carrier_types
       ├─ freight_fee (最終運費，可手動改)
       ├─ weight (重量，文字欄位)
       └─ invoice_trip_stops (目的地清單)
            ├─ location_id → locations
            └─ sequence
```

**Filament UI**：InvoiceTrip 以 Repeater 呈現在 Invoice 表單內；stops 以嵌套 Repeater 呈現。

---

### D3. 費率自動帶入邏輯（前端 Livewire/Alpine）

**決定**：使用 Filament 的 `afterStateUpdated` reactive callback，當起點、第一目的地、或托運方式變動時，透過 AJAX（Livewire action）查詢費率表並更新 freight_fee 欄位。

**計算邏輯**：
```
base_price = FreightRate.lookup(origin, stops[0], carrier_type) ?? 0
extra_stops = max(0, stops.count - 1)
auto_price = base_price + (extra_stops × setting('additional_stop_fee'))
freight_fee = auto_price  // 用戶仍可手動覆寫
```

**觸發時機**：起點改變、stops[0] 改變、托運方式改變時重新計算。stops 數量增減時重新計算。

---

### D4. 請款單確認（鎖定）機制

**決定**：Invoice 有 `status` 欄位（`draft` / `confirmed`）及 `confirmed_at`。Confirmed 後，Filament form 進入唯讀模式（`disabled(fn($record) => $record?->isConfirmed())`），並隱藏儲存按鈕。

**解鎖**：提供「解除鎖定」功能，將狀態從 confirmed 改回 draft，清除 confirmed_at，表單恢復可編輯。已確認的請款單也可刪除。

---

### D4b. 請款單資料快照

**決定**：請款單建立行程明細時，相關選項（司機名稱、地點名稱、托運方式名稱等）寫入後即固定。若後續基礎資料被修改，已建立的請款單不受影響。實作方式為行程明細透過 FK 關聯主檔，但請款單 confirmed 後整張鎖定。草稿狀態時，行程記錄跟隨主檔最新值顯示。

**理由**：確認後的請款單作為財務文件，內容不應因主檔修改而改變。

---

### D4c. 行程排序

**決定**：請款單內的行程明細按日期升序排列（小的在前）。Repeater 預設以 date 排序，使用者也可在 Repeater 中手動拖曳排序。

---

### D5. 請款單列印

**決定**：使用獨立 Blade view（`resources/views/invoices/print.blade.php`）+ print-friendly CSS，透過 Filament Action 開新視窗預覽列印。不使用 PDF 套件。

**理由**：瀏覽器列印即可滿足需求，不引入額外 dependency。

---

### D6. 系統設定儲存方式

**決定**：使用 `settings` 資料表（key-value 結構），搭配簡單的 `Setting` Model 與 helper。不使用第三方 settings 套件。

**設定項目**：
- `additional_stop_fee`（附加站點費用，預設 0）
- `issuer_name`（請款方公司名稱）
- `issuer_address`（請款方地址）
- `issuer_phone`（請款方電話）

---

### D7. Filament 安裝方式

**決定**：使用 Filament v3 standalone panel（`php artisan filament:install --panels`），預設 Admin Panel，路徑 `/admin`。使用 Laravel 內建 User model 作為 Filament user（`FilamentUser` interface）。

## Risks / Trade-offs

- **嵌套 Repeater 複雜度** → Filament 的 Repeater 支援嵌套，但 stops 嵌套在 trips 內，需謹慎處理 `afterStateUpdated` 的事件傳遞。緩解：先實作單層 Repeater，再加嵌套。
- **運費自動計算 UX** → Livewire reactive 可能有短暫 loading 閃爍。緩解：加上 loading 狀態提示。
- **大量行程效能** → 一個月若有數百筆行程，Repeater 渲染可能較慢。緩解：此情境不太可能，暫不處理。
- **列印格式跨瀏覽器差異** → print CSS 在不同瀏覽器行為略有差異。緩解：以 Chrome 為主要支援目標。

## Open Questions

- 無，所有關鍵決策已在探索階段確認。
