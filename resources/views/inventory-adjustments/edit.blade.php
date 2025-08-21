@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Edit Inventory Adjustment</h2>
    <div>
        <a href="{{ route('inventory-adjustments.show', $inventoryAdjustment) }}" class="btn btn-secondary me-2">
            <i class="bi bi-eye"></i> View Details
        </a>
        <a href="{{ route('inventory-adjustments.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Adjustments
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Edit Adjustment Information</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('inventory-adjustments.update', $inventoryAdjustment) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Current Adjustment Info Alert -->
            <div class="alert alert-info mb-4">
                <h6><i class="bi bi-info-circle"></i> Current Adjustment</h6>
                <div class="row">
                    <div class="col-md-3">
                        <strong>Item:</strong> {{ $inventoryAdjustment->item->name }}
                    </div>
                    <div class="col-md-3">
                        <strong>Date:</strong> {{ $inventoryAdjustment->adjustment_date->format('M d, Y') }}
                    </div>
                    <div class="col-md-3">
                        <strong>Adjustment:</strong> 
                        @php
                            $currentAdjustment = $inventoryAdjustment->actual_quantity - $inventoryAdjustment->system_quantity;
                        @endphp
                        <span class="badge {{ $currentAdjustment >= 0 ? 'bg-success' : 'bg-danger' }}">
                            {{ $currentAdjustment >= 0 ? '+' : '' }}{{ $currentAdjustment }}
                        </span>
                    </div>
                    <div class="col-md-3">
                        <strong>Impact:</strong> ${{ number_format($inventoryAdjustment->financial_impact, 2) }}
                    </div>
                </div>
            </div>

            <!-- Item Selection -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Item <span class="text-danger">*</span></label>
                    <select name="item_id" class="form-select @error('item_id') is-invalid @enderror" id="itemSelect" required>
                        <option value="">Select an item...</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" 
                                    data-quantity="{{ $item->quantity }}" 
                                    data-price="{{ $item->price ?? 0 }}"
                                    {{ old('item_id', $inventoryAdjustment->item_id) == $item->id ? 'selected' : '' }}>
                                {{ $item->name }} ({{ $item->code }}) - Current: {{ $item->quantity }}
                            </option>
                        @endforeach
                    </select>
                    @error('item_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Adjustment Date <span class="text-danger">*</span></label>
                    <input type="date" name="adjustment_date" 
                           value="{{ old('adjustment_date', $inventoryAdjustment->adjustment_date->format('Y-m-d')) }}" 
                           class="form-control @error('adjustment_date') is-invalid @enderror" required>
                    @error('adjustment_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Current Item Info Card -->
            <div id="currentItemInfo" class="card bg-light mb-4">
                <div class="card-body">
                    <h6 class="card-title text-primary">
                        <i class="bi bi-info-circle"></i> Current Item Information
                    </h6>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Current System Quantity:</strong>
                            <span id="currentQuantity" class="badge bg-info fs-6">{{ $inventoryAdjustment->item->quantity }}</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Item Price:</strong>
                            <span id="itemPrice">${{ number_format($inventoryAdjustment->item->price ?? 0, 2) }}</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Estimated Financial Impact:</strong>
                            <span id="estimatedImpact" class="badge bg-warning">${{ number_format($inventoryAdjustment->financial_impact, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quantity Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="text-primary mb-3">
                        <i class="bi bi-clipboard-data"></i> Quantity Adjustment
                    </h6>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">System Quantity <span class="text-danger">*</span></label>
                    <input type="number" name="system_quantity" id="systemQuantity" 
                           value="{{ old('system_quantity', $inventoryAdjustment->system_quantity) }}" 
                           class="form-control @error('system_quantity') is-invalid @enderror" 
                           min="0" required>
                    @error('system_quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Original system quantity at time of adjustment</div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Actual Quantity <span class="text-danger">*</span></label>
                    <input type="number" name="actual_quantity" id="actualQuantity" 
                           value="{{ old('actual_quantity', $inventoryAdjustment->actual_quantity) }}" 
                           class="form-control @error('actual_quantity') is-invalid @enderror" 
                           min="0" required>
                    @error('actual_quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Actual quantity counted</div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Adjustment Amount</label>
                    <div id="adjustmentAmount" class="form-control-plaintext">
                        @php
                            $adjustment = $inventoryAdjustment->actual_quantity - $inventoryAdjustment->system_quantity;
                        @endphp
                        @if($adjustment === 0)
                            <span class="badge bg-secondary">No Change</span>
                        @elseif($adjustment > 0)
                            <span class="badge bg-success">+{{ $adjustment }}</span>
                        @else
                            <span class="badge bg-danger">{{ $adjustment }}</span>
                        @endif
                    </div>
                    <div class="form-text">Difference (actual - system)</div>
                </div>
            </div>

            <!-- Reason and Notes -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Reason <span class="text-danger">*</span></label>
                    <select name="reason" class="form-select @error('reason') is-invalid @enderror" required>
                        <option value="">Select a reason...</option>
                        @foreach(\App\Models\InventoryAdjustment::REASONS as $key => $label)
                            <option value="{{ $key }}" {{ old('reason', $inventoryAdjustment->reason) == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="3" 
                              class="form-control @error('notes') is-invalid @enderror" 
                              placeholder="Additional details about this adjustment...">{{ old('notes', $inventoryAdjustment->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Adjustment Preview -->
            <div id="adjustmentPreview" class="card border-warning mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Adjustment Preview</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Adjustment Type:</strong>
                            <div id="previewType" class="mt-1">
                                @php
                                    $adjustment = $inventoryAdjustment->actual_quantity - $inventoryAdjustment->system_quantity;
                                @endphp
                                <span class="badge {{ $adjustment >= 0 ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $adjustment >= 0 ? 'Increase' : 'Decrease' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <strong>Quantity Change:</strong>
                            <div id="previewQuantity" class="mt-1">
                                <span class="badge {{ $adjustment >= 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $adjustment >= 0 ? '+' : '' }}{{ $adjustment }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <strong>Financial Impact:</strong>
                            <div id="previewImpact" class="mt-1">
                                <span class="badge bg-warning text-dark">${{ number_format($inventoryAdjustment->financial_impact, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <strong>New System Quantity:</strong>
                            <div id="previewNewQuantity" class="mt-1">
                                <span class="badge bg-info">{{ $inventoryAdjustment->actual_quantity }}</span>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>Warning:</strong> Updating this adjustment will change the item's current system quantity to match the new actual quantity.
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Update Adjustment
                </button>
                <a href="{{ route('inventory-adjustments.show', $inventoryAdjustment) }}" class="btn btn-secondary">
                    <i class="bi bi-x-lg"></i> Cancel
                </a>
                <form action="{{ route('inventory-adjustments.destroy', $inventoryAdjustment) }}" 
                      method="POST" class="d-inline ms-auto"
                      onsubmit="return confirm('Are you sure you want to delete this inventory adjustment? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash"></i> Delete Adjustment
                    </button>
                </form>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemSelect = document.getElementById('itemSelect');
    const systemQuantity = document.getElementById('systemQuantity');
    const actualQuantity = document.getElementById('actualQuantity');
    const currentQuantitySpan = document.getElementById('currentQuantity');
    const itemPriceSpan = document.getElementById('itemPrice');
    const estimatedImpact = document.getElementById('estimatedImpact');
    const adjustmentAmount = document.getElementById('adjustmentAmount');

    // Handle item selection
    itemSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            const quantity = selectedOption.dataset.quantity;
            const price = selectedOption.dataset.price;
            
            currentQuantitySpan.textContent = quantity;
            itemPriceSpan.textContent = '$' + parseFloat(price).toFixed(2);
            
            updatePreview();
        }
    });

    // Handle quantity changes
    systemQuantity.addEventListener('input', updatePreview);
    actualQuantity.addEventListener('input', updatePreview);

    function updatePreview() {
        const systemQty = parseInt(systemQuantity.value) || 0;
        const actualQty = parseInt(actualQuantity.value) || 0;
        const itemPrice = parseFloat(itemSelect.options[itemSelect.selectedIndex]?.dataset.price) || 0;

        const adjustment = actualQty - systemQty;
        const financialImpact = Math.abs(adjustment) * itemPrice;
        
        // Update adjustment amount display
        if (adjustment === 0) {
            adjustmentAmount.innerHTML = '<span class="badge bg-secondary">No Change</span>';
        } else if (adjustment > 0) {
            adjustmentAmount.innerHTML = `<span class="badge bg-success">+${adjustment}</span>`;
        } else {
            adjustmentAmount.innerHTML = `<span class="badge bg-danger">${adjustment}</span>`;
        }

        // Update estimated impact
        estimatedImpact.textContent = '$' + financialImpact.toFixed(2);

        // Update preview panel
        document.getElementById('previewType').innerHTML = adjustment >= 0 ? 
            '<span class="badge bg-success">Increase</span>' : 
            '<span class="badge bg-warning text-dark">Decrease</span>';
            
        document.getElementById('previewQuantity').innerHTML = adjustment >= 0 ? 
            `<span class="badge bg-success">+${adjustment}</span>` : 
            `<span class="badge bg-danger">${adjustment}</span>`;
            
        document.getElementById('previewImpact').innerHTML = 
            `<span class="badge bg-warning text-dark">$${financialImpact.toFixed(2)}</span>`;
            
        document.getElementById('previewNewQuantity').innerHTML = 
            `<span class="badge bg-info">${actualQty}</span>`;
    }

    // Initial setup
    updatePreview();
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

.badge {
    font-size: 0.8em;
}

.form-control-plaintext {
    display: flex;
    align-items: center;
    min-height: calc(1.5em + 0.75rem + 2px);
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #ffecb5;
    color: #664d03;
}
</style>
@endsection