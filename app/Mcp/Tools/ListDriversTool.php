<?php

namespace App\Mcp\Tools;

use App\Models\Driver;
use Illuminate\Auth\Access\AuthorizationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ListDriversTool extends Tool
{
    protected string $description = '列出所有司機。需要 clients:read ability。';

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('clients:read')) {
            throw new AuthorizationException('Token does not have clients:read ability.');
        }

        return Response::json(Driver::all(['id', 'name', 'phone'])->toArray());
    }
}
