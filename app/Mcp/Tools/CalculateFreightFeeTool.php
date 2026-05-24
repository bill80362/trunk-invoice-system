<?php

namespace App\Mcp\Tools;

use App\Models\FreightRate;
use App\Models\Setting;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class CalculateFreightFeeTool extends Tool
{
    protected string $description = '預算運費（不寫入 DB）：依起點、托運方式、目的地站點清單計算 base_price + additional_stop_fee。需要 rates:read ability。';

    public function schema(JsonSchema $schema): array
    {
        return [
            'origin_id' => $schema->integer()->required()->description('起點地點 ID'),
            'carrier_type_id' => $schema->integer()->required()->description('托運方式 ID'),
            'destination_ids' => $schema->array()->required()->description('目的地站點 location_id 陣列，第一個為主目的地'),
        ];
    }

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('rates:read')) {
            throw new AuthorizationException('Token does not have rates:read ability.');
        }

        $data = $request->validate([
            'origin_id' => 'required|integer|exists:locations,id',
            'carrier_type_id' => 'required|integer|exists:carrier_types,id',
            'destination_ids' => 'required|array|min:1',
            'destination_ids.*' => 'integer|exists:locations,id',
        ]);

        $firstDestinationId = $data['destination_ids'][0];
        $additionalStops = count($data['destination_ids']) - 1;

        $rate = FreightRate::where('origin_id', $data['origin_id'])
            ->where('destination_id', $firstDestinationId)
            ->where('carrier_type_id', $data['carrier_type_id'])
            ->first();

        $basePrice = $rate ? (float) $rate->base_price : 0;
        $additionalStopFee = (float) Setting::get('additional_stop_fee', 0);
        $totalAdditionalFee = $additionalStops * $additionalStopFee;
        $totalFee = $basePrice + $totalAdditionalFee;

        return Response::json([
            'base_price' => $basePrice,
            'additional_stops' => $additionalStops,
            'additional_stop_fee' => $additionalStopFee,
            'total_additional_fee' => $totalAdditionalFee,
            'freight_fee' => $totalFee,
            'rate_found' => $rate !== null,
        ]);
    }
}
