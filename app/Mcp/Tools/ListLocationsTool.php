<?php

namespace App\Mcp\Tools;

use App\Models\Location;
use Illuminate\Auth\Access\AuthorizationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ListLocationsTool extends Tool
{
    protected string $description = '列出所有地點（起點/目的地）。需要 clients:read ability。';

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('clients:read')) {
            throw new AuthorizationException('Token does not have clients:read ability.');
        }

        return Response::json(Location::all(['id', 'name'])->toArray());
    }
}
