<?php

namespace App\Mcp\Tools;

use App\Models\Setting;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GetSettingTool extends Tool
{
    protected string $description = '取得指定 key 的系統設定值。需要 settings:read ability。';

    public function schema(JsonSchema $schema): array
    {
        return [
            'key' => $schema->string()->required()->description('設定鍵名'),
        ];
    }

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('settings:read')) {
            throw new AuthorizationException('Token does not have settings:read ability.');
        }

        $request->validate(['key' => 'required|string']);

        $value = Setting::get($request->get('key'));

        return Response::json(['key' => $request->get('key'), 'value' => $value]);
    }
}
