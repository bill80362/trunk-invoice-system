<?php

namespace App\Http\Controllers;

use App\Models\Invoice;

class InvoiceController extends Controller
{
    public function print(Invoice $invoice)
    {
        $invoice->load([
            'client',
            'invoiceTrips' => fn ($q) => $q->orderBy('sequence')->orderBy('date'),
            'invoiceTrips.origin',
            'invoiceTrips.driver',
            'invoiceTrips.carrierType',
            'invoiceTrips.invoiceTripStops.location',
        ]);

        return view('invoices.print', compact('invoice'));
    }
}
