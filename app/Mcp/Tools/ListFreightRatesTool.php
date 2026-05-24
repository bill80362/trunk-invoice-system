<?php

namespace App\Mcp\Tools;

use App\Models\FreightRate;
use Illuminate\Auth\Access\AuthorizationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ListFreightRatesTool extends Tool
{
    protected string $description = '列出費率表，可依 origin_id、carrier_type_id 篩選。需要 rates:read ability。';

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('rates:read')) {
            throw new AuthorizationException('Token does not have rates:read ability.');
        }

        $query = FreightRate::with([
            'origin:id,name',
            'destination:id,name',
            'carrierType:id,name',
        ]);

        if ($originId = $request->get('origin_id')) {
            $query->where('origin_id', $originId);
        }

        if ($carrierTypeId = $request->get('carrier_type_id')) {
            $query->where('carrier_type_id', $carrierTypeId);
        }

        return Response::json($query->get()->toArray());
    }
}
