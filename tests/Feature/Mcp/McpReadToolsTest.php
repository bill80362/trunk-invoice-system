<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Servers\InvoiceServer;
use App\Mcp\Tools\GetInvoiceTool;
use App\Mcp\Tools\GetSettingTool;
use App\Mcp\Tools\ListCarrierTypesTool;
use App\Mcp\Tools\ListClientsTool;
use App\Mcp\Tools\ListDriversTool;
use App\Mcp\Tools\ListFreightRatesTool;
use App\Mcp\Tools\ListInvoicesTool;
use App\Mcp\Tools\ListInvoiceTripsTool;
use App\Mcp\Tools\ListLocationsTool;
use App\Models\CarrierType;
use App\Models\Client;
use App\Models\Driver;
use App\Models\FreightRate;
use App\Models\Invoice;
use App\Models\InvoiceTrip;
use App\Models\InvoiceTripStop;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class McpReadToolsTest extends TestCase
{
    use RefreshDatabase;

    private function actingWithAbilities(array $abilities): User
    {
        $user = User::factory()->create();
        $token = $user->createToken('test', $abilities);
        $user->withAccessToken($token->accessToken);

        return $user;
    }

    public function test_list_clients_returns_all_clients(): void
    {
        Client::factory()->count(3)->create();
        $user = $this->actingWithAbilities(['clients:read']);

        $response = InvoiceServer::actingAs($user)
            ->tool(ListClientsTool::class);

        $response->assertOk()->assertSee(Client::first()->name);
    }

    public function test_list_clients_requires_ability(): void
    {
        $user = $this->actingWithAbilities([]);

        $response = InvoiceServer::actingAs($user)
            ->tool(ListClientsTool::class);

        $response->assertHasErrors(['clients:read']);
    }

    public function test_list_invoices_returns_invoices(): void
    {
        $client = Client::factory()->create(['name' => 'Invoice Client']);
        Invoice::factory()->create(['client_id' => $client->id, 'year' => 2025, 'month' => 1]);
        Invoice::factory()->create(['client_id' => $client->id, 'year' => 2025, 'month' => 2]);
        $user = $this->actingWithAbilities(['invoices:read']);

        $response = InvoiceServer::actingAs($user)
            ->tool(ListInvoicesTool::class);

        $response->assertOk()->assertSee('Invoice Client');
    }

    public function test_list_invoices_filters_by_client_id(): void
    {
        $clientA = Client::factory()->create(['name' => 'Client Alpha']);
        $clientB = Client::factory()->create(['name' => 'Client Beta']);
        Invoice::factory()->create(['client_id' => $clientA->id, 'year' => 2025, 'month' => 1]);
        Invoice::factory()->create(['client_id' => $clientB->id, 'year' => 2025, 'month' => 2]);
        $user = $this->actingWithAbilities(['invoices:read']);

        $response = InvoiceServer::actingAs($user)
            ->tool(ListInvoicesTool::class, ['client_id' => $clientA->id]);

        $response->assertOk()->assertSee('Client Alpha')->assertDontSee('Client Beta');
    }

    public function test_list_invoices_requires_ability(): void
    {
        $user = $this->actingWithAbilities([]);

        InvoiceServer::actingAs($user)
            ->tool(ListInvoicesTool::class)
            ->assertHasErrors(['invoices:read']);
    }

    public function test_get_invoice_returns_full_invoice_with_trips(): void
    {
        $origin = Location::factory()->create();
        $destination = Location::factory()->create();
        $driver = Driver::factory()->create();
        $carrierType = CarrierType::factory()->create();
        $invoice = Invoice::factory()->create();
        $trip = InvoiceTrip::factory()->create([
            'invoice_id' => $invoice->id,
            'origin_id' => $origin->id,
            'driver_id' => $driver->id,
            'carrier_type_id' => $carrierType->id,
        ]);
        InvoiceTripStop::create(['invoice_trip_id' => $trip->id, 'location_id' => $destination->id, 'sequence' => 1]);

        $user = $this->actingWithAbilities(['invoices:read']);

        $response = InvoiceServer::actingAs($user)
            ->tool(GetInvoiceTool::class, ['invoice_id' => $invoice->id]);

        $response->assertOk()->assertSee($origin->name);
    }

    public function test_get_invoice_returns_error_for_missing_invoice(): void
    {
        $user = $this->actingWithAbilities(['invoices:read']);

        InvoiceServer::actingAs($user)
            ->tool(GetInvoiceTool::class, ['invoice_id' => 99999])
            ->assertHasErrors(['not found']);
    }

    public function test_list_invoice_trips_returns_trips_for_invoice(): void
    {
        $invoice = Invoice::factory()->create();
        InvoiceTrip::factory()->count(2)->create(['invoice_id' => $invoice->id]);
        $user = $this->actingWithAbilities(['trips:read']);

        InvoiceServer::actingAs($user)
            ->tool(ListInvoiceTripsTool::class, ['invoice_id' => $invoice->id])
            ->assertOk();
    }

    public function test_list_invoice_trips_requires_ability(): void
    {
        $invoice = Invoice::factory()->create();
        $user = $this->actingWithAbilities([]);

        InvoiceServer::actingAs($user)
            ->tool(ListInvoiceTripsTool::class, ['invoice_id' => $invoice->id])
            ->assertHasErrors(['trips:read']);
    }

    public function test_list_freight_rates_returns_rates(): void
    {
        FreightRate::factory()->count(2)->create();
        $user = $this->actingWithAbilities(['rates:read']);

        InvoiceServer::actingAs($user)
            ->tool(ListFreightRatesTool::class)
            ->assertOk();
    }

    public function test_list_locations_returns_all_locations(): void
    {
        Location::factory()->count(3)->create();
        $user = $this->actingWithAbilities(['clients:read']);

        InvoiceServer::actingAs($user)
            ->tool(ListLocationsTool::class)
            ->assertOk();
    }

    public function test_list_drivers_returns_all_drivers(): void
    {
        Driver::factory()->count(2)->create();
        $user = $this->actingWithAbilities(['clients:read']);

        InvoiceServer::actingAs($user)
            ->tool(ListDriversTool::class)
            ->assertOk();
    }

    public function test_list_carrier_types_returns_all_carrier_types(): void
    {
        CarrierType::factory()->count(2)->create();
        $user = $this->actingWithAbilities(['clients:read']);

        InvoiceServer::actingAs($user)
            ->tool(ListCarrierTypesTool::class)
            ->assertOk();
    }

    public function test_get_setting_returns_value(): void
    {
        Setting::set('issuer_name', 'Test Company');
        $user = $this->actingWithAbilities(['settings:read']);

        InvoiceServer::actingAs($user)
            ->tool(GetSettingTool::class, ['key' => 'issuer_name'])
            ->assertOk()
            ->assertSee('Test Company');
    }

    public function test_get_setting_requires_ability(): void
    {
        $user = $this->actingWithAbilities([]);

        InvoiceServer::actingAs($user)
            ->tool(GetSettingTool::class, ['key' => 'issuer_name'])
            ->assertHasErrors(['settings:read']);
    }
}
