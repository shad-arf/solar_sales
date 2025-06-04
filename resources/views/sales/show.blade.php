@extends('layouts.admin')

@push('styles')
<style>
    .invoice-wrapper {
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 30px;
        margin: 20px 0;
    }
    .table {
        border-collapse: collapse;
        width: 100%;
    }
    .table th, .table td {
        border: 1px solid #000;
        padding: 8px;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Invoice: {{ $sale->code }}</h2>
        <a href="{{ route('sales.downloadPdf', $sale->id) }}" target="_blank" class="btn btn-danger">
            <i class="bi bi-file-earmark-pdf"></i> Download PDF
        </a>
    </div>

    {{-- Invoice Content --}}
    <div class="invoice-wrapper" id="invoice-content">
        <div class="text-center mb-4">
            <h1 class="mb-1">INVOICE</h1>
            <p class="text-muted">Invoice #{{ $sale->code }}</p>
        </div>

        <div class="row mb-4">
            <div class="col-sm-6">
                <h5>From:</h5>
                <strong>Photon</strong><br>
                Main Street Mosul<br>
                Kahabt<br>
                Phone: (964) 7709647036<br>
                Email: info@yourcompany.com
            </div>
            <div class="col-sm-6 text-sm-end">
                <h5>Bill To:</h5>
                <strong>{{ $sale->customer->name ?? '— Deleted Customer —' }}</strong><br>
                {{ $sale->customer->address ?? '' }}<br>
                Phone: {{ $sale->customer->phone ?? '' }}<br>
                Email: {{ $sale->customer->email ?? '' }}<br>
                <br>
                <strong>Invoice Date:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d') }}<br>
                <strong>Invoice #:</strong> {{ $sale->code }}
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive mb-4">
            <table class="table">
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
        </div>

        {{-- Totals --}}
        @php
            $discountTotal = (float) $sale->discount;
            $totalAmount   = (float) $sale->total;
            $paidAmount    = (float) $sale->paid_amount;
            $outstanding   = max(0, $totalAmount - $paidAmount);
        @endphp
        <div class="row justify-content-end">
            <div class="col-sm-5">
                <table class="table table-borderless">
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
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-4 pt-3 border-top">
            <p class="small text-muted text-center">
                Thank you for your business! Contact us at info@yourcompany.com.
            </p>
        </div>
    </div>
</div>
@endsection
