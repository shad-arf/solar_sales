@extends('layouts.admin')

@section('content')
<h2>Edit Sale</h2>
<form action="{{ route('sales.update', $sale) }}" method="POST" class="mt-3" id="sale-form">
    @csrf
    @method('PUT')

    {{-- 1) Customer --}}
    <div class="row mb-3">
        <div class="col-md-8">
            <label class="form-label">Customer</label>
            <select name="customer_id" id="customer-select" class="form-select @error('customer_id') is-invalid @enderror" required>
                <option value="" disabled {{ old('customer_id', $sale->customer_id) ? '' : 'selected' }}>Select customer…</option>
                @foreach($customers as $cust)
                    <option value="{{ $cust->id }}" 
                            {{ old('customer_id', default: $sale->customer_id) == $cust->id ? 'selected' : '' }}>
                        {{ $cust->name }}
                    </option>
                @endforeach
            </select>
            @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Customer Type for This Sale</label>
            <select name="customer_type" id="customer-type-select"
                    class="form-select @error('customer_type') is-invalid @enderror"
                    required>
                <option value="end_user" {{ old('customer_type', $sale->customer_type ?? 'end_user') == 'end_user' ? 'selected' : '' }}>End User</option>
                <option value="installer" {{ old('customer_type', $sale->customer_type) == 'installer' ? 'selected' : '' }}>Installer</option>
                <option value="reseller" {{ old('customer_type', $sale->customer_type) == 'reseller' ? 'selected' : '' }}>Reseller</option>
            </select>
            @error('customer_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- 2) Invoice Code --}}
    <div class="mb-3">
        <label class="form-label">Invoice Code</label>
        <input type="text" name="code" value="{{ old('code', $sale->code) }}" class="form-control @error('code') is-invalid @enderror" required>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- 3) Items Table --}}
    <div class="mb-3">
        <label class="form-label">Items</label>
        <table class="table table-bordered" id="items-table">
            <thead class="table-light">
                <tr>
                    <th>Item</th>
                    <th class="text-end">End user ($)</th>
                    <th class="text-end">Installer ($)</th>
                    <th class="text-end">Reseller ($)</th>
                    <th>Type</th>
                    <th class="text-center">Qty</th>
                    <th class="text-center">Discount (%)</th>
                    <th class="text-end">Line Total ($)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="items-body">
                @if(old('item_id'))
                    @foreach(old('item_id') as $i => $oldItemId)
                        <tr class="item-row">
                            <input type="hidden" name="order_item_id[]" value="{{ old("order_item_id.$i") }}">
                            <td>
                                <select name="item_id[]" class="form-select item-select @error('item_id.'.$i) is-invalid @enderror" required onchange="updateRow(this)">
                                    <option value="" disabled>Select…</option>
                                    @foreach($items as $itm)
                                        <option value="{{ $itm->id }}" data-price="{{ $itm->price }}" data-op-price="{{ $itm->operator_price }}" data-base-price="{{ $itm->base_price }}" {{ $oldItemId == $itm->id ? 'selected' : '' }}>{{ $itm->name }}</option>
                                    @endforeach
                                </select>
                                @error('item_id.'.$i)<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </td>
                            <td><input type="text" class="form-control line-price-reg" readonly></td>
                            <td><input type="text" class="form-control line-price-op" readonly></td>
                            <td><input type="text" class="form-control line-price-base" readonly></td>
                            <td>
                                <select name="price_type[]" class="form-select price-type" onchange="updateRow(this)">
                                    <option value="regular" {{ old("price_type.$i")=='regular' ? 'selected':'' }}>End user</option>
                                    <option value="operator" {{ old("price_type.$i")=='operator' ? 'selected':'' }}>Installer</option>
                                    <option value="base" {{ old("price_type.$i")=='base' ? 'selected':'' }}>Reseller</option>
                                </select>
                            </td>
                            <td><input type="number" name="quantity[]" class="form-control line-quantity" min="1" value="{{ old("quantity.$i",1) }}" required onchange="updateRow(this)" oninput="updateRow(this)"></td>
                            <td><input type="number" name="line_discount[]" class="form-control line-discount" step="0.01" min="0" max="100" value="{{ old("line_discount.$i",0) }}" oninput="updateRow(this)"></td>
                            <td><input type="text" name="line_total[]" class="form-control line-total" readonly></td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    @endforeach
                @else
                    @foreach($sale->orderItems as $i => $oi)
                        @php
                            $type = 'regular';
                            if ($oi->item->operator_price && abs($oi->unit_price - $oi->item->operator_price) < 0.01) {
                                $type = 'operator';
                            } elseif ($oi->item->base_price && abs($oi->unit_price - $oi->item->base_price) < 0.01) {
                                $type = 'base';
                            }
                        @endphp
                        <tr class="item-row">
                            <input type="hidden" name="order_item_id[]" value="{{ $oi->id }}">
                            <td>
                                <select name="item_id[]" class="form-select item-select" required onchange="updateRow(this)">
                                    <option value="" disabled>Select…</option>
                                    @foreach($items as $itm)
                                        <option value="{{ $itm->id }}" data-price="{{ $itm->price }}" data-op-price="{{ $itm->operator_price }}" data-base-price="{{ $itm->base_price }}" {{ $oi->item_id == $itm->id ? 'selected' : '' }}>{{ $itm->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="text" class="form-control line-price-reg" readonly></td>
                            <td><input type="text" class="form-control line-price-op" readonly></td>
                            <td><input type="text" class="form-control line-price-base" readonly></td>
                            <td>
                                <select name="price_type[]" class="form-select price-type" onchange="updateRow(this)">
                                    <option value="regular" {{ $type=='regular'? 'selected':'' }}>End user</option>
                                    <option value="operator" {{ $type=='operator'? 'selected':'' }}>Installer</option>
                                    <option value="base" {{ $type=='base'? 'selected':'' }}>Reseller</option>
                                </select>
                            </td>
                            <td><input type="number" name="quantity[]" class="form-control line-quantity" min="1" value="{{ old("quantity.$i", $oi->quantity) }}" required onchange="updateRow(this)" oninput="updateRow(this)"></td>
                            <td><input type="number" name="line_discount[]" class="form-control line-discount" step="0.01" min="0" max="100" value="{{ old("line_discount.$i", $oi->line_discount) }}" oninput="updateRow(this)"></td>
                            <td><input type="text" name="line_total[]" class="form-control line-total" readonly></td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="add-item-btn"><i class="bi bi-plus-lg"></i> Add Item</button>
    </div>

    {{-- 4) Totals & Payments --}}
    @php
        $initialSubtotal = $sale->orderItems->sum(fn($oi) => $oi->line_total);
        $existingPaid    = $sale->paid_amount;
        $itemsTotal      = $initialSubtotal + ($sale->discount ?? 0);
    @endphp
    <div class="row gy-3">
        <div class="col-md-3">
            <label class="form-label">Items Total</label>
            <input type="text" id="items_total" class="form-control" value="{{ number_format($itemsTotal, 2) }}" readonly>
        </div>
        <div class="col-md-3">
            <label class="form-label">Overall Discount ($)</label>
            <input type="number" name="discount" id="discount" 
                   class="form-control" 
                   value="{{ old('discount', $sale->discount ?? 0) }}" 
                   step="0.01" min="0" 
                   oninput="recalcTotals()"
                   placeholder="0.00">
        </div>
        <div class="col-md-3">
            <label class="form-label">Subtotal</label>
            <input type="text" id="subtotal" class="form-control" value="{{ number_format($initialSubtotal,2) }}" readonly>
        </div>
        <div class="col-md-3">
            <label class="form-label">Already Paid</label>
            <input type="text" class="form-control" value="{{ number_format($existingPaid,2) }}" readonly>
        </div>
    </div>
    <div class="row gy-3 mt-2">
        <div class="col-md-4">
            <label class="form-label">New Payment</label>
            <input type="number" name="paid" id="paid_amount" class="form-control" value="{{ old('paid',0) }}" step="0.01"  oninput="recalcAll()">
        </div>
        <div class="col-md-4">
            <label class="form-label">Outstanding Before</label>
            <input type="text" class="form-control" value="{{ number_format(max(0,$initialSubtotal-$existingPaid),2) }}" readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label">Outstanding After</label>
            <input type="text" id="outstanding_after" class="form-control" readonly>
        </div>
    </div>

    {{-- 5) Sale Date --}}
    <div class="mb-3 mt-4">
        <label class="form-label">Sale Date</label>
        <input type="date" name="sale_date" class="form-control @error('sale_date') is-invalid @enderror" value="{{ old('sale_date', $sale->sale_date) }}" required>
        @error('sale_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <button type="submit" class="btn btn-primary">Update Sale</button>
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
            <option value="{{ $itm->id }}" data-price="{{ $itm->price }}" data-op-price="{{ $itm->operator_price }}" data-base-price="{{ $itm->base_price }}">{{ $itm->name }}</option>
          @endforeach
        </select>
      </td>
      <td><input type="text" class="form-control line-price-reg" readonly></td>
      <td><input type="text" class="form-control line-price-op" readonly></td>
      <td><input type="text" class="form-control line-price-base" readonly></td>
      <td>
        <select name="price_type[]" class="form-select price-type" onchange="updateRow(this)">
          <option value="regular" selected>End user</option>
          <option value="operator">Installer</option>
          <option value="base">Reseller</option>
        </select>
      </td>
      <td><input type="number" name="quantity[]" class="form-control line-quantity" value="1" min="1" required onchange="updateRow(this)" oninput="updateRow(this)"></td>
      <td><input type="number" name="line_discount[]" class="form-control line-discount" value="0" step="0.01" min="0" max="100" oninput="updateRow(this)"></td>
      <td><input type="text" name="line_total[]" class="form-control line-total" readonly></td>
      <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="bi bi-trash"></i></button></td>
    </tr>
  </tbody>
</table>

<script>
  document.getElementById('add-item-btn').addEventListener('click', addItemRow);

  function addItemRow() {
    const tpl = document.getElementById('item-row-template');
    const row = tpl.cloneNode(true);
    row.removeAttribute('id');
    row.classList.add('item-row');
    row.style.display = '';
    attachHandlers(row);
    document.getElementById('items-body').appendChild(row);
    updateRow(row.querySelector('.item-select'));
  }

  function removeRow(btn) {
    btn.closest('tr').remove();
    recalcAll();
  }

  function updateRow(el) {
    const row = el.closest('tr');
    const opt = row.querySelector('.item-select').selectedOptions[0] || {};
    const reg   = parseFloat(opt.dataset.price)     || 0;
    const op    = parseFloat(opt.dataset.opPrice)   || 0;
    const baseP = parseFloat(opt.dataset.basePrice) || 0;

    // Populate price fields
    row.querySelector('.line-price-reg').value  = reg.toFixed(2);
    row.querySelector('.line-price-op').value   = op.toFixed(2);
    row.querySelector('.line-price-base').value = baseP.toFixed(2);

    const type = row.querySelector('.price-type').value;
    // Recalculate discount % on item or type change
    const discField = row.querySelector('.line-discount');
    if (el.classList.contains('item-select') || el.classList.contains('price-type')) {
      let pct = 0;
      if (type === 'operator') pct = ((reg - op) / reg) * 100;
      if (type === 'base')     pct = ((reg - baseP) / reg) * 100;
      discField.value = pct.toFixed(2);
    }

    const qty = +row.querySelector('.line-quantity').value || 0;
    let disc = parseFloat(discField.value) || 0;
    disc = Math.min(100, Math.max(0, disc));

    // Always apply discount on regular price
    const lineTotal = reg * qty * (1 - disc / 100);
    row.querySelector('.line-total').value = lineTotal.toFixed(2);

    recalcAll();
  }

  function recalcAll() {
    recalcTotals();
  }

  function recalcTotals() {
    // Calculate items total
    let itemsTotal = 0;
    document.querySelectorAll('.line-total').forEach(input => itemsTotal += parseFloat(input.value) || 0);
    document.getElementById('items_total').value = itemsTotal.toFixed(2);
    
    // Apply overall discount
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const subtotal = Math.max(0, itemsTotal - discount);
    document.getElementById('subtotal').value = subtotal.toFixed(2);

    const existing = parseFloat("{{ $existingPaid }}") || 0;
    const paid = parseFloat(document.getElementById('paid_amount').value) || 0;

    const before = subtotal - existing;
    const after = Math.max(0, before - paid);
    document.getElementById('outstanding_after').value = after.toFixed(2);
  }

  function attachHandlers(row) {
    row.querySelector('.item-select').onchange = e => updateRow(e.target);
    row.querySelector('.price-type').onchange = e => updateRow(e.target);
    row.querySelector('.line-quantity').oninput = e => updateRow(e.target);
    row.querySelector('.line-discount').oninput = e => updateRow(e.target);
    row.querySelector('.btn-outline-danger').onclick = e => removeRow(e.target);
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('tr.item-row').forEach(row => {
      attachHandlers(row);
      // Only update prices without changing the selected price type
      const itemSelect = row.querySelector('.item-select');
      const opt = itemSelect.selectedOptions[0] || {};
      const reg   = parseFloat(opt.dataset.price)     || 0;
      const op    = parseFloat(opt.dataset.opPrice)   || 0;
      const baseP = parseFloat(opt.dataset.basePrice) || 0;

      // Populate price fields only
      row.querySelector('.line-price-reg').value  = reg.toFixed(2);
      row.querySelector('.line-price-op').value   = op.toFixed(2);
      row.querySelector('.line-price-base').value = baseP.toFixed(2);

      // Calculate line total based on existing selections
      const qty = +row.querySelector('.line-quantity').value || 0;
      const disc = parseFloat(row.querySelector('.line-discount').value) || 0;
      const lineTotal = reg * qty * (1 - disc / 100);
      row.querySelector('.line-total').value = lineTotal.toFixed(2);
    });
  });

  // Auto-suggest price type based on customer type
  const customerTypeSelect = document.getElementById('customer-type-select');
  if (customerTypeSelect) {
    customerTypeSelect.addEventListener('change', function() {
      const customerType = this.value;
      let suggestedPriceType = 'regular'; // default to End user
      
      if (customerType === 'installer') {
        suggestedPriceType = 'operator';
      } else if (customerType === 'reseller') {
        suggestedPriceType = 'base';
      }
      
      // Update all price type dropdowns in new item rows only (not existing ones)
      document.querySelectorAll('tr:not(.item-row) .price-type').forEach(priceSelect => {
        priceSelect.value = suggestedPriceType;
        updateRow(priceSelect);
      });
    });
  }
</script>
@endsection
