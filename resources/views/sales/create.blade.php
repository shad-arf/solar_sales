@extends('layouts.admin')

@section('content')
<h2>Record Sale</h2>

<form action="{{ route('sales.store') }}" method="POST" class="mt-3" id="sale-form">
    @csrf

    {{-- 1) Customer --}}
    <div class="mb-3">
        <label class="form-label">Customer</label>
        <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
            <option value="" disabled selected>Select customer…</option>
            @foreach($customers as $cust)
                <option value="{{ $cust->id }}" {{ old('customer_id') == $cust->id ? 'selected' : '' }}>
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
               value="{{ old('code') }}"
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
                    <th style="width: 35%;">Item</th>
                    <th style="width: 15%;" class="text-end">Price ($)</th>
                    <th style="width: 15%;" class="text-center">Quantity</th>
                    <th style="width: 15%;" class="text-center">Discount (%)</th>
                    <th style="width: 15%;" class="text-end">Line Total ($)</th>
                    <th style="width: 5%;"></th>
                </tr>
            </thead>
            <tbody id="items-body">
                {{-- initially empty; user clicks “Add Item” to insert row --}}
            </tbody>
        </table>
        <button type="button"
                class="btn btn-sm btn-outline-secondary"
                id="add-item-btn">
            <i class="bi bi-plus-lg"></i> Add Item
        </button>
        @error('item_id')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    {{-- 4) Subtotal, Paid, Outstanding --}}
    <div class="row gy-3">
        <div class="col-md-4">
            <label class="form-label">Subtotal ($)</label>
            <input type="text"
                   name="subtotal"
                   id="subtotal"
                   class="form-control"
                   value="{{ old('subtotal', '0.00') }}"
                   readonly>
            @error('subtotal')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Amount Paid ($)</label>
            <input type="number"
                   name="paid_amount"
                   id="paid_amount"
                   class="form-control @error('paid_amount') is-invalid @enderror"
                   value="{{ old('paid_amount', '0.00') }}"
                   step="0.01"
                   min="0"
                   oninput="recalculateOutstanding()"
                   required>
            @error('paid_amount')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Outstanding ($)</label>
            <input type="text"
                   id="outstanding"
                   class="form-control"
                   value="{{ old('outstanding', '0.00') }}"
                   readonly>
        </div>
    </div>

    {{-- 5) Sale Date --}}
    <div class="mb-3 mt-4">
        <label class="form-label">Sale Date</label>
        <input type="date"
               name="sale_date"
               class="form-control @error('sale_date') is-invalid @enderror"
               value="{{ old('sale_date', now()->toDateString()) }}"
               required>
        @error('sale_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- 6) Submit / Cancel --}}
    <button type="submit" class="btn btn-primary">Save Sale</button>
    <a href="{{ route('sales.index') }}" class="btn btn-secondary">Cancel</a>
</form>

{{-- Hidden template row (cloned by JavaScript) --}}
<table style="display: none;">
    <tbody>
        <tr id="item-row-template">
            <td>
                <select name="item_id[]" class="form-select item-select" required onchange="updateRow(this)">
                    <option value="" disabled selected>Select item…</option>
                    @foreach($items as $itm)
                        <option value="{{ $itm->id }}" data-price="{{ $itm->price }}">
                            {{ $itm->name }} — ${{ number_format($itm->price, 2) }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td class="text-end">
                <input type="text" name="price[]" value="0.00" class="form-control line-price" readonly>
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
                       name="discount[]"
                       value="0"
                       step="0.01"
                       min="0"
                       max="100"
                       class="form-control line-discount"
                       onchange="updateRow(this)"
                       oninput="updateRow(this)">
            </td>
            <td class="text-end">
                <input type="text" name="line_total[]" value="0.00" class="form-control line-total" readonly>
            </td>
            <td class="text-center">
                <button type="button"
                        class="btn btn-sm btn-outline-danger"
                        onclick="removeRow(this)"
                        title="Remove this item">
                    <i class="bi bi-trash">delete</i>
                </button>
            </td>
        </tr>
    </tbody>
</table>

{{-- JavaScript --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        addItemRow(); // add one row initially
    });

    document.getElementById('add-item-btn').addEventListener('click', function() {
        addItemRow();
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
        recalculateSubtotalAndOutstanding();
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

        recalculateSubtotalAndOutstanding();
    }

    function recalculateSubtotalAndOutstanding() {
        let subtotal = 0;
        document.querySelectorAll('input.line-total').forEach(input => {
            subtotal += parseFloat(input.value) || 0;
        });
        document.getElementById('subtotal').value = subtotal.toFixed(2);
        recalculateOutstanding();
    }

    function recalculateOutstanding() {
        const subtotal = parseFloat(document.getElementById('subtotal').value) || 0;
        const paidAmount = parseFloat(document.getElementById('paid_amount').value) || 0;
        let outstanding = subtotal - paidAmount;
        document.getElementById('outstanding').value = outstanding.toFixed(2);
    }
</script>
@endsection
