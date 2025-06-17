<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $sale->code }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            padding: 30px;
            color: #000;
        }
        .invoice-wrapper {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 20px;
        }
        h1, h2, h5 {
            margin: 0;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-muted { color: #777; }
        .borderless td { border: none !important; }
    </style>
</head>
<body>
    <div class="invoice-wrapper">
        {{-- Logo at the top center --}}
         <table style="width: 100%; margin-bottom: 20px; border: none;">
            <tr>
                <td  style="vertical-align: top; width: 50%; border: none;">
                    <img src="{{ public_path('images/logo.jpg') }}" alt="Company Logo" style="max-width: 150px;">
                </td>
                <td style="vertical-align: top;padding-top: text-align: right; width: 50%; border: none;">
                    <h2>Photon</h2>
                    <p>Main Street Mosul, Kahabt</p>
                    <p>Phone: (964) 7709647036</p>
                </td>

            </tr>

         </table>

        <div class="text-center mb-4">
            <h1>INVOICE</h1>
            <p class="text-muted">Invoice #{{ $sale->code }}</p>
        </div>

        <table style="width: 100%; margin-bottom: 20px; border: none;">
            <tr>
                <td style="vertical-align: top; width: 50%; border: none;">
                    <h5>From:</h5>
                    <strong>Photon</strong><br>
                    Main Street Mosul<br>
                    Kahabt<br>
                    Phone: (964) 7709647036<br>
                    Email: info@yourcompany.com
                </td>
                <td style="vertical-align: top; text-align: right; width: 50%; border: none;">
                    <h5>Bill To:</h5>
                    <strong>{{ $sale->customer->name ?? '— Deleted Customer —' }}</strong><br>
                    {{ $sale->customer->address ?? '' }}<br>
                    Phone: {{ $sale->customer->phone ?? '' }}<br>
                    Email: {{ $sale->customer->email ?? '' }}<br>
                    <br>
                    <strong>Invoice Date:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d') }}<br>
                    <strong>Invoice #:</strong> {{ $sale->code }}
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Discount</th>
                    <th class="text-end">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @php $subtotal = 0; @endphp
                @forelse($sale->orderItems as $index => $item)
                    @php
                        $lineTotal = (float) $item->line_total;
                        $subtotal += $lineTotal;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->item->name ?? 'Deleted Item' }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end">{{ number_format($item->line_discount, 2) }}</td>
                        <td class="text-end">{{ number_format($lineTotal, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @php
            $discountTotal = (float) $sale->discount;
            $totalAmount   = (float) $sale->total;
            $paidAmount    = (float) $sale->paid_amount;
            $outstanding   = max(0, $totalAmount - $paidAmount);
        @endphp

        <table class="borderless" style="width: 40%; float: right;">
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td class="text-end">${{ number_format($subtotal, 2) }}</td>
            </tr>
            @if($discountTotal > 0)
            <tr>
                <td><strong>Discount:</strong></td>
                <td class="text-end">- ${{ number_format($discountTotal, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td><strong>Total:</strong></td>
                <td class="text-end">${{ number_format($totalAmount, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Paid:</strong></td>
                <td class="text-end">${{ number_format($paidAmount, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Outstanding:</strong></td>
                <td class="text-end {{ $outstanding > 0 ? 'text-danger' : '' }}">
                    ${{ number_format($outstanding, 2) }}
                </td>
            </tr>
        </table>

        <div style="clear: both;"></div>

        <p class="text-muted text-center mt-5">
            Thank you for your business! Contact us at info@yourcompany.com.
        </p>
    </div>
</body>
</html>
