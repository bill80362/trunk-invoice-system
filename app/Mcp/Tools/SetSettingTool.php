<?php

namespace App\Mcp\Tools;

use App\Models\Setting;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class SetSettingTool extends Tool
{
    protected string $description = '修改系統設定值（key/value）。需要 settings:write ability。';

    public function schema(JsonSchema $schema): array
    {
        return [
            'key' => $schema->string()->required()->description('設定鍵名'),
            'value' => $schema->string()->required()->description('設定值'),
        ];
    }

    public function handle(Request $request): Response
    {
        if (! $request->user()?->tokenCan('settings:write')) {
            throw new AuthorizationException('Token does not have settings:write ability.');
        }

        $data = $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'required',
        ]);

        Setting::set($data['key'], $data['value']);

        return Response::json(['success' => true, 'key' => $data['key'], 'value' => $data['value']]);
    }
}
