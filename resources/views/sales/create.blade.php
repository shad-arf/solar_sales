@extends('layouts.admin')

@section('content')
<h2>Record Sale</h2>

<form action="{{ route('sales.store') }}" method="POST" id="sale-form">
    @csrf

    {{-- 1) Customer --}}
    <div class="row mb-3">
        <div class="col-md-8">
            <label class="form-label">Customer</label>
            <select name="customer_id" id="customer-select"
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
        <div class="col-md-4">
            <label class="form-label">Customer Type for This Sale</label>
            <select name="customer_type" id="customer-type-select"
                    class="form-select @error('customer_type') is-invalid @enderror"
                    required>
                <option value="end_user" {{ old('customer_type', 'end_user') == 'end_user' ? 'selected' : '' }}>End User</option>
                <option value="installer" {{ old('customer_type') == 'installer' ? 'selected' : '' }}>Installer</option>
                <option value="reseller" {{ old('customer_type') == 'reseller' ? 'selected' : '' }}>Reseller</option>
            </select>
            @error('customer_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
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
                    <th>Price Type</th>
                    <th class="text-end">Unit Price ($)</th>
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
                                <select name="item_id[]" class="form-select item-select" required onchange="updateItemOptions(this)">
                                    <option value="" disabled>Select…</option>
                                    @foreach($items as $itm)
                                        <option value="{{ $itm->id }}" {{ $oldItemId == $itm->id ? 'selected' : '' }}>
                                            {{ $itm->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="price_id[]" class="form-select price-select" required onchange="updateRow(this)">
                                    <option value="" disabled>Select price type…</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control unit-price" readonly>
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
        <div class="col-md-3">
            <label>Items Total</label>
            <input type="text" id="items_total" class="form-control" value="0.00" readonly>
        </div>
        <div class="col-md-3">
            <label>Overall Discount ($)</label>
            <input type="number" name="discount" id="discount" 
                   class="form-control" 
                   value="{{ old('discount', '0.00') }}" 
                   step="0.01" min="0" 
                   oninput="recalcTotals()"
                   placeholder="0.00">
        </div>
        <div class="col-md-3">
            <label>Subtotal</label>
            <input type="text" name="subtotal" id="subtotal" class="form-control" value="0.00" readonly>
        </div>
        <div class="col-md-3">
            <label>Amount Paid</label>
            <input type="number" name="paid_amount" id="paid_amount"
                   class="form-control"
                   value="{{ old('paid_amount','0.00') }}"
                   step="0.01" min="0"
                   oninput="recalcOutstanding()"
                   required>
        </div>
    </div>
    <div class="row gy-3 mt-2">
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
        <select name="item_id[]" class="form-select item-select" required onchange="updateItemOptions(this)">
          <option value="" disabled selected>Select…</option>
          @foreach($items as $itm)
            <option value="{{ $itm->id }}">{{ $itm->name }}</option>
          @endforeach
        </select>
      </td>
      <td>
        <select name="price_id[]" class="form-select price-select" required onchange="updateRow(this)">
          <option value="" disabled selected>Select price type…</option>
        </select>
      </td>
      <td>
        <input type="text" class="form-control unit-price" readonly>
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
  // Item data from Laravel
  const itemsData = @json($items->keyBy('id'));

  document.getElementById('add-item-btn').addEventListener('click', addItemRow);

  function addItemRow() {
    const tpl = document.getElementById('item-row-template');
    const row = tpl.cloneNode(true);
    row.id = '';
    row.classList.add('item-row');
    row.style.display = '';
    document.getElementById('items-body').appendChild(row);
  }

  function removeRow(btn) {
    btn.closest('tr').remove();
    recalcAll();
  }

  function updateItemOptions(selectElement) {
    const row = selectElement.closest('tr');
    const itemId = selectElement.value;
    const priceSelect = row.querySelector('.price-select');
    
    // Clear existing price options
    priceSelect.innerHTML = '<option value="" disabled selected>Select price type…</option>';
    
    if (itemId && itemsData[itemId]) {
      const item = itemsData[itemId];
      // Add price options for this item
      if (item.item_prices) {
        item.item_prices.forEach(price => {
          const option = document.createElement('option');
          option.value = price.id;
          option.textContent = `${price.name} ($${parseFloat(price.price).toFixed(2)})`;
          option.dataset.price = price.price;
          priceSelect.appendChild(option);
        });
      }
    }
    
    // Reset other fields
    row.querySelector('.unit-price').value = '';
    row.querySelector('.line-total').value = '0.00';
    recalcAll();
  }

  function updateRow(el) {
    const row = el.closest('tr');
    const priceSelect = row.querySelector('.price-select');
    const selectedOption = priceSelect.selectedOptions[0];
    const qty = +row.querySelector('.line-quantity').value || 0;
    const discField = row.querySelector('.line-discount');
    
    if (selectedOption && selectedOption.dataset.price) {
      const unitPrice = parseFloat(selectedOption.dataset.price);
      row.querySelector('.unit-price').value = unitPrice.toFixed(2);
      
      const finalDisc = Math.min(100, Math.max(0, parseFloat(discField.value) || 0));
      const lineTotal = unitPrice * qty * (1 - finalDisc/100);
      row.querySelector('.line-total').value = lineTotal.toFixed(2);
    } else {
      row.querySelector('.unit-price').value = '';
      row.querySelector('.line-total').value = '0.00';
    }

    recalcAll();
  }

  function recalcAll() {
    recalcTotals();
  }

  function recalcTotals() {
    // Calculate items total
    let itemsTotal = 0;
    document.querySelectorAll('.line-total').forEach(i => itemsTotal += +i.value || 0);
    document.getElementById('items_total').value = itemsTotal.toFixed(2);
    
    // Apply overall discount
    const discount = +document.getElementById('discount').value || 0;
    const subtotal = Math.max(0, itemsTotal - discount);
    document.getElementById('subtotal').value = subtotal.toFixed(2);
    
    recalcOutstanding();
  }

  function recalcOutstanding() {
    const sub = +document.getElementById('subtotal').value || 0;
    const paid = +document.getElementById('paid_amount').value || 0;
    document.getElementById('outstanding').value = (sub - paid).toFixed(2);
  }

  document.addEventListener('DOMContentLoaded', () => {
    if (!document.querySelector('.item-row')) {
      addItemRow();
    } else {
      // Initialize existing rows
      document.querySelectorAll('.item-select').forEach(el => {
        if (el.value) updateItemOptions(el);
      });
    }
  });

  // Auto-suggest price type based on customer type
  const customerTypeSelect = document.getElementById('customer-type-select');
  if (customerTypeSelect) {
    customerTypeSelect.addEventListener('change', function() {
      const customerType = this.value;
      let suggestedPriceName = '';
      
      // Map customer type to likely price names
      if (customerType === 'installer') {
        suggestedPriceName = 'installer';
      } else if (customerType === 'reseller') {
        suggestedPriceName = 'reseller';
      } else {
        suggestedPriceName = 'retail'; // or 'end user'
      }
      
      // Update all existing price dropdowns to the suggested type
      document.querySelectorAll('.price-select').forEach(priceSelect => {
        const options = Array.from(priceSelect.options);
        const matchingOption = options.find(opt => 
          opt.textContent.toLowerCase().includes(suggestedPriceName.toLowerCase())
        );
        
        if (matchingOption) {
          priceSelect.value = matchingOption.value;
          updateRow(priceSelect);
        }
      });
    });
  }
</script>
@endsection
