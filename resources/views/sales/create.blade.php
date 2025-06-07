@extends('layouts.admin')

@section('content')
<h2>Record Sale</h2>
<form action="{{ route('sales.store') }}" method="POST" class="mt-3" id="sale-form">
    @csrf

    {{-- 1) Customer --}}
    <div class="mb-3">
        <label class="form-label">Customer</label>
        <select name="customer_id"
                id="customer-select"
                class="form-select @error('customer_id') is-invalid @enderror"
                required>
            <option value="" disabled selected>Select customer…</option>
            @foreach($customers as $cust)
                <option value="{{ $cust->id }}"
                        data-is-operator="{{ $cust->is_operator ? '1' : '0' }}">
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

    {{-- 3) Items Table --}}
    <div class="mb-3">
        <label class="form-label">Items</label>
        <table class="table table-bordered" id="items-table">
            <thead class="table-light">
                <tr>
                    <th>Item</th>
                    <th class="text-end">Reg. Price ($)</th>
                    <th class="text-end">Op. Price ($)</th>
                    <th class="text-end">Base Price ($)</th>
                    <th>Price Type</th>
                    <th class="text-center">Qty</th>
                    <th class="text-center">Discount (%)</th>
                    <th class="text-end">Line Total ($)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="items-body"></tbody>
        </table>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="add-item-btn">
            <i class="bi bi-plus-lg"></i> Add Item
        </button>
    </div>

    {{-- 4) Totals --}}
    <div class="row gy-3">
        <div class="col-md-4">
            <label class="form-label">Subtotal ($)</label>
            <input type="text" name="subtotal" id="subtotal"
                   class="form-control" value="0.00" readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label">Amount Paid ($)</label>
            <input type="number" name="paid_amount" id="paid_amount"
                   class="form-control"
                   value="0.00"
                   step="0.01" min="0"
                   oninput="recalcOutstanding()" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Outstanding ($)</label>
            <input type="text" id="outstanding" class="form-control" value="0.00" readonly>
        </div>
    </div>

    {{-- 5) Sale Date --}}
    <div class="mb-3 mt-4">
        <label class="form-label">Sale Date</label>
        <input type="date" name="sale_date"
               class="form-control"
               value="{{ old('sale_date', now()->toDateString()) }}"
               required>
    </div>

    {{-- Submit/Cancel --}}
    <button type="submit" class="btn btn-primary">Save Sale</button>
    <a href="{{ route('sales.index') }}" class="btn btn-secondary">Cancel</a>
</form>

{{-- Hidden template --}}
<table style="display:none">
  <tbody>
    <tr id="item-row-template">
      <td>
        <select name="item_id[]" class="form-select item-select" required onchange="updateRow(this)">
          <option value="" disabled selected>Select item…</option>
          @foreach($items as $itm)
            <option value="{{ $itm->id }}"
                    data-price="{{ $itm->price }}"
                    data-op-price="{{ $itm->operator_price }}"
                    data-base-price="{{ $itm->base_price }}">
              {{ $itm->name }}
            </option>
          @endforeach
        </select>
      </td>
      <td class="text-end">
        <input type="text" class="form-control line-price-reg" value="0.00" readonly>
      </td>
      <td class="text-end">
        <input type="text" class="form-control line-price-op" value="0.00" readonly>
      </td>
      <td class="text-end">
        <input type="text" class="form-control line-price-base" value="0.00" readonly>
      </td>
      <td>
        <select name="price_type[]" class="form-select price-type" onchange="updateRow(this)">
          <option value="regular" selected>Regular</option>
          <option value="operator">Operator</option>
          <option value="base">Base</option>
        </select>
      </td>
      <td class="text-center">
        <input type="number" name="quantity[]" value="1" min="1"
               class="form-control line-quantity"
               onchange="updateRow(this)" oninput="updateRow(this)" required>
      </td>
      <td class="text-center">
        <input type="number" name="discount[]" value="0" step="0.01" min="0" max="100"
               class="form-control line-discount"
               onchange="updateRow(this)" oninput="updateRow(this)">
      </td>
      <td class="text-end">
        <input type="text" name="line_total[]" class="form-control line-total" value="0.00" readonly>
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
          <i class="bi bi-trash">delete</i>
        </button>
      </td>
    </tr>
  </tbody>
</table>

<script>
  document.getElementById('add-item-btn').addEventListener('click', addItemRow);
  document.addEventListener('DOMContentLoaded', () => addItemRow());

  function addItemRow() {
    const tpl = document.getElementById('item-row-template');
    const row = tpl.cloneNode(true);
    row.id = '';
    row.classList.add('item-row');
    row.style.display = '';
    document.getElementById('items-body').appendChild(row);
    updateRow(row.querySelector('select.item-select'));
  }

  function removeRow(btn) {
    btn.closest('tr').remove();
    recalcAll();
  }

  function updateRow(el) {
    const row = el.closest('tr');
    const opt = row.querySelector('select.item-select').selectedOptions[0];

    // read the three prices
    const reg   = parseFloat(opt.dataset.price      || 0);
    const op    = parseFloat(opt.dataset.opPrice    || 0);
    const baseP = parseFloat(opt.dataset.basePrice  || 0);

    // show all three
    row.querySelector('.line-price-reg').value  = reg.toFixed(2);
    row.querySelector('.line-price-op').value   = op.toFixed(2);
    row.querySelector('.line-price-base').value = baseP.toFixed(2);

    // pick by per-row price type
    const type = row.querySelector('.price-type').value;
    let unit;
    if (type === 'operator') unit = op;
    else if (type === 'base') unit = baseP;
    else unit = reg;

    const qty  = +row.querySelector('.line-quantity').value || 0;
    const disc = Math.min(100, Math.max(0, +row.querySelector('.line-discount').value || 0));
    const total = unit * qty * (1 - disc/100);

    row.querySelector('.line-total').value = total.toFixed(2);
    recalcAll();
  }

  function recalcAll() {
    let sum = 0;
    document.querySelectorAll('.line-total')
            .forEach(i => sum += +i.value || 0);
    document.getElementById('subtotal').value = sum.toFixed(2);
    recalcOutstanding();
  }

  function recalcOutstanding() {
    const sub  = +document.getElementById('subtotal').value || 0;
    const paid = +document.getElementById('paid_amount').value || 0;
    document.getElementById('outstanding').value = (sub - paid).toFixed(2);
  }
</script>
@endsection
