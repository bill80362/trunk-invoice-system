<?php

namespace App\Mcp\Tools;

use App\Models\Client;
use Illuminate\Auth\Access\AuthorizationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ListClientsTool extends Tool
{
    protected string $description = '列出所有客戶（貨主）。需要 clients:read ability。';

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('clients:read')) {
            throw new AuthorizationException('Token does not have clients:read ability.');
        }

        $clients = Client::all(['id', 'name', 'contact', 'phone', 'address']);

        return Response::json($clients->toArray());
    }
}
