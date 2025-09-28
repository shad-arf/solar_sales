<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $sale->code }} - Solar Sales</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">
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
        /* ensure right-to-left support if needed */
        [dir="rtl"] { direction: rtl; }
    </style>
</head>
<body>

    {{-- Download PDF Button --}}
    <div style="text-align: right; margin-bottom: 15px;">
        <button onclick="generatePDF()" style="padding: 8px 16px; font-size: 14px;">
            📄 Download PDF
        </button>
    </div>

    {{-- Invoice Content --}}
    <div id="invoice-wrapper" class="invoice-wrapper">
        {{-- Logo & Company Info --}}
        <table style="width: 100%; margin-bottom: 20px; border: none;">
            <tr>
                <td style="vertical-align: top; width: 50%; border: none;">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Company Logo" style="max-width: 150px;" crossorigin="anonymous">
                </td>
                <td style="vertical-align: top; text-align: right; width: 50%; border: none;">
                    <h2>{{ env('COMPANY_NAME', 'Hostyar') }}</h2>
                    <p>{{ env('ADDRESS', '') }}</p>
                    <p>{{ env('STREET_NAME', '') }}, {{ env('ADDRESS', '') }}</p>
                    <p>Phone: {{ env('PHONE', '') }}</p>
                </td>
            </tr>
        </table>

        {{-- Invoice Heading --}}
        <div class="text-center mb-4">
            <h1>INVOICE</h1>
            <p class="text-muted">Invoice #{{ $sale->code }}</p>
        </div>

        {{-- From / Bill To --}}
        <table style="width: 100%; margin-bottom: 20px; border: none;">
            <tr>
                <td style="vertical-align: top; width: 50%; border: none;">
                    <h5>From:</h5>
                    <strong>{{ env('COMPANY_NAME', 'Hostyar') }}</strong><br>
                         <strong>{{ env('COMPANY_NAME', 'Hostyar') }}</strong><br>
                        {{ env('STREET_NAME', '') }}<br>
                        Phone: {{ env('PHONE', '') }}<br>
                        Email: {{ env('EMAIL', '') }}
            </td>
                <td style="vertical-align: top; text-align: right; width: 50%; border: none;">
                    <h5>Bill To:</h5>
                    <strong>{{ $sale->customer->name ?? '— Deleted Customer —' }}</strong><br>
                    {{ $sale->customer->address ?? '' }}<br>
                    Phone: {{ $sale->customer->phone ?? '' }}<br>
                    Email: {{ $sale->customer->email ?? '' }}<br><br>
                    <strong>Invoice Date:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d') }}<br>
                    <strong>Invoice #:</strong> {{ $sale->code }}
                </td>
            </tr>
        </table>

        {{-- Line Items --}}
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
                        <td class="text-end">{{ number_format($item->unit_price, 2) }}$</td>
                        <td class="text-end">{{ number_format($item->line_discount, 2) }}%</td>
                        <td class="text-end">{{ number_format($lineTotal, 2) }}$</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Totals --}}
        @php
            $discountTotal = (float) $sale->discount;
            $totalAmount   = (float) $sale->total;
            $paidAmount    = (float) $sale->paid_amount;
            $outstanding   = max(0, $totalAmount - $paidAmount);
        @endphp

        <table class="borderless" style="width: 40%; float: right;">
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td class="text-end">{{ number_format($subtotal, 2) }}$</td>
            </tr>
            @if($discountTotal > 0)
            <tr>
                <td><strong>Discount:</strong></td>
                <td class="text-end">- {{ number_format($discountTotal, 2) }}$</td>
            </tr>
            @endif
            <tr>
                <td><strong>Total:</strong></td>
                <td class="text-end">{{ number_format($totalAmount, 2) }}$</td>
            </tr>
            <tr>
                <td><strong>Paid:</strong></td>
                <td class="text-end">{{ number_format($paidAmount, 2) }}$</td>
            </tr>
            <tr>
                <td><strong>Outstanding:</strong></td>
                <td class="text-end {{ $outstanding > 0 ? 'text-danger' : '' }}">
                    {{ number_format($outstanding, 2) }}$
                </td>
            </tr>
        </table>
        <div style="clear: both;"></div>

        <p class="text-muted text-center mt-5">
            Thank you for your business! Contact us at info@yourcompany.com.
        </p>
    </div>

    {{-- Include html2canvas & jsPDF --}}
    <script src="https://unpkg.com/html2canvas@1.0.0-rc.5/dist/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        async function generatePDF() {
            // Grab the invoice element
            const invoice = document.getElementById('invoice-wrapper');

            // Render to canvas with better image handling
            const canvas = await html2canvas(invoice, {
                scale: 2,
                allowTaint: true,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff'
            });
            const imgData = canvas.toDataURL('image/png');

            // Create jsPDF instance
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4'
            });

            // Calculate dimensions
            const pageWidth = pdf.internal.pageSize.getWidth();
            const imgProps  = pdf.getImageProperties(imgData);
            const pdfHeight = (imgProps.height * pageWidth) / imgProps.width;

            // Add image & save
            pdf.addImage(imgData, 'PNG', 0, 0, pageWidth, pdfHeight);
            pdf.save(`invoice-{{ $sale->code }}.pdf`);
        }
    </script>
</body>
</html>
