@extends('layouts.admin')

@section('content')
<h2>Record Sale</h2>

<form action="{{ route('sales.store') }}" method="POST" id="sale-form">
    @csrf

    {{-- 1) Customer --}}
    <div class="mb-3">
        <label class="form-label">Customer</label>
        <select name="customer_id"
                class="form-select @error('customer_id') is-invalid @enderror"
                required>
            <option value="" disabled {{ old('customer_id') ? '' : 'selected' }}>
                Select customer…
            </option>
            @foreach($customers as $cust)
                <option value="{{ $cust->id }}"
                        {{ old('customer_id') == $cust->id ? 'selected' : '' }}>
                    {{ $cust->name }}
                </option>
            @endforeach
        </select>
        @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- 2) Invoice Code --}}
    <div class="mb-3">
        <label class="form-label">Invoice Code</label>
        <input type="text"
               name="code"
               value="{{ old('code', $saleId ?? '') }}"
               class="form-control @error('code') is-invalid @enderror"
               required>
        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- 3) Items Table --}}
    <div class="mb-3">
        <label class="form-label">Items</label>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Item</th>
                    <th class="text-end">Reg. ($)</th>
                    <th class="text-end">Op. ($)</th>
                    <th class="text-end">Base ($)</th>
                    <th>Type</th>
                    <th class="text-center">Qty</th>
                    <th class="text-center">Discount (%)</th>
                    <th class="text-end">Line Total ($)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="items-body">
                @if(old('item_id'))
                    @foreach(old('item_id') as $key => $oldItemId)
                        <tr class="item-row">
                            <td>
                                <select name="item_id[]" class="form-select item-select" required onchange="updateRow(this)">
                                    <option value="" disabled>Select…</option>
                                    @foreach($items as $itm)
                                        <option value="{{ $itm->id }}"
                                                data-price="{{ $itm->price }}"
                                                data-op-price="{{ $itm->operator_price }}"
                                                data-base-price="{{ $itm->base_price }}"
                                                {{ $oldItemId == $itm->id ? 'selected' : '' }}>
                                            {{ $itm->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="text" class="form-control line-price-reg"  readonly></td>
                            <td><input type="text" class="form-control line-price-op"   readonly></td>
                            <td><input type="text" class="form-control line-price-base" readonly></td>
                            <td>
                                <select name="price_type[]" class="form-select price-type" onchange="updateRow(this)">
                                    <option value="regular"  {{ old("price_type.$key")=='regular'  ? 'selected':'' }}>Regular</option>
                                    <option value="operator" {{ old("price_type.$key")=='operator'? 'selected':'' }}>Operator</option>
                                    <option value="base"     {{ old("price_type.$key")=='base'    ? 'selected':'' }}>Base</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="quantity[]" min="1"
                                       class="form-control line-quantity"
                                       required
                                       value="{{ old("quantity.$key",1) }}"
                                       onchange="updateRow(this)" oninput="updateRow(this)">
                            </td>
                            <td>
                                <input type="number" name="line_discount[]" step="0.01" min="0" max="100"
                                       class="form-control line-discount"
                                       value="{{ old("line_discount.$key",0) }}"
                                       oninput="updateRow(this)">
                            </td>
                            <td>
                                <input type="text" name="line_total[]" class="form-control line-total" readonly>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="add-item-btn">
            <i class="bi bi-plus-lg"></i> Add Item
        </button>
    </div>

    {{-- 4) Totals & Payment --}}
    <div class="row gy-3">
        <div class="col-md-4">
            <label>Subtotal</label>
            <input type="text" name="subtotal" id="subtotal" class="form-control" value="0.00" readonly>
        </div>
        <div class="col-md-4">
            <label>Amount Paid</label>
            <input type="number" name="paid_amount" id="paid_amount"
                   class="form-control"
                   value="{{ old('paid_amount','0.00') }}"
                   step="0.01" min="0"
                   oninput="recalcOutstanding()"
                   required>
        </div>
        <div class="col-md-4">
            <label>Outstanding</label>
            <input type="text" id="outstanding" class="form-control" value="0.00" readonly>
        </div>
    </div>

    {{-- 5) Sale Date --}}
    <div class="mb-3 mt-4">
        <label>Sale Date</label>
        <input type="date" name="sale_date"
               class="form-control @error('sale_date') is-invalid @enderror"
               value="{{ old('sale_date', now()->toDateString()) }}"
               required>
        @error('sale_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <button type="submit" class="btn btn-primary">Save Sale</button>
    <a href="{{ route('sales.index') }}" class="btn btn-secondary">Cancel</a>
</form>

{{-- Hidden template --}}
<table style="display:none">
  <tbody>
    <tr id="item-row-template">
      <td>
        <select name="item_id[]" class="form-select item-select" required onchange="updateRow(this)">
          <option value="" disabled selected>Select…</option>
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
      <td><input type="text" class="form-control line-price-reg" readonly></td>
      <td><input type="text" class="form-control line-price-op"  readonly></td>
      <td><input type="text" class="form-control line-price-base" readonly></td>
      <td>
        <select name="price_type[]" class="form-select price-type" onchange="updateRow(this)">
          <option value="regular" selected>Regular</option>
          <option value="operator">Operator</option>
          <option value="base">Base</option>
        </select>
      </td>
      <td>
        <input type="number" name="quantity[]" value="1" min="1"
               class="form-control line-quantity"
               onchange="updateRow(this)" oninput="updateRow(this)" required>
      </td>
      <td>
        <input type="number" name="line_discount[]" value="0" step="0.01" min="0" max="100"
               class="form-control line-discount"
               oninput="updateRow(this)">
      </td>
      <td><input type="text" name="line_total[]" class="form-control line-total" readonly></td>
      <td class="text-center">
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
          <i class="bi bi-trash"></i>
        </button>
      </td>
    </tr>
  </tbody>
</table>

<script>
  document.getElementById('add-item-btn').addEventListener('click', addItemRow);

  function addItemRow() {
    const tpl = document.getElementById('item-row-template');
    const row = tpl.cloneNode(true);
    row.id = '';
    row.classList.add('item-row');
    row.style.display = '';
    document.getElementById('items-body').appendChild(row);
    updateRow(row.querySelector('.item-select'));
  }

  function removeRow(btn) {
    btn.closest('tr').remove();
    recalcAll();
  }

  function updateRow(el) {
    const row       = el.closest('tr');
    const opt       = row.querySelector('.item-select').selectedOptions[0] || {};
    const reg       = parseFloat(opt.dataset.price     || 0);
    const op        = parseFloat(opt.dataset.opPrice   || 0);
    const baseP     = parseFloat(opt.dataset.basePrice || 0);
    const type      = row.querySelector('.price-type').value;
    const qty       = +row.querySelector('.line-quantity').value || 0;
    const discField = row.querySelector('.line-discount');

    // display prices
    row.querySelector('.line-price-reg').value  = reg.toFixed(2);
    row.querySelector('.line-price-op').value   = op.toFixed(2);
    row.querySelector('.line-price-base').value = baseP.toFixed(2);

    // calculate default discount if needed
    if (el.classList.contains('price-type') || el.classList.contains('item-select')) {
      let pct = 0;
      if (type==='operator') pct = ((reg-op)/reg)*100;
      if (type==='base')     pct = ((reg-baseP)/reg)*100;
      discField.value = pct.toFixed(2);
    }

    const finalDisc = Math.min(100, Math.max(0, parseFloat(discField.value)||0));
    const lineTotal = reg * qty * (1 - finalDisc/100);
    row.querySelector('.line-total').value = lineTotal.toFixed(2);

    recalcAll();
  }

  function recalcAll() {
    let sum = 0;
    document.querySelectorAll('.line-total').forEach(i => sum+=+i.value||0);
    document.getElementById('subtotal').value = sum.toFixed(2);
    recalcOutstanding();
  }

  function recalcOutstanding() {
    const sub = +document.getElementById('subtotal').value||0;
    const paid= +document.getElementById('paid_amount').value||0;
    document.getElementById('outstanding').value = (sub-paid).toFixed(2);
  }

  document.addEventListener('DOMContentLoaded', () => {
    if (! document.querySelector('.item-row')) {
      addItemRow();
    } else {
      document.querySelectorAll('.item-select').forEach(el=>updateRow(el));
    }
  });
</script>
@endsection
