<?php

namespace App\Mcp\Tools;

use App\Models\Invoice;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class RecalculateInvoiceTotalTool extends Tool
{
    protected string $description = '重新計算請款單總金額（SUM 所有行程 freight_fee）並更新 total_amount。需要 invoices:write ability。';

    public function schema(JsonSchema $schema): array
    {
        return [
            'invoice_id' => $schema->integer()->required()->description('請款單 ID'),
        ];
    }

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('invoices:write')) {
            throw new AuthorizationException('Token does not have invoices:write ability.');
        }

        $request->validate(['invoice_id' => 'required|integer|exists:invoices,id']);

        $invoice = Invoice::findOrFail($request->get('invoice_id'));
        $invoice->recalculateTotal();

        return Response::json([
            'invoice_id' => $invoice->id,
            'total_amount' => (float) $invoice->total_amount,
        ]);
    }
}
