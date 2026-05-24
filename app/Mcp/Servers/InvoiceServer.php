<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\AddInvoiceTripTool;
use App\Mcp\Tools\CalculateFreightFeeTool;
use App\Mcp\Tools\ConfirmInvoiceTool;
use App\Mcp\Tools\CreateInvoiceTool;
use App\Mcp\Tools\DeleteInvoiceTripTool;
use App\Mcp\Tools\GetInvoiceTool;
use App\Mcp\Tools\GetSettingTool;
use App\Mcp\Tools\ListCarrierTypesTool;
use App\Mcp\Tools\ListClientsTool;
use App\Mcp\Tools\ListDriversTool;
use App\Mcp\Tools\ListFreightRatesTool;
use App\Mcp\Tools\ListInvoicesTool;
use App\Mcp\Tools\ListInvoiceTripsTool;
use App\Mcp\Tools\ListLocationsTool;
use App\Mcp\Tools\RecalculateInvoiceTotalTool;
use App\Mcp\Tools\SetSettingTool;
use App\Mcp\Tools\UnlockInvoiceTool;
use App\Mcp\Tools\UpdateFreightRateTool;
use App\Mcp\Tools\UpdateInvoiceTripTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Invoice Server')]
#[Version('1.0.0')]
#[Instructions('貨運請款單系統 MCP Server。支援查詢客戶、請款單、行程明細、費率；以及建立請款單、管理行程、確認/解除鎖定等業務操作。所有寫入操作需對應 Token Ability。')]
class InvoiceServer extends Server
{
    protected array $tools = [
        // Read Tools
        ListClientsTool::class,
        ListInvoicesTool::class,
        GetInvoiceTool::class,
        ListInvoiceTripsTool::class,
        ListFreightRatesTool::class,
        ListLocationsTool::class,
        ListDriversTool::class,
        ListCarrierTypesTool::class,
        GetSettingTool::class,
        // Write Tools
        CreateInvoiceTool::class,
        AddInvoiceTripTool::class,
        UpdateInvoiceTripTool::class,
        DeleteInvoiceTripTool::class,
        UpdateFreightRateTool::class,
        SetSettingTool::class,
        // Action Tools
        ConfirmInvoiceTool::class,
        UnlockInvoiceTool::class,
        CalculateFreightFeeTool::class,
        RecalculateInvoiceTotalTool::class,
    ];

    protected array $resources = [];

    protected array $prompts = [];
}
