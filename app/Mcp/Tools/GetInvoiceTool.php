<?php

namespace App\Mcp\Tools;

use App\Models\Invoice;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GetInvoiceTool extends Tool
{
    protected string $description = '取得單張請款單的完整資料，含行程明細與目的地站點。需要 invoices:read ability。';

    public function schema(JsonSchema $schema): array
    {
        return [
            'invoice_id' => $schema->integer()->required()->description('請款單 ID'),
        ];
    }

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('invoices:read')) {
            throw new AuthorizationException('Token does not have invoices:read ability.');
        }

        $request->validate(['invoice_id' => 'required|integer']);

        $invoice = Invoice::with([
            'client:id,name',
            'invoiceTrips' => fn ($q) => $q->orderBy('sequence')->orderBy('date'),
            'invoiceTrips.origin:id,name',
            'invoiceTrips.driver:id,name',
            'invoiceTrips.carrierType:id,name',
            'invoiceTrips.invoiceTripStops.location:id,name',
        ])->find($request->get('invoice_id'));

        if (! $invoice) {
            return Response::error('Invoice not found.');
        }

        return Response::json($invoice->toArray());
    }
}
