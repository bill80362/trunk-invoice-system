<?php

namespace Tests\Feature\Mcp;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class McpAuthTest extends TestCase
{
    use RefreshDatabase;

    private function mcpPayload(): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => '1',
            'method' => 'tools/call',
            'params' => [
                'name' => 'list-clients-tool',
                'arguments' => [],
            ],
        ];
    }

    public function test_mcp_endpoint_returns_401_without_token(): void
    {
        $response = $this->postJson('/mcp', $this->mcpPayload());

        $response->assertStatus(401);
    }

    public function test_mcp_endpoint_returns_401_with_invalid_token(): void
    {
        $response = $this->withToken('invalid-token-here')
            ->postJson('/mcp', $this->mcpPayload());

        $response->assertStatus(401);
    }

    public function test_mcp_endpoint_accepts_valid_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test', ['clients:read']);

        $response = $this->withToken($token->plainTextToken)
            ->postJson('/mcp', $this->mcpPayload());

        $response->assertSuccessful();
    }

    public function test_mcp_tool_returns_error_without_required_ability(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test', []);

        $response = $this->withToken($token->plainTextToken)
            ->postJson('/mcp', $this->mcpPayload());

        $response->assertSuccessful();
        $response->assertJsonPath('result.isError', true);
        $response->assertJsonPath('result.content.0.text', fn ($msg) => str_contains($msg, 'clients:read'));
    }
}
