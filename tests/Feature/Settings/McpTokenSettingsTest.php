<?php

namespace Tests\Feature\Settings;

use App\Filament\Pages\Settings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Livewire\Livewire;
use Tests\TestCase;

class McpTokenSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_mount_loads_empty_abilities_when_no_token_exists(): void
    {
        Livewire::test(Settings::class)
            ->assertSet('data.mcp_abilities', []);
    }

    public function test_mount_loads_existing_token_abilities(): void
    {
        $this->user->createToken('mcp-system-token', ['invoices:read', 'trips:read']);

        Livewire::test(Settings::class)
            ->assertSet('data.mcp_abilities', ['invoices:read', 'trips:read']);
    }

    public function test_save_creates_new_token_when_none_exists_and_shows_plain_text(): void
    {
        $component = Livewire::test(Settings::class)
            ->set('data.mcp_abilities', ['clients:read', 'invoices:read'])
            ->call('save');

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
            'name' => 'mcp-system-token',
        ]);

        $token = $this->user->tokens()->where('name', 'mcp-system-token')->first();
        $this->assertEquals(['clients:read', 'invoices:read'], $token->abilities);

        $this->assertNotEmpty($component->get('data.newly_generated_token'));
    }

    public function test_save_updates_abilities_without_recreating_token(): void
    {
        $original = $this->user->createToken('mcp-system-token', ['clients:read']);
        $originalId = $original->accessToken->id;

        Livewire::test(Settings::class)
            ->set('data.mcp_abilities', ['clients:read', 'invoices:read', 'trips:write'])
            ->call('save');

        $token = PersonalAccessToken::find($originalId);
        $this->assertNotNull($token, 'Token should NOT be deleted');
        $this->assertEquals(['clients:read', 'invoices:read', 'trips:write'], $token->abilities);
        $this->assertEquals(1, $this->user->tokens()->count());
    }

    public function test_save_does_not_expose_token_when_it_already_exists(): void
    {
        $this->user->createToken('mcp-system-token', ['clients:read']);

        $component = Livewire::test(Settings::class)
            ->set('data.mcp_abilities', ['clients:read', 'invoices:read'])
            ->call('save');

        $this->assertEmpty($component->get('data.newly_generated_token'));
    }

    public function test_regenerate_token_deletes_old_and_creates_new(): void
    {
        $original = $this->user->createToken('mcp-system-token', ['clients:read']);
        $originalId = $original->accessToken->id;

        $component = Livewire::test(Settings::class)
            ->set('data.mcp_abilities', ['invoices:read', 'trips:read'])
            ->call('regenerateToken');

        $this->assertNull(PersonalAccessToken::find($originalId));

        $newToken = $this->user->tokens()->where('name', 'mcp-system-token')->first();
        $this->assertNotNull($newToken);
        $this->assertEquals(['invoices:read', 'trips:read'], $newToken->abilities);
        $this->assertNotEmpty($component->get('data.newly_generated_token'));
        $this->assertEquals(1, $this->user->tokens()->count());
    }

    public function test_token_status_shows_not_created_when_no_token(): void
    {
        Livewire::test(Settings::class)
            ->assertSee('尚未建立');
    }

    public function test_token_status_shows_created_when_token_exists(): void
    {
        $this->user->createToken('mcp-system-token', ['clients:read']);

        Livewire::test(Settings::class)
            ->assertSee('已建立');
    }
}
