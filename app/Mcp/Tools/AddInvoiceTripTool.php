<?php

namespace App\Mcp\Tools;

use App\Models\Invoice;
use App\Models\InvoiceTrip;
use App\Models\InvoiceTripStop;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class AddInvoiceTripTool extends Tool
{
    protected string $description = '為指定請款單新增行程明細（confirmed 狀態請款單不可新增）。需要 trips:write ability。';

    public function schema(JsonSchema $schema): array
    {
        return [
            'invoice_id' => $schema->integer()->required()->description('請款單 ID'),
            'date' => $schema->string()->required()->description('行程日期（YYYY-MM-DD）'),
            'origin_id' => $schema->integer()->required()->description('起點地點 ID'),
            'driver_id' => $schema->integer()->required()->description('司機 ID'),
            'carrier_type_id' => $schema->integer()->required()->description('托運方式 ID'),
            'freight_fee' => $schema->number()->required()->description('運費金額'),
            'weight' => $schema->string()->nullable()->description('貨物重量（選填）'),
            'stops' => $schema->array()->required()->description('目的地站點 location_id 陣列，依序排列'),
        ];
    }

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('trips:write')) {
            throw new AuthorizationException('Token does not have trips:write ability.');
        }

        $data = $request->validate([
            'invoice_id' => 'required|integer|exists:invoices,id',
            'date' => 'required|date',
            'origin_id' => 'required|integer|exists:locations,id',
            'driver_id' => 'required|integer|exists:drivers,id',
            'carrier_type_id' => 'required|integer|exists:carrier_types,id',
            'freight_fee' => 'required|numeric|min:0',
            'weight' => 'nullable|string|max:255',
            'stops' => 'required|array|min:1',
            'stops.*' => 'integer|exists:locations,id',
        ]);

        $invoice = Invoice::findOrFail($data['invoice_id']);

        if ($invoice->isConfirmed()) {
            return Response::error('請款單已確認，無法新增行程。');
        }

        $maxSequence = $invoice->invoiceTrips()->max('sequence') ?? 0;

        $trip = InvoiceTrip::create([
            'invoice_id' => $invoice->id,
            'date' => $data['date'],
            'origin_id' => $data['origin_id'],
            'driver_id' => $data['driver_id'],
            'carrier_type_id' => $data['carrier_type_id'],
            'freight_fee' => $data['freight_fee'],
            'weight' => $data['weight'] ?? null,
            'sequence' => $maxSequence + 1,
        ]);

        foreach ($data['stops'] as $seq => $locationId) {
            InvoiceTripStop::create([
                'invoice_trip_id' => $trip->id,
                'location_id' => $locationId,
                'sequence' => $seq + 1,
            ]);
        }

        $invoice->recalculateTotal();

        return Response::json($trip->load([
            'origin:id,name',
            'driver:id,name',
            'carrierType:id,name',
            'invoiceTripStops.location:id,name',
        ])->toArray());
    }
}
