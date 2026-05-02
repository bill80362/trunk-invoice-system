# Copilot Instructions — trunk-invoice-system

## Tech Stack

- PHP 8.4 / Laravel 13 / Filament v5 / Livewire v4
- PHPUnit v12 for testing (no Pest)
- Laravel Pint for code formatting (`vendor/bin/pint --dirty --format agent` after any PHP change)

## Filament v5 Key API

- Form method: `form(Schema $schema): Schema` using `Filament\Schemas\Schema`
- `Section` is now `Filament\Schemas\Components\Section`
- Table actions: `Filament\Actions\{EditAction,DeleteAction,...}` (not `Filament\Tables\Actions`)
- `$navigationIcon` / `$navigationGroup` are instance properties, not static
- Page `$view` is `protected string $view` (not static)
- Use `TextColumn::make()->badge()->color(fn...)` instead of `BadgeColumn`

## Business Domain

This is a **freight invoice management system** (貨運請款單系統) for a logistics company.

### Core Entities

| Model | Table | Purpose |
|---|---|---|
| `Client` | `clients` | 貨主（客戶），每月向其開立請款單 |
| `Invoice` | `invoices` | 請款單，以「貨主 + 年份 + 月份」唯一，狀態: `draft` / `confirmed` |
| `InvoiceTrip` | `invoice_trips` | 請款單內的行程明細，含起點、托運方式、司機、運費 |
| `InvoiceTripStop` | `invoice_trip_stops` | 行程的目的地站點清單（按 sequence 排序） |
| `FreightRate` | `freight_rates` | 費率表，唯一鍵：`origin_id + destination_id + carrier_type_id` |
| `Driver` | `drivers` | 司機 |
| `Location` | `locations` | 地點（起點 / 目的地共用） |
| `CarrierType` | `carrier_types` | 托運方式 |
| `Setting` | `settings` | key-value 系統設定，用 `Setting::get/set()` |

### Key Business Rules

1. **運費計算**：`freight_fee = base_price + (額外目的地數量 × additional_stop_fee)`
   - `base_price` 來自 `FreightRate`（origin + 第一目的地 + CarrierType），無對應則為 0
   - `additional_stop_fee` 來自 `Setting::get('additional_stop_fee', 0)`
2. **請款單總金額**：`total_amount = SUM(invoice_trips.freight_fee)`，透過 `Invoice::recalculateTotal()` 更新
3. **請款單狀態**：`confirmed` 狀態下行程明細為唯讀，不可新增/編輯/刪除
4. **確認操作**：設定 `status = confirmed` + 記錄 `confirmed_at`
5. **解除鎖定**：將 `confirmed` 改回 `draft`，清除 `confirmed_at`
6. **請款抬頭預設值**：`Setting::get('issuer_name/issuer_address/issuer_phone')` 新建請款單時自動帶入

### Relationships

```
Client → hasMany → Invoice
Invoice → hasMany → InvoiceTrip (ordered by sequence, date)
InvoiceTrip → hasMany → InvoiceTripStop (ordered by sequence)
InvoiceTrip → belongsTo → Location (origin), Driver, CarrierType
InvoiceTripStop → belongsTo → Location (destination)
FreightRate → belongsTo → Location (origin), Location (destination), CarrierType
```

## Conventions

- Named routes + `route()` function for URL generation
- Factories for all test model creation; check for existing factory states first
- Feature tests preferred over unit tests
- Use `Setting::get()` / `Setting::set()` for all system settings access
- `php artisan make:` commands for all new files
