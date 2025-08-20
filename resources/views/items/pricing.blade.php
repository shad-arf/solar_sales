@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Item Pricing Management</h2>
    <a href="{{ route('items.index') }}" class="btn btn-secondary">Back to Items</a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Add Multiple Prices for Item</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('items.pricing.store') }}" method="POST" id="pricingForm">
            @csrf
            
            <!-- Item Selection -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Select Item</label>
                    <select name="item_id" class="form-select" required onchange="loadItemInfo(this.value)">
                        <option value="">Choose an item...</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" data-name="{{ $item->name }}" data-code="{{ $item->code }}">
                                {{ $item->code }} - {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Unit</label>
                    <input type="text" name="unit" class="form-control" placeholder="e.g., pieces, kg, meters" required>
                </div>
            </div>

            <!-- Selected Item Info -->
            <div id="itemInfo" class="alert alert-info" style="display: none;">
                <strong>Selected Item:</strong> <span id="selectedItemName"></span> (<span id="selectedItemCode"></span>)
            </div>

            <!-- Price Types Section -->
            <div class="mb-4">
                <h6 class="mb-3">Price Types</h6>
                <div id="priceTypes">
                    <!-- Price A -->
                    <div class="row mb-3 price-row">
                        <div class="col-md-4">
                            <label class="form-label">Price Type</label>
                            <input type="text" name="price_types[0][name]" class="form-control" value="Price A" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="price_types[0][price]" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Description (Optional)</label>
                            <input type="text" name="price_types[0][description]" class="form-control" placeholder="e.g., Wholesale">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger" onclick="removePriceRow(this)" disabled>
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Price B -->
                    <div class="row mb-3 price-row">
                        <div class="col-md-4">
                            <label class="form-label">Price Type</label>
                            <input type="text" name="price_types[1][name]" class="form-control" value="Price B" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="price_types[1][price]" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Description (Optional)</label>
                            <input type="text" name="price_types[1][description]" class="form-control" placeholder="e.g., Retail">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger" onclick="removePriceRow(this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Price C -->
                    <div class="row mb-3 price-row">
                        <div class="col-md-4">
                            <label class="form-label">Price Type</label>
                            <input type="text" name="price_types[2][name]" class="form-control" value="Price C" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="price_types[2][price]" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Description (Optional)</label>
                            <input type="text" name="price_types[2][description]" class="form-control" placeholder="e.g., Premium">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger" onclick="removePriceRow(this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <button type="button" class="btn btn-outline-primary" onclick="addPriceRow()">
                    <i class="bi bi-plus-circle"></i> Add Another Price Type
                </button>
            </div>

            <!-- Submit Button -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Pricing</button>
                <a href="{{ route('items.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<!-- Current Item Prices (if editing) -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Current Item Prices</h5>
    </div>
    <div class="card-body">
        <div id="currentPrices" class="text-muted">
            Select an item to view its current pricing
        </div>
    </div>
</div>

<script>
let priceRowIndex = 3;

function loadItemInfo(itemId) {
    const select = document.querySelector('select[name="item_id"]');
    const selectedOption = select.querySelector(`option[value="${itemId}"]`);
    
    if (selectedOption) {
        const itemName = selectedOption.dataset.name;
        const itemCode = selectedOption.dataset.code;
        
        document.getElementById('selectedItemName').textContent = itemName;
        document.getElementById('selectedItemCode').textContent = itemCode;
        document.getElementById('itemInfo').style.display = 'block';
        
        // Load current prices (you can implement an AJAX call here)
        loadCurrentPrices(itemId);
    } else {
        document.getElementById('itemInfo').style.display = 'none';
        document.getElementById('currentPrices').innerHTML = '<div class="text-muted">Select an item to view its current pricing</div>';
    }
}

function loadCurrentPrices(itemId) {
    // This would typically be an AJAX call to fetch current prices
    // For now, showing a placeholder
    document.getElementById('currentPrices').innerHTML = `
        <div class="row">
            <div class="col-md-12">
                <p><strong>Loading current prices for this item...</strong></p>
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    `;
}

function addPriceRow() {
    const priceTypesContainer = document.getElementById('priceTypes');
    const newRow = document.createElement('div');
    newRow.className = 'row mb-3 price-row';
    newRow.innerHTML = `
        <div class="col-md-4">
            <label class="form-label">Price Type</label>
            <input type="text" name="price_types[${priceRowIndex}][name]" class="form-control" value="Price ${String.fromCharCode(65 + priceRowIndex)}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Price Amount</label>
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" name="price_types[${priceRowIndex}][price]" class="form-control" step="0.01" min="0" placeholder="0.00" required>
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label">Description (Optional)</label>
            <input type="text" name="price_types[${priceRowIndex}][description]" class="form-control" placeholder="e.g., Special">
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="button" class="btn btn-outline-danger" onclick="removePriceRow(this)">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    priceTypesContainer.appendChild(newRow);
    priceRowIndex++;
    updateRemoveButtons();
}

function removePriceRow(button) {
    const row = button.closest('.price-row');
    row.remove();
    updateRemoveButtons();
    reindexPriceRows();
}

function updateRemoveButtons() {
    const priceRows = document.querySelectorAll('.price-row');
    priceRows.forEach((row, index) => {
        const removeButton = row.querySelector('button[onclick*="removePriceRow"]');
        if (removeButton) {
            removeButton.disabled = priceRows.length <= 1;
        }
    });
}

function reindexPriceRows() {
    const priceRows = document.querySelectorAll('.price-row');
    priceRows.forEach((row, index) => {
        const inputs = row.querySelectorAll('input[name*="price_types"]');
        inputs.forEach(input => {
            const name = input.name;
            const newName = name.replace(/price_types\[\d+\]/, `price_types[${index}]`);
            input.name = newName;
        });
    });
    priceRowIndex = priceRows.length;
}

// Initialize the remove button states
document.addEventListener('DOMContentLoaded', function() {
    updateRemoveButtons();
});
</script>

<style>
.price-row {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #dee2e6;
    margin-bottom: 10px;
}

.price-row:hover {
    background-color: #e9ecef;
}

.input-group-text {
    min-width: 45px;
    justify-content: center;
}

#itemInfo {
    border-left: 4px solid #0d6efd;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}
</style>
@endsection