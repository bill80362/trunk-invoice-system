<?php

namespace App\Mcp\Tools;

use App\Models\CarrierType;
use Illuminate\Auth\Access\AuthorizationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ListCarrierTypesTool extends Tool
{
    protected string $description = '列出所有托運方式。需要 clients:read ability。';

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('clients:read')) {
            throw new AuthorizationException('Token does not have clients:read ability.');
        }

        return Response::json(CarrierType::all(['id', 'name'])->toArray());
    }
}
