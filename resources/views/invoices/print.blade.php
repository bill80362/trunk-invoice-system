<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>貨運請款單 - {{ $invoice->client->name }} {{ $invoice->year }}年{{ $invoice->month }}月</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans TC', sans-serif;
            font-size: 14px;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }

        .draft-badge {
            color: #dc2626;
            font-size: 14px;
            font-weight: normal;
            margin-left: 10px;
            border: 2px solid #dc2626;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .info-block p {
            margin-bottom: 4px;
        }

        .info-label {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #333;
            padding: 8px 12px;
            text-align: left;
        }

        th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-align: center;
        }

        td.number {
            text-align: right;
        }

        td.center {
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            font-size: 16px;
        }

        .total-row td {
            border-top: 2px solid #333;
        }

        .print-button {
            text-align: center;
            margin-bottom: 20px;
        }

        .print-button button {
            padding: 10px 30px;
            font-size: 16px;
            cursor: pointer;
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
        }

        .print-button button:hover {
            background-color: #1d4ed8;
        }

        @media print {
            .print-button {
                display: none !important;
            }

            body {
                padding: 0;
            }

            @page {
                margin: 15mm;
            }
        }
    </style>
</head>
<body>
    <div class="print-button">
        <button onclick="window.print()">列印</button>
    </div>

    <div class="header">
        <h1>
            貨運請款單
            @if($invoice->status === 'draft')
                <span class="draft-badge">草稿</span>
            @endif
        </h1>
    </div>

    <div class="info-section">
        <div class="info-block">
            <p><span class="info-label">請款方：</span>{{ $invoice->issuer_name }}</p>
            @if($invoice->issuer_address)
                <p><span class="info-label">地址：</span>{{ $invoice->issuer_address }}</p>
            @endif
            @if($invoice->issuer_phone)
                <p><span class="info-label">電話：</span>{{ $invoice->issuer_phone }}</p>
            @endif
        </div>
        <div class="info-block" style="text-align: right;">
            <p><span class="info-label">請款對象：</span>{{ $invoice->client->name }}</p>
            <p><span class="info-label">月份：</span>{{ $invoice->year }}年{{ $invoice->month }}月</p>
            @if($invoice->invoice_number)
                <p><span class="info-label">單號：</span>{{ $invoice->invoice_number }}</p>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">日期</th>
                <th style="width: 30%;">行程明細</th>
                <th style="width: 12%;">托運方式</th>
                <th style="width: 12%;">重量</th>
                <th style="width: 14%;">運費</th>
                <th style="width: 10%;">司機</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->invoiceTrips as $trip)
                <tr>
                    <td class="center">{{ $trip->date->format('m/d') }}</td>
                    <td>
                        {{ $trip->origin->name }} → {{ $trip->invoiceTripStops->pluck('location.name')->implode(' → ') }}
                    </td>
                    <td class="center">{{ $trip->carrierType->name }}</td>
                    <td class="center">{{ $trip->weight ?? '' }}</td>
                    <td class="number">{{ (float) $trip->freight_fee !== 0.0 ? '$' . number_format((float) $trip->freight_fee, 0) : '' }}</td>
                    <td class="center">{{ $trip->driver->name }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">合計</td>
                <td class="number">${{ number_format((float) $invoice->total_amount, 0) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
