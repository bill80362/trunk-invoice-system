<?php

namespace App\Mcp\Tools;

use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class CreateInvoiceTool extends Tool
{
    protected string $description = '建立新的草稿請款單，自動帶入發票抬頭預設值。需要 invoices:write ability。';

    public function schema(JsonSchema $schema): array
    {
        return [
            'client_id' => $schema->integer()->required()->description('客戶 ID'),
            'year' => $schema->integer()->required()->description('請款年份'),
            'month' => $schema->integer()->required()->description('請款月份（1-12）'),
            'invoice_number' => $schema->string()->nullable()->description('請款單號（選填）'),
        ];
    }

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('invoices:write')) {
            throw new AuthorizationException('Token does not have invoices:write ability.');
        }

        $data = $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'invoice_number' => 'nullable|string|max:255',
        ]);

        $invoice = Invoice::create([
            'client_id' => $data['client_id'],
            'year' => $data['year'],
            'month' => $data['month'],
            'invoice_number' => $data['invoice_number'] ?? null,
            'issuer_name' => Setting::get('issuer_name', ''),
            'issuer_address' => Setting::get('issuer_address', ''),
            'issuer_phone' => Setting::get('issuer_phone', ''),
            'total_amount' => 0,
            'status' => 'draft',
        ]);

        return Response::json($invoice->load('client:id,name')->toArray());
    }
}
