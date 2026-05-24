<?php

namespace App\Mcp\Tools;

use App\Models\InvoiceTrip;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class DeleteInvoiceTripTool extends Tool
{
    protected string $description = '刪除指定行程明細及其所有站點（所屬請款單 confirmed 時不可刪除）。需要 trips:write ability。';

    public function schema(JsonSchema $schema): array
    {
        return [
            'trip_id' => $schema->integer()->required()->description('行程明細 ID'),
        ];
    }

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('trips:write')) {
            throw new AuthorizationException('Token does not have trips:write ability.');
        }

        $request->validate(['trip_id' => 'required|integer|exists:invoice_trips,id']);

        $trip = InvoiceTrip::with('invoice')->findOrFail($request->get('trip_id'));

        if ($trip->invoice->isConfirmed()) {
            return Response::error('請款單已確認，無法刪除行程。');
        }

        $invoice = $trip->invoice;
        $trip->delete();
        $invoice->recalculateTotal();

        return Response::json(['success' => true, 'message' => '行程已刪除。']);
    }
}
