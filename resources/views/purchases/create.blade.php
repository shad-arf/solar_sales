@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Create Purchase Order</h2>
    <div>
        <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Purchases
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">New Purchase Order - {{ $purchaseNumber }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
            @csrf

            <!-- Purchase Details -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Supplier <span class="text-danger">*</span></label>
                    <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                        <option value="">Select a supplier...</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                                @if($supplier->contact_person)
                                    - {{ $supplier->contact_person }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Purchase Date <span class="text-danger">*</span></label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" 
                           class="form-control @error('purchase_date') is-invalid @enderror" required>
                    @error('purchase_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror" 
                              placeholder="Optional notes about this purchase...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Purchase Items Section -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-primary mb-0">
                        <i class="bi bi-box"></i> Purchase Items
                    </h6>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addPurchaseItem()">
                        <i class="bi bi-plus-circle"></i> Add Item
                    </button>
                </div>

                <div id="purchaseItems">
                    @if(old('items'))
                        @foreach(old('items') as $index => $item)
                            <div class="purchase-item-row mb-3" data-index="{{ $index }}">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Item</label>
                                        <select name="items[{{ $index }}][item_id]" class="form-select item-select" required>
                                            <option value="">Select an item...</option>
                                            @foreach($items as $availableItem)
                                                <option value="{{ $availableItem->id }}" 
                                                        {{ $item['item_id'] == $availableItem->id ? 'selected' : '' }}>
                                                    {{ $availableItem->code }} - {{ $availableItem->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" name="items[{{ $index }}][quantity_purchased]" 
                                               value="{{ $item['quantity_purchased'] }}" min="1" 
                                               class="form-control quantity-input" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Purchase Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="items[{{ $index }}][purchase_price]" 
                                                   value="{{ $item['purchase_price'] }}" step="0.01" min="0" 
                                                   class="form-control price-input" required>
                                        </div>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removePurchaseItem(this)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <div class="line-total-display">
                                            <small class="text-muted">Total:</small>
                                            <strong class="line-total">$0.00</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <!-- Default empty item row -->
                        <div class="purchase-item-row mb-3" data-index="0">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Item</label>
                                    <select name="items[0][item_id]" class="form-select item-select" required>
                                        <option value="">Select an item...</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->code }} - {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" name="items[0][quantity_purchased]" min="1" 
                                           class="form-control quantity-input" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Purchase Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="items[0][purchase_price]" step="0.01" min="0" 
                                               class="form-control price-input" required>
                                    </div>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removePurchaseItem(this)" disabled>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <div class="line-total-display">
                                        <small class="text-muted">Total:</small>
                                        <strong class="line-total">$0.00</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Purchase Total -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6 class="mb-0">Total Purchase Amount:</h6>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <h4 class="mb-0" id="grandTotal">$0.00</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Create Purchase Order
                </button>
                <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-lg"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
let itemIndex = {{ old('items') ? count(old('items')) : 1 }};

document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to existing rows
    addEventListenersToRow();
    calculateGrandTotal();

    function addEventListenersToRow() {
        document.querySelectorAll('.quantity-input, .price-input').forEach(input => {
            input.addEventListener('input', function() {
                updateLineTotal(this.closest('.purchase-item-row'));
                calculateGrandTotal();
            });
        });
    }

    window.addPurchaseItem = function() {
        const container = document.getElementById('purchaseItems');
        const newRow = document.createElement('div');
        newRow.className = 'purchase-item-row mb-3';
        newRow.dataset.index = itemIndex;
        newRow.innerHTML = `
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Item</label>
                    <select name="items[${itemIndex}][item_id]" class="form-select item-select" required>
                        <option value="">Select an item...</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}">
                                {{ $item->code }} - {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="items[${itemIndex}][quantity_purchased]" min="1" 
                           class="form-control quantity-input" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Purchase Price</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="items[${itemIndex}][purchase_price]" step="0.01" min="0" 
                               class="form-control price-input" required>
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removePurchaseItem(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <div class="line-total-display">
                        <small class="text-muted">Total:</small>
                        <strong class="line-total">$0.00</strong>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(newRow);
        itemIndex++;
        
        // Add event listeners to new row
        const newInputs = newRow.querySelectorAll('.quantity-input, .price-input');
        newInputs.forEach(input => {
            input.addEventListener('input', function() {
                updateLineTotal(this.closest('.purchase-item-row'));
                calculateGrandTotal();
            });
        });
        
        updateRemoveButtons();
    }

    window.removePurchaseItem = function(button) {
        const row = button.closest('.purchase-item-row');
        row.remove();
        updateRemoveButtons();
        calculateGrandTotal();
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.purchase-item-row');
        rows.forEach((row, index) => {
            const removeButton = row.querySelector('button[onclick*="removePurchaseItem"]');
            if (removeButton) {
                removeButton.disabled = rows.length <= 1;
            }
        });
    }

    function updateLineTotal(row) {
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const total = quantity * price;
        
        const lineTotalElement = row.querySelector('.line-total');
        lineTotalElement.textContent = '$' + total.toFixed(2);
    }

    function calculateGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.purchase-item-row').forEach(row => {
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            grandTotal += quantity * price;
            updateLineTotal(row);
        });
        
        document.getElementById('grandTotal').textContent = '$' + grandTotal.toFixed(2);
    }

    // Initial setup
    updateRemoveButtons();
});
</script>

<style>
.purchase-item-row {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.line-total-display {
    text-align: center;
    padding: 0.375rem 0;
}

.purchase-item-row:hover {
    background-color: #e9ecef;
    border-color: #dee2e6;
}
</style>
@endsection