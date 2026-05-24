<?php

namespace App\Mcp\Tools;

use App\Models\InvoiceTrip;
use App\Models\InvoiceTripStop;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class UpdateInvoiceTripTool extends Tool
{
    protected string $description = '修改指定行程明細（所屬請款單 confirmed 時不可修改）。需要 trips:write ability。';

    public function schema(JsonSchema $schema): array
    {
        return [
            'trip_id' => $schema->integer()->required()->description('行程明細 ID'),
            'date' => $schema->string()->nullable()->description('行程日期（YYYY-MM-DD）'),
            'origin_id' => $schema->integer()->nullable()->description('起點地點 ID'),
            'driver_id' => $schema->integer()->nullable()->description('司機 ID'),
            'carrier_type_id' => $schema->integer()->nullable()->description('托運方式 ID'),
            'freight_fee' => $schema->number()->nullable()->description('運費金額'),
            'weight' => $schema->string()->nullable()->description('貨物重量'),
            'stops' => $schema->array()->nullable()->description('目的地站點 location_id 陣列（傳入則整批替換）'),
        ];
    }

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('trips:write')) {
            throw new AuthorizationException('Token does not have trips:write ability.');
        }

        $data = $request->validate([
            'trip_id' => 'required|integer|exists:invoice_trips,id',
            'date' => 'sometimes|date',
            'origin_id' => 'sometimes|integer|exists:locations,id',
            'driver_id' => 'sometimes|integer|exists:drivers,id',
            'carrier_type_id' => 'sometimes|integer|exists:carrier_types,id',
            'freight_fee' => 'sometimes|numeric|min:0',
            'weight' => 'sometimes|nullable|string|max:255',
            'stops' => 'sometimes|array|min:1',
            'stops.*' => 'integer|exists:locations,id',
        ]);

        $trip = InvoiceTrip::with('invoice')->findOrFail($data['trip_id']);

        if ($trip->invoice->isConfirmed()) {
            return Response::error('請款單已確認，無法修改行程。');
        }

        $trip->fill(array_filter(
            array_intersect_key($data, array_flip(['date', 'origin_id', 'driver_id', 'carrier_type_id', 'freight_fee', 'weight'])),
            fn ($v) => ! is_null($v),
        ));
        $trip->save();

        if (isset($data['stops'])) {
            $trip->invoiceTripStops()->delete();
            foreach ($data['stops'] as $seq => $locationId) {
                InvoiceTripStop::create([
                    'invoice_trip_id' => $trip->id,
                    'location_id' => $locationId,
                    'sequence' => $seq + 1,
                ]);
            }
        }

        $trip->invoice->recalculateTotal();

        return Response::json($trip->load([
            'origin:id,name',
            'driver:id,name',
            'carrierType:id,name',
            'invoiceTripStops.location:id,name',
        ])->toArray());
    }
}
