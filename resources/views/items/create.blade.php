@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Add New Item</h2>
    <div>

        <a href="{{ route('items.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Items
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Item Information</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('items.store') }}" method="POST">
            @csrf

            <!-- Basic Information -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Item Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Item Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" class="form-control @error('code') is-invalid @enderror" required>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Unique identifier for this item</div>
                </div>
            </div>

            <!-- Description -->
            <div class="row mb-4">
                <div class="col-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Enter item description...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Pricing Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="text-primary mb-3">
                        <i class="bi bi-currency-dollar"></i> Pricing Information
                    </h6>
                </div>

                <div class="col-12">
                    <div id="pricingTypes">
                        <!-- Default Pricing Row -->
                        <div class="pricing-row mb-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Unit/Type Name</label>
                                    <input type="text" name="pricing[0][unit_name]" value="{{ old('pricing.0.unit_name', 'End User Price') }}" class="form-control" placeholder="e.g., Per piece, Per kg" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Unit Description</label>
                                    <input type="text" name="pricing[0][unit_description]" value="{{ old('pricing.0.unit_description') }}" class="form-control" placeholder="e.g., Standard selling price">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Price Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="pricing[0][price]" value="{{ old('pricing.0.price') }}" step="0.01" min="0" class="form-control pricing-amount" placeholder="0.00" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Is Default</label>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input default-price-radio" type="radio" name="default_pricing" value="0" checked>
                                        <label class="form-check-label">Default</label>
                                    </div>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removePricingRow(this)" disabled>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Second Pricing Row -->
                        <div class="pricing-row mb-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Unit/Type Name</label>
                                    <input type="text" name="pricing[1][unit_name]" value="{{ old('pricing.1.unit_name', 'Installer Price') }}" class="form-control" placeholder="e.g., Per dozen, Bulk">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Unit Description</label>
                                    <input type="text" name="pricing[1][unit_description]" value="{{ old('pricing.1.unit_description') }}" class="form-control" placeholder="e.g., Bulk/wholesale pricing">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Price Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="pricing[1][price]" value="{{ old('pricing.1.price') }}" step="0.01" min="0" class="form-control pricing-amount" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Is Default</label>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input default-price-radio" type="radio" name="default_pricing" value="1">
                                        <label class="form-check-label">Default</label>
                                    </div>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removePricingRow(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Third Pricing Row -->
                        <div class="pricing-row mb-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Unit/Type Name</label>
                                    <input type="text" name="pricing[2][unit_name]" value="{{ old('pricing.2.unit_name', 'Reseller Price') }}" class="form-control" placeholder="e.g., Per box, Special">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Unit Description</label>
                                    <input type="text" name="pricing[2][unit_description]" value="{{ old('pricing.2.unit_description') }}" class="form-control" placeholder="e.g., Special operator pricing">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Price Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="pricing[2][price]" value="{{ old('pricing.2.price') }}" step="0.01" min="0" class="form-control pricing-amount" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Is Default</label>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input default-price-radio" type="radio" name="default_pricing" value="2">
                                        <label class="form-check-label">Default</label>
                                    </div>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removePricingRow(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addPricingRow()">
                            <i class="bi bi-plus-circle"></i> Add Another Pricing Option
                        </button>
                    </div>
                </div>
            </div>


            <!-- Action Buttons -->
            <div class="d-flex gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Save Item
                </button>
                <a href="{{ route('items.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-lg"></i> Cancel
                </a>
                <button type="button" class="btn btn-outline-info" onclick="openPricingHelper()">
                    <i class="bi bi-calculator"></i> Pricing Helper
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Pricing Helper Modal -->
<div class="modal fade" id="pricingHelperModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pricing Calculator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Cost Price</label>
                    <input type="number" id="helperCost" class="form-control" step="0.01" placeholder="0.00">
                </div>
                <div class="mb-3">
                    <label class="form-label">Markup Percentage</label>
                    <input type="number" id="helperMarkup" class="form-control" step="1" placeholder="25" value="25">
                    <div class="form-text">Recommended: 20-30%</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Suggested Selling Price</label>
                    <input type="text" id="helperResult" class="form-control" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="applyHelperPrice()">Apply to Form</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let pricingRowIndex = 3;

document.addEventListener('DOMContentLoaded', function() {

    // Add event listeners to all pricing amount inputs and radio buttons
    function addPricingEventListeners() {
        document.querySelectorAll('.pricing-amount, .default-price-radio').forEach(input => {
            // Event listeners for price updates can be added here if needed
        });
    }

    // Pricing helper functions
    window.openPricingHelper = function() {
        const modal = new bootstrap.Modal(document.getElementById('pricingHelperModal'));
        modal.show();
    }

    window.applyHelperPrice = function() {
        const result = document.getElementById('helperResult').value;
        if (result) {
            const defaultPricingIndex = document.querySelector('input[name="default_pricing"]:checked')?.value || 0;
            const defaultPriceInput = document.querySelector(`input[name="pricing[${defaultPricingIndex}][price]"]`);
            if (defaultPriceInput) {
                defaultPriceInput.value = parseFloat(result).toFixed(2);
            }
            bootstrap.Modal.getInstance(document.getElementById('pricingHelperModal')).hide();
        }
    }

    // Helper calculator
    document.getElementById('helperCost').addEventListener('input', calculateHelper);
    document.getElementById('helperMarkup').addEventListener('input', calculateHelper);

    function calculateHelper() {
        const cost = parseFloat(document.getElementById('helperCost').value) || 0;
        const markup = parseFloat(document.getElementById('helperMarkup').value) || 0;
        const sellingPrice = cost * (1 + markup / 100);
        document.getElementById('helperResult').value = sellingPrice.toFixed(2);
    }

    // Add new pricing row
    window.addPricingRow = function() {
        const pricingContainer = document.getElementById('pricingTypes');
        const newRow = document.createElement('div');
        newRow.className = 'pricing-row mb-3';
        newRow.innerHTML = `
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Unit/Type Name</label>
                    <input type="text" name="pricing[${pricingRowIndex}][unit_name]" class="form-control" placeholder="e.g., Per case, Custom" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Unit Description</label>
                    <input type="text" name="pricing[${pricingRowIndex}][unit_description]" class="form-control" placeholder="Description for this pricing">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Price Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="pricing[${pricingRowIndex}][price]" step="0.01" min="0" class="form-control pricing-amount" placeholder="0.00">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Is Default</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input default-price-radio" type="radio" name="default_pricing" value="${pricingRowIndex}">
                        <label class="form-check-label">Default</label>
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removePricingRow(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        pricingContainer.appendChild(newRow);
        pricingRowIndex++;
        updateRemoveButtons();
        addPricingEventListeners();
    }

    // Remove pricing row
    window.removePricingRow = function(button) {
        const row = button.closest('.pricing-row');
        row.remove();
        updateRemoveButtons();
        reindexPricingRows();
        addPricingEventListeners();
    }

    // Update remove button states
    function updateRemoveButtons() {
        const pricingRows = document.querySelectorAll('.pricing-row');
        pricingRows.forEach((row, index) => {
            const removeButton = row.querySelector('button[onclick*="removePricingRow"]');
            if (removeButton) {
                removeButton.disabled = pricingRows.length <= 1;
            }
        });
    }

    // Reindex pricing rows
    function reindexPricingRows() {
        const pricingRows = document.querySelectorAll('.pricing-row');
        pricingRows.forEach((row, index) => {
            // Update input names
            const inputs = row.querySelectorAll('input[name*="pricing"]');
            inputs.forEach(input => {
                const name = input.name;
                if (name.includes('[unit_name]')) {
                    input.name = `pricing[${index}][unit_name]`;
                } else if (name.includes('[unit_description]')) {
                    input.name = `pricing[${index}][unit_description]`;
                } else if (name.includes('[price]')) {
                    input.name = `pricing[${index}][price]`;
                }
            });

            // Update radio button values
            const radio = row.querySelector('.default-price-radio');
            if (radio) {
                radio.value = index;
            }
        });
        pricingRowIndex = pricingRows.length;
    }

    // Initial setup
    addPricingEventListeners();
    updateRemoveButtons();
});
</script>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.form-label {
    font-weight: 500;
    color: #495057;
}

.text-danger {
    color: #dc3545 !important;
}

.text-primary {
    color: #0d6efd !important;
}

.input-group-text {
    background-color: #e9ecef;
    border-color: #ced4da;
}

#totalValue {
    background-color: #f8f9fa;
    font-weight: 500;
}

.pricing-row {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.pricing-row:hover {
    background-color: #e9ecef;
    border-color: #dee2e6;
}

.pricing-row .form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

.pricing-row .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>
@endsection
