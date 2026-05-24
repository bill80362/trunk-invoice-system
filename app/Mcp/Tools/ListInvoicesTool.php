<?php

namespace App\Mcp\Tools;

use App\Models\Invoice;
use Illuminate\Auth\Access\AuthorizationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ListInvoicesTool extends Tool
{
    protected string $description = '列出請款單，可依 client_id、year、month、status 篩選。需要 invoices:read ability。';

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('invoices:read')) {
            throw new AuthorizationException('Token does not have invoices:read ability.');
        }

        $query = Invoice::with('client:id,name');

        if ($clientId = $request->get('client_id')) {
            $query->where('client_id', $clientId);
        }

        if ($year = $request->get('year')) {
            $query->where('year', $year);
        }

        if ($month = $request->get('month')) {
            $query->where('month', $month);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $invoices = $query->orderByDesc('year')->orderByDesc('month')->get([
            'id', 'client_id', 'year', 'month', 'invoice_number',
            'status', 'total_amount', 'confirmed_at', 'created_at',
        ]);

        return Response::json($invoices->toArray());
    }
}
