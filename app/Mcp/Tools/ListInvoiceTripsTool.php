<?php

namespace App\Mcp\Tools;

use App\Models\InvoiceTrip;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ListInvoiceTripsTool extends Tool
{
    protected string $description = '列出指定請款單的所有行程明細，依 sequence/date 排序。需要 trips:read ability。';

    public function schema(JsonSchema $schema): array
    {
        return [
            'invoice_id' => $schema->integer()->required()->description('請款單 ID'),
        ];
    }

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('trips:read')) {
            throw new AuthorizationException('Token does not have trips:read ability.');
        }

        $request->validate(['invoice_id' => 'required|integer']);

        $trips = InvoiceTrip::with([
            'origin:id,name',
            'driver:id,name',
            'carrierType:id,name',
            'invoiceTripStops.location:id,name',
        ])
            ->where('invoice_id', $request->get('invoice_id'))
            ->orderBy('sequence')
            ->orderBy('date')
            ->get();

        return Response::json($trips->toArray());
    }
}
