<?php

use App\Mcp\Servers\InvoiceServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', InvoiceServer::class)->middleware(['auth:sanctum']);
