<?php

namespace App\Mcp\Tools;

use App\Models\Invoice;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ConfirmInvoiceTool extends Tool
{
    protected string $description = '確認請款單（將狀態改為 confirmed 並記錄確認時間），已確認的請款單不可重複確認。需要 invoices:write ability。';

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

        if ($invoice->isConfirmed()) {
            return Response::error('請款單已是 confirmed 狀態。');
        }

        $invoice->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        return Response::json($invoice->load('client:id,name')->toArray());
    }
}
