<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Servers\InvoiceServer;
use App\Mcp\Tools\CalculateFreightFeeTool;
use App\Mcp\Tools\ConfirmInvoiceTool;
use App\Mcp\Tools\RecalculateInvoiceTotalTool;
use App\Mcp\Tools\UnlockInvoiceTool;
use App\Models\CarrierType;
use App\Models\FreightRate;
use App\Models\Invoice;
use App\Models\InvoiceTrip;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class McpActionToolsTest extends TestCase
{
    use RefreshDatabase;

    private function actingWithAbilities(array $abilities): User
    {
        $user = User::factory()->create();
        $token = $user->createToken('test', $abilities);
        $user->withAccessToken($token->accessToken);

        return $user;
    }

    public function test_confirm_invoice_sets_status_and_confirmed_at(): void
    {
        $invoice = Invoice::factory()->create(['status' => 'draft', 'confirmed_at' => null]);
        $user = $this->actingWithAbilities(['invoices:write']);

        InvoiceServer::actingAs($user)
            ->tool(ConfirmInvoiceTool::class, ['invoice_id' => $invoice->id])
            ->assertOk()
            ->assertSee('confirmed');

        $invoice->refresh();
        $this->assertEquals('confirmed', $invoice->status);
        $this->assertNotNull($invoice->confirmed_at);
    }

    public function test_confirm_invoice_fails_if_already_confirmed(): void
    {
        $invoice = Invoice::factory()->confirmed()->create();
        $user = $this->actingWithAbilities(['invoices:write']);

        InvoiceServer::actingAs($user)
            ->tool(ConfirmInvoiceTool::class, ['invoice_id' => $invoice->id])
            ->assertHasErrors(['已是']);
    }

    public function test_confirm_invoice_requires_ability(): void
    {
        $invoice = Invoice::factory()->create();
        $user = $this->actingWithAbilities([]);

        InvoiceServer::actingAs($user)
            ->tool(ConfirmInvoiceTool::class, ['invoice_id' => $invoice->id])
            ->assertHasErrors(['invoices:write']);
    }

    public function test_unlock_invoice_sets_status_to_draft_and_clears_confirmed_at(): void
    {
        $invoice = Invoice::factory()->confirmed()->create();
        $user = $this->actingWithAbilities(['invoices:write']);

        InvoiceServer::actingAs($user)
            ->tool(UnlockInvoiceTool::class, ['invoice_id' => $invoice->id])
            ->assertOk()
            ->assertSee('draft');

        $invoice->refresh();
        $this->assertEquals('draft', $invoice->status);
        $this->assertNull($invoice->confirmed_at);
    }

    public function test_unlock_invoice_fails_if_not_confirmed(): void
    {
        $invoice = Invoice::factory()->create(['status' => 'draft']);
        $user = $this->actingWithAbilities(['invoices:write']);

        InvoiceServer::actingAs($user)
            ->tool(UnlockInvoiceTool::class, ['invoice_id' => $invoice->id])
            ->assertHasErrors(['並非']);
    }

    public function test_calculate_freight_fee_returns_breakdown(): void
    {
        $origin = Location::factory()->create();
        $dest1 = Location::factory()->create();
        $dest2 = Location::factory()->create();
        $carrierType = CarrierType::factory()->create();
        FreightRate::factory()->create([
            'origin_id' => $origin->id,
            'destination_id' => $dest1->id,
            'carrier_type_id' => $carrierType->id,
            'base_price' => 1000,
        ]);
        Setting::set('additional_stop_fee', 200);
        $user = $this->actingWithAbilities(['rates:read']);

        InvoiceServer::actingAs($user)
            ->tool(CalculateFreightFeeTool::class, [
                'origin_id' => $origin->id,
                'carrier_type_id' => $carrierType->id,
                'destination_ids' => [$dest1->id, $dest2->id],
            ])
            ->assertOk()
            ->assertSee('1200');
    }

    public function test_calculate_freight_fee_returns_zero_when_no_rate_found(): void
    {
        $origin = Location::factory()->create();
        $destination = Location::factory()->create();
        $carrierType = CarrierType::factory()->create();
        $user = $this->actingWithAbilities(['rates:read']);

        $response = InvoiceServer::actingAs($user)
            ->tool(CalculateFreightFeeTool::class, [
                'origin_id' => $origin->id,
                'carrier_type_id' => $carrierType->id,
                'destination_ids' => [$destination->id],
            ]);

        $response->assertOk()->assertSee('false');
    }

    public function test_recalculate_invoice_total_sums_trip_fees(): void
    {
        $invoice = Invoice::factory()->create(['total_amount' => 0]);
        InvoiceTrip::factory()->create(['invoice_id' => $invoice->id, 'freight_fee' => 300]);
        InvoiceTrip::factory()->create(['invoice_id' => $invoice->id, 'freight_fee' => 700]);
        $user = $this->actingWithAbilities(['invoices:write']);

        InvoiceServer::actingAs($user)
            ->tool(RecalculateInvoiceTotalTool::class, ['invoice_id' => $invoice->id])
            ->assertOk()
            ->assertSee('1000');

        $invoice->refresh();
        $this->assertEquals(1000, $invoice->total_amount);
    }

    public function test_recalculate_invoice_total_requires_ability(): void
    {
        $invoice = Invoice::factory()->create();
        $user = $this->actingWithAbilities([]);

        InvoiceServer::actingAs($user)
            ->tool(RecalculateInvoiceTotalTool::class, ['invoice_id' => $invoice->id])
            ->assertHasErrors(['invoices:write']);
    }
}
