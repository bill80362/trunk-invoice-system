## 1. 建立 InvoiceTripsRelationManager

- [x] 1.1 執行 `php artisan make:filament-relation-manager InvoiceResource invoiceTrips InvoiceTrip --no-interaction` 建立 RelationManager 骨架
- [x] 1.2 在 `InvoiceTripsRelationManager` 的 `form()` 內實作行程 schema：DatePicker (date)、Select (origin_id)、Select (carrier_type_id)、Select (driver_id)、TextInput (weight)、TextInput (freight_fee)
- [x] 1.3 在 `form()` 內加入 `invoiceTripStops` Repeater（含 Select location_id，使用 `->searchable()` 不加 `->preload()`）
- [x] 1.4 將 `calculateFreightFee` 邏輯移植至 RelationManager，origin_id / carrier_type_id / invoiceTripStops 異動時自動計算運費
- [x] 1.5 在 `table()` 設定顯示欄位：日期、起點、司機、托運方式、重量、運費
- [x] 1.6 加入 `headerActions`：CreateAction（依 `isConfirmed()` 隱藏）
- [x] 1.7 加入 table actions：EditAction、DeleteAction（依 `isConfirmed()` 隱藏）
- [x] 1.8 在 `afterCreate`、`afterSave`、`afterDelete` 回呼中呼叫 `$this->getOwnerRecord()->recalculateTotal()`

## 2. 修改 InvoiceResource

- [x] 2.1 從 `InvoiceResource::form()` 移除整個 `invoiceTrips` Repeater schema block
- [x] 2.2 在 `InvoiceResource::getRelations()` 中註冊 `InvoiceTripsRelationManager::class`

## 3. 修改 EditInvoice

- [x] 3.1 將 `sortTripsByDate` Action 改為資料庫操作：查詢該 Invoice 所有行程依 date 排序，批次更新 `sequence` 欄位
- [x] 3.2 `sortTripsByDate` 完成後呼叫 `$this->refreshFormData([])` 或觸發 RelationManager 刷新

## 4. 測試

- [ ] 4.1 在本機開啟行程數量多的請款單編輯頁面，確認不再 OOM
- [ ] 4.2 測試新增行程：填寫資料、儲存、確認 table 更新、總金額正確重算
- [ ] 4.3 測試編輯行程：修改運費、儲存、確認總金額更新
- [ ] 4.4 測試刪除行程：確認 table 更新、總金額重算
- [ ] 4.5 測試運費自動計算：選擇起點/托運方式/目的地後確認運費帶入
- [ ] 4.6 測試確認請款單後：新增/編輯/刪除按鈕應隱藏
- [ ] 4.7 測試解鎖後：新增/編輯/刪除按鈕應恢復
- [ ] 4.8 測試「依日期排序」：執行後 table 應依日期重新排列

## 5. 部署

- [x] 5.1 執行 `vendor/bin/pint --dirty` 確認程式碼格式
- [ ] 5.2 上傳異動檔案至伺服器（SFTP）
- [ ] 5.3 在伺服器執行 `php artisan view:clear && php artisan cache:clear`
- [ ] 5.4 在伺服器開啟 `/admin/invoices/3/edit` 確認問題解決
