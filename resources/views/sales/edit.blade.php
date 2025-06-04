@extends('layouts.admin')

@section('content')
<h2>Edit Sale</h2>

<form action="{{ route('sales.update', $sale) }}" method="POST" class="mt-3" id="sale-form">
    @csrf
    @method('PUT')

    {{-- 1) Customer --}}
    <div class="mb-3">
        <label class="form-label">Customer</label>
        <select name="customer_id"
                class="form-select @error('customer_id') is-invalid @enderror"
                required>
            @foreach ($customers as $cust)
                <option value="{{ $cust->id }}"
                    {{ old('customer_id', $sale->customer_id) == $cust->id ? 'selected' : '' }}>
                    {{ $cust->name }}
                </option>
            @endforeach
        </select>
        @error('customer_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- 2) Invoice Code --}}
    <div class="mb-3">
        <label class="form-label">Invoice Code</label>
        <input type="text"
               name="code"
               value="{{ old('code', $sale->code) }}"
               class="form-control @error('code') is-invalid @enderror"
               required>
        @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- 3) Dynamic Items Table --}}
    <div class="mb-3">
        <label class="form-label">Items</label>
        <table class="table table-bordered" id="items-table">
            <thead class="table-light">
                <tr>
                    <th style="width: 30%;">Item</th>
                    <th style="width: 15%;" class="text-end">Price ($)</th>
                    <th style="width: 15%;" class="text-center">Quantity</th>
                    <th style="width: 15%;" class="text-center">Discount (%)</th>
                    <th style="width: 15%;" class="text-end">Line Total ($)</th>
                    <th style="width: 5%;"></th>
                </tr>
            </thead>
            <tbody id="items-body">
                {{-- Pre-populate existing orderItems --}}
                @foreach ($sale->orderItems as $index => $orderItem)
                    <tr class="item-row">
                        <td>
                            <select name="item_id[]"
                                    class="form-select item-select @error('item_id.' . $index) is-invalid @enderror"
                                    required
                                    onchange="updateRow(this)">
                                <option value="" disabled>Select item…</option>
                                @foreach($items as $itm)
                                    <option value="{{ $itm->id }}"
                                        data-price="{{ $itm->price }}"
                                        {{ $itm->id == $orderItem->item_id ? 'selected' : '' }}>
                                        {{ $itm->name }} — ${{ number_format($itm->price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error("item_id.$index")
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>

                        <td class="text-end">
                            <input type="text"
                                   name="price[]"
                                   value="{{ number_format($orderItem->unit_price, 2) }}"
                                   class="form-control line-price"
                                   readonly>
                        </td>

                        <td class="text-center">
                            <input type="number"
                                   name="quantity[]"
                                   value="{{ old("quantity.$index", $orderItem->quantity) }}"
                                   min="1"
                                   class="form-control line-quantity @error('quantity.' . $index) is-invalid @enderror"
                                   onchange="updateRow(this)"
                                   oninput="updateRow(this)"
                                   required>
                            @error("quantity.$index")
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>

                        <td class="text-center">
                            <input type="number"
                                   name="line_discount[]"
                                   value="{{ old("line_discount.$index", $orderItem->line_discount) }}"
                                   step="0.01"
                                   min="0"
                                   max="100"
                                   class="form-control line-discount @error('line_discount.' . $index) is-invalid @enderror"
                                   onchange="updateRow(this)"
                                   oninput="updateRow(this)">
                            @error("line_discount.$index")
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>

                        <td class="text-end">
                            <input type="text"
                                   name="line_total[]"
                                   value="{{ number_format($orderItem->line_total, 2) }}"
                                   class="form-control line-total"
                                   readonly>
                        </td>

                        <td class="text-center">
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="removeRow(this)"
                                    title="Remove this item">
                                <i class="bi bi-trash"> delete</i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <button type="button"
                class="btn btn-sm btn-outline-secondary"
                id="add-item-btn">
            <i class="bi bi-plus-lg"></i> Add Item
        </button>
    </div>

    {{-- 4) Subtotal, Paid, Outstanding --}}
    @php
        // Calculate current subtotal from existing orderItems
        $existingSubtotal = $sale->orderItems->sum(fn($oi) => $oi->line_total);
    @endphp
    <div class="row gy-3">
        <div class="col-md-4">
            <label class="form-label">Subtotal ($)</label>
            <input type="text"
                   name="subtotal"
                   id="subtotal"
                   class="form-control"
                   value="{{ old('subtotal', number_format($existingSubtotal, 2)) }}"
                   readonly>
            @error('subtotal')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Existing Paid ($)</label>
            <input type="text"
                   class="form-control"
                   value="{{ number_format($sale->paid_amount, 2) }}"
                   readonly>
        </div>

        <div class="col-md-4">
            <label class="form-label">New Payment ($)</label>
            <input type="number"
                   name="paid"
                   id="paid_amount"
                   class="form-control @error('paid') is-invalid @enderror"
                   value="{{ old('paid', 0) }}"
                   step="0.01"
                   >
            @error('paid')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Outstanding Before ($)</label>
            @php
                $beforeOutstanding = max(0, $existingSubtotal - $sale->paid_amount);
            @endphp
            <input type="text"
                   class="form-control {{ $beforeOutstanding > 0 ? 'text-danger' : 'text-success' }}"
                   value="{{ number_format($beforeOutstanding, 2) }}"
                   readonly>
        </div>

        <div class="col-md-4">
            <label class="form-label">Outstanding After ($)</label>
            <input type="text"
                   id="outstanding"
                   class="form-control"
                   value="{{ number_format($beforeOutstanding - floatval(old('paid', 0)), 2) }}"
                   readonly>
        </div>
    </div>

    {{-- 5) Sale Date --}}
    <div class="mb-3 mt-4">
        <label class="form-label">Sale Date</label>
        <input type="date"
               name="sale_date"
               class="form-control @error('sale_date') is-invalid @enderror"
               value="{{ old('sale_date', $sale->sale_date) }}"
               required>
        @error('sale_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- 6) Display Payment History --}}
    <h4>Payment History</h4>
    <ul class="list-group mb-3">
        @forelse($payments as $pay)
            <li class="list-group-item d-flex justify-content-between">
                <span>${{ number_format($pay->amount, 2) }}</span>
                <small>{{ $pay->paid_at }}</small>
            </li>
        @empty
            <li class="list-group-item text-muted">No payments recorded</li>
        @endforelse
    </ul>

    {{-- 7) Save / Cancel --}}
    <button class="btn btn-primary">Update Sale</button>
    <a href="{{ route('sales.index') }}" class="btn btn-secondary">Cancel</a>

    {{-- 8) Clear Remaining Loan if desired --}}
    <form action="{{ route('customers.clearLoan', $sale->customer_id) }}" method="POST" class="d-inline float-end">
        @csrf
        <button type="submit"
                class="btn btn-warning"
                onclick="return confirm('Mark all as fully paid and clear loan?')">
            Mark Fully Paid
        </button>
    </form>
</form>

{{-- Hidden template row (cloned by JS) --}}
<table style="display: none;">
    <tbody>
        <tr id="item-row-template">
            <td>
                <select name="item_id[]"
                        class="form-select item-select"
                        required
                        onchange="updateRow(this)">
                    <option value="" disabled selected>Select item…</option>
                    @foreach($items as $itm)
                        <option value="{{ $itm->id }}"
                                data-price="{{ $itm->price }}">
                            {{ $itm->name }} — ${{ number_format($itm->price, 2) }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td class="text-end">
                <input type="text"
                       name="price[]"
                       value="0.00"
                       class="form-control line-price"
                       readonly>
            </td>
            <td class="text-center">
                <input type="number"
                       name="quantity[]"
                       value="1"
                       min="1"
                       class="form-control line-quantity"
                       onchange="updateRow(this)"
                       oninput="updateRow(this)"
                       required>
            </td>
            <td class="text-center">
                <input type="number"
                       name="line_discount[]"
                       value="0"
                       step="0.01"
                       min="0"
                       max="100"
                       class="form-control line-discount"
                       onchange="updateRow(this)"
                       oninput="updateRow(this)">
            </td>
            <td class="text-end">
                <input type="text"
                       name="line_total[]"
                       value="0.00"
                       class="form-control line-total"
                       readonly>
            </td>
            <td class="text-center">
                <button type="button"
                        class="btn btn-sm btn-outline-danger"
                        onclick="removeRow(this)"
                        title="Remove this item">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    </tbody>
</table>

{{-- JavaScript to handle dynamic rows & recalculations --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Ensure each existing row’s JS-updates run once on load
        document.querySelectorAll('tr.item-row').forEach(row => {
            updateRow(row.querySelector('select.item-select'));
        });

        // Add “Add Item” button handler
        document.getElementById('add-item-btn').addEventListener('click', function() {
            addItemRow();
        });
    });

    function addItemRow() {
        const template = document.getElementById('item-row-template');
        const clone = template.cloneNode(true);
        clone.removeAttribute('id');
        clone.classList.add('item-row');
        clone.style.display = '';
        document.getElementById('items-body').appendChild(clone);
        updateRow(clone.querySelector('select.item-select'));
    }

    function removeRow(button) {
        const row = button.closest('tr');
        row.parentNode.removeChild(row);
        recalcSubtotalAndOutstanding();
    }

    function updateRow(element) {
        const row = element.closest('tr');
        const select = row.querySelector('select.item-select');
        const qtyInput = row.querySelector('input.line-quantity');
        const discInput = row.querySelector('input.line-discount');
        const priceInput = row.querySelector('input.line-price');
        const lineTotalInput = row.querySelector('input.line-total');

        const price = parseFloat(select.selectedOptions[0]?.dataset.price || 0);
        priceInput.value = price.toFixed(2);

        const quantity = parseInt(qtyInput.value) || 0;
        let discountPercent = parseFloat(discInput.value) || 0;
        discountPercent = Math.max(0, Math.min(100, discountPercent));

        const rawLine = price * quantity;
        const discounted = rawLine * (discountPercent / 100);
        const lineTotal = rawLine - discounted;
        lineTotalInput.value = lineTotal.toFixed(2);

        recalcSubtotalAndOutstanding();
    }

    function recalcSubtotalAndOutstanding() {
        let subtotal = 0;
        document.querySelectorAll('input.line-total').forEach(input => {
            subtotal += parseFloat(input.value) || 0;
        });
        document.getElementById('subtotal').value = subtotal.toFixed(2);

        const existingPaid = parseFloat("{{ $sale->paid_amount }}") || 0;
        const newPaid = parseFloat(document.getElementById('paid_amount').value) || 0;
        const outstandingBefore = subtotal - existingPaid;
        let outstandingAfter = outstandingBefore - newPaid;
        if (outstandingAfter < 0) outstandingAfter = 0;
        document.getElementById('outstanding').value = outstandingAfter.toFixed(2);
    }
</script>
@endsection
