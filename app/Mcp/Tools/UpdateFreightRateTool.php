<?php

namespace App\Mcp\Tools;

use App\Models\FreightRate;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class UpdateFreightRateTool extends Tool
{
    protected string $description = '修改指定費率的 base_price。需要 rates:write ability。';

    public function schema(JsonSchema $schema): array
    {
        return [
            'rate_id' => $schema->integer()->required()->description('費率 ID'),
            'base_price' => $schema->number()->required()->description('新的基本運費'),
        ];
    }

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('rates:write')) {
            throw new AuthorizationException('Token does not have rates:write ability.');
        }

        $data = $request->validate([
            'rate_id' => 'required|integer|exists:freight_rates,id',
            'base_price' => 'required|numeric|min:0',
        ]);

        $rate = FreightRate::findOrFail($data['rate_id']);
        $rate->update(['base_price' => $data['base_price']]);

        return Response::json($rate->load([
            'origin:id,name',
            'destination:id,name',
            'carrierType:id,name',
        ])->toArray());
    }
}
