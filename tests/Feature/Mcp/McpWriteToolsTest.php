<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Servers\InvoiceServer;
use App\Mcp\Tools\AddInvoiceTripTool;
use App\Mcp\Tools\CreateInvoiceTool;
use App\Mcp\Tools\DeleteInvoiceTripTool;
use App\Mcp\Tools\SetSettingTool;
use App\Mcp\Tools\UpdateFreightRateTool;
use App\Mcp\Tools\UpdateInvoiceTripTool;
use App\Models\CarrierType;
use App\Models\Client;
use App\Models\Driver;
use App\Models\FreightRate;
use App\Models\Invoice;
use App\Models\InvoiceTrip;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class McpWriteToolsTest extends TestCase
{
    use RefreshDatabase;

    private function actingWithAbilities(array $abilities): User
    {
        $user = User::factory()->create();
        $token = $user->createToken('test', $abilities);
        $user->withAccessToken($token->accessToken);

        return $user;
    }

    public function test_create_invoice_creates_draft_with_issuer_defaults(): void
    {
        Setting::set('issuer_name', 'My Company');
        Setting::set('issuer_address', '123 Main St');
        Setting::set('issuer_phone', '02-1234-5678');
        $client = Client::factory()->create();
        $user = $this->actingWithAbilities(['invoices:write']);

        InvoiceServer::actingAs($user)
            ->tool(CreateInvoiceTool::class, [
                'client_id' => $client->id,
                'year' => 2025,
                'month' => 6,
            ])
            ->assertOk()
            ->assertSee('draft')
            ->assertSee('My Company');

        $this->assertDatabaseHas('invoices', [
            'client_id' => $client->id,
            'year' => 2025,
            'month' => 6,
            'status' => 'draft',
            'issuer_name' => 'My Company',
        ]);
    }

    public function test_create_invoice_requires_ability(): void
    {
        $client = Client::factory()->create();
        $user = $this->actingWithAbilities([]);

        InvoiceServer::actingAs($user)
            ->tool(CreateInvoiceTool::class, ['client_id' => $client->id, 'year' => 2025, 'month' => 1])
            ->assertHasErrors(['invoices:write']);
    }

    public function test_add_invoice_trip_creates_trip_and_recalculates_total(): void
    {
        $invoice = Invoice::factory()->create(['total_amount' => 0]);
        $origin = Location::factory()->create();
        $destination = Location::factory()->create();
        $driver = Driver::factory()->create();
        $carrierType = CarrierType::factory()->create();
        $user = $this->actingWithAbilities(['trips:write']);

        InvoiceServer::actingAs($user)
            ->tool(AddInvoiceTripTool::class, [
                'invoice_id' => $invoice->id,
                'date' => '2025-06-01',
                'origin_id' => $origin->id,
                'driver_id' => $driver->id,
                'carrier_type_id' => $carrierType->id,
                'freight_fee' => 1500,
                'stops' => [$destination->id],
            ])
            ->assertOk();

        $invoice->refresh();
        $this->assertEquals(1500, $invoice->total_amount);
        $this->assertDatabaseHas('invoice_trips', ['invoice_id' => $invoice->id, 'freight_fee' => 1500]);
        $this->assertDatabaseHas('invoice_trip_stops', ['location_id' => $destination->id]);
    }

    public function test_add_invoice_trip_blocked_when_confirmed(): void
    {
        $invoice = Invoice::factory()->confirmed()->create();
        $origin = Location::factory()->create();
        $destination = Location::factory()->create();
        $driver = Driver::factory()->create();
        $carrierType = CarrierType::factory()->create();
        $user = $this->actingWithAbilities(['trips:write']);

        InvoiceServer::actingAs($user)
            ->tool(AddInvoiceTripTool::class, [
                'invoice_id' => $invoice->id,
                'date' => '2025-06-01',
                'origin_id' => $origin->id,
                'driver_id' => $driver->id,
                'carrier_type_id' => $carrierType->id,
                'freight_fee' => 1000,
                'stops' => [$destination->id],
            ])
            ->assertHasErrors(['已確認']);
    }

    public function test_update_invoice_trip_updates_fields(): void
    {
        $invoice = Invoice::factory()->create();
        $trip = InvoiceTrip::factory()->create(['invoice_id' => $invoice->id, 'freight_fee' => 500]);
        $user = $this->actingWithAbilities(['trips:write']);

        InvoiceServer::actingAs($user)
            ->tool(UpdateInvoiceTripTool::class, [
                'trip_id' => $trip->id,
                'freight_fee' => 800,
            ])
            ->assertOk();

        $this->assertDatabaseHas('invoice_trips', ['id' => $trip->id, 'freight_fee' => 800]);
    }

    public function test_update_invoice_trip_blocked_when_confirmed(): void
    {
        $invoice = Invoice::factory()->confirmed()->create();
        $trip = InvoiceTrip::factory()->create(['invoice_id' => $invoice->id]);
        $user = $this->actingWithAbilities(['trips:write']);

        InvoiceServer::actingAs($user)
            ->tool(UpdateInvoiceTripTool::class, [
                'trip_id' => $trip->id,
                'freight_fee' => 999,
            ])
            ->assertHasErrors(['已確認']);
    }

    public function test_delete_invoice_trip_removes_trip_and_recalculates(): void
    {
        $invoice = Invoice::factory()->create(['total_amount' => 1000]);
        $trip = InvoiceTrip::factory()->create(['invoice_id' => $invoice->id, 'freight_fee' => 1000]);
        $user = $this->actingWithAbilities(['trips:write']);

        InvoiceServer::actingAs($user)
            ->tool(DeleteInvoiceTripTool::class, ['trip_id' => $trip->id])
            ->assertOk();

        $this->assertDatabaseMissing('invoice_trips', ['id' => $trip->id]);
        $invoice->refresh();
        $this->assertEquals(0, $invoice->total_amount);
    }

    public function test_delete_invoice_trip_blocked_when_confirmed(): void
    {
        $invoice = Invoice::factory()->confirmed()->create();
        $trip = InvoiceTrip::factory()->create(['invoice_id' => $invoice->id]);
        $user = $this->actingWithAbilities(['trips:write']);

        InvoiceServer::actingAs($user)
            ->tool(DeleteInvoiceTripTool::class, ['trip_id' => $trip->id])
            ->assertHasErrors(['已確認']);
    }

    public function test_update_freight_rate_updates_base_price(): void
    {
        $rate = FreightRate::factory()->create(['base_price' => 100]);
        $user = $this->actingWithAbilities(['rates:write']);

        InvoiceServer::actingAs($user)
            ->tool(UpdateFreightRateTool::class, [
                'rate_id' => $rate->id,
                'base_price' => 250,
            ])
            ->assertOk();

        $this->assertDatabaseHas('freight_rates', ['id' => $rate->id, 'base_price' => 250]);
    }

    public function test_set_setting_updates_value(): void
    {
        $user = $this->actingWithAbilities(['settings:write']);

        InvoiceServer::actingAs($user)
            ->tool(SetSettingTool::class, ['key' => 'additional_stop_fee', 'value' => '200'])
            ->assertOk();

        $this->assertEquals('200', Setting::get('additional_stop_fee'));
    }

    public function test_set_setting_requires_ability(): void
    {
        $user = $this->actingWithAbilities([]);

        InvoiceServer::actingAs($user)
            ->tool(SetSettingTool::class, ['key' => 'additional_stop_fee', 'value' => '200'])
            ->assertHasErrors(['settings:write']);
    }
}
