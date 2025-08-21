@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Inventory Adjustment Details</h2>
        <div class="text-muted">
            <i class="bi bi-calendar"></i> {{ $inventoryAdjustment->adjustment_date->format('M d, Y') }}
            @php
                $adjustmentType = ($inventoryAdjustment->actual_quantity - $inventoryAdjustment->system_quantity) >= 0 ? 'increase' : 'decrease';
            @endphp
            <span class="badge {{ $adjustmentType == 'increase' ? 'bg-success' : 'bg-warning text-dark' }} ms-2">
                {{ ucfirst($adjustmentType) }}
            </span>
        </div>
    </div>
    <div>
        <a href="{{ route('inventory-adjustments.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Adjustments
        </a>
        <a href="{{ route('inventory-adjustments.edit', $inventoryAdjustment) }}" class="btn btn-primary">
            <i class="bi bi-pencil-square"></i> Edit Adjustment
        </a>
    </div>
</div>

<div class="row">
    <!-- Main Adjustment Information -->
    <div class="col-md-8">
        <!-- Basic Information Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle"></i> Adjustment Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Item:</th>
                                <td>
                                    <strong>{{ $inventoryAdjustment->item->name }}</strong>
                                    <br><small class="text-muted">Code: <code class="bg-light px-1 rounded">{{ $inventoryAdjustment->item->code }}</code></small>
                                </td>
                            </tr>
                            <tr>
                                <th>Adjustment Date:</th>
                                <td>{{ $inventoryAdjustment->adjustment_date->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <th>Reason:</th>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ \App\Models\InventoryAdjustment::REASONS[$inventoryAdjustment->reason] ?? ucfirst($inventoryAdjustment->reason) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Financial Impact:</th>
                                <td>
                                    <strong class="text-warning">${{ number_format($inventoryAdjustment->financial_impact, 2) }}</strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">System Quantity:</th>
                                <td><span class="badge bg-light text-dark fs-6">{{ $inventoryAdjustment->system_quantity }}</span></td>
                            </tr>
                            <tr>
                                <th>Actual Quantity:</th>
                                <td><span class="badge bg-info fs-6">{{ $inventoryAdjustment->actual_quantity }}</span></td>
                            </tr>
                            <tr>
                                <th>Adjustment:</th>
                                <td>
                                    @php
                                        $adjustment = $inventoryAdjustment->actual_quantity - $inventoryAdjustment->system_quantity;
                                        $badgeClass = $adjustment >= 0 ? 'bg-success' : 'bg-danger';
                                        $icon = $adjustment >= 0 ? 'bi-arrow-up' : 'bi-arrow-down';
                                    @endphp
                                    <span class="badge {{ $badgeClass }} fs-6">
                                        <i class="{{ $icon }}"></i> {{ $adjustment >= 0 ? '+' : '' }}{{ $adjustment }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Recorded:</th>
                                <td>
                                    {{ $inventoryAdjustment->created_at->format('M d, Y g:i A') }}
                                    <br><small class="text-muted">{{ $inventoryAdjustment->created_at->diffForHumans() }}</small>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($inventoryAdjustment->notes)
                <div class="mt-3">
                    <h6 class="text-muted">Notes:</h6>
                    <div class="p-3 bg-light rounded">
                        {{ $inventoryAdjustment->notes }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Item Details Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-box-seam"></i> Item Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Item Name:</th>
                                <td>
                                    <a href="{{ route('items.show', $inventoryAdjustment->item) }}" class="text-decoration-none">
                                        {{ $inventoryAdjustment->item->name }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Current Stock:</th>
                                <td>
                                    @php
                                        $currentStock = $inventoryAdjustment->item->quantity;
                                        $stockClass = '';
                                        if ($currentStock == 0) {
                                            $stockClass = 'bg-danger';
                                        } elseif ($currentStock < 10) {
                                            $stockClass = 'bg-warning text-dark';
                                        } else {
                                            $stockClass = 'bg-success';
                                        }
                                    @endphp
                                    <span class="badge {{ $stockClass }} fs-6">{{ $currentStock }} units</span>
                                </td>
                            </tr>
                            <tr>
                                <th>Item Price:</th>
                                <td>${{ number_format($inventoryAdjustment->item->price ?? 0, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Description:</th>
                                <td>{{ $inventoryAdjustment->item->description ?: 'No description' }}</td>
                            </tr>
                            <tr>
                                <th>Stock Value:</th>
                                <td>
                                    <strong>${{ number_format(($inventoryAdjustment->item->price ?? 0) * $inventoryAdjustment->item->quantity, 2) }}</strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history"></i> Adjustment Timeline
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6>Adjustment Recorded</h6>
                            <p class="text-muted mb-1">{{ $inventoryAdjustment->created_at->format('M d, Y g:i A') }}</p>
                            <small class="text-muted">Initial inventory adjustment entry created</small>
                        </div>
                    </div>
                    
                    @if($inventoryAdjustment->updated_at != $inventoryAdjustment->created_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <h6>Adjustment Modified</h6>
                            <p class="text-muted mb-1">{{ $inventoryAdjustment->updated_at->format('M d, Y g:i A') }}</p>
                            <small class="text-muted">Adjustment details were updated</small>
                        </div>
                    </div>
                    @endif

                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6>Stock Updated</h6>
                            <p class="text-muted mb-1">{{ $inventoryAdjustment->created_at->format('M d, Y g:i A') }}</p>
                            <small class="text-muted">
                                Item stock adjusted from {{ $inventoryAdjustment->system_quantity }} to {{ $inventoryAdjustment->actual_quantity }} units
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Quick Stats Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Adjustment Summary</h6>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-6 border-end">
                        <h4 class="text-primary">{{ $inventoryAdjustment->system_quantity }}</h4>
                        <small class="text-muted">System Qty</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-info">{{ $inventoryAdjustment->actual_quantity }}</h4>
                        <small class="text-muted">Actual Qty</small>
                    </div>
                </div>
                
                <div class="text-center">
                    <div class="mb-2">
                        @php
                            $adjustment = $inventoryAdjustment->actual_quantity - $inventoryAdjustment->system_quantity;
                            $adjustmentType = $adjustment >= 0 ? 'increase' : 'decrease';
                        @endphp
                        <h3 class="{{ $adjustment >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $adjustment >= 0 ? '+' : '' }}{{ $adjustment }}
                        </h3>
                        <span class="badge {{ $adjustmentType == 'increase' ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ ucfirst($adjustmentType) }}
                        </span>
                    </div>
                </div>

                <hr>

                <div class="text-center">
                    <h5 class="text-warning">${{ number_format($inventoryAdjustment->financial_impact, 2) }}</h5>
                    <small class="text-muted">Financial Impact</small>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('inventory-adjustments.edit', $inventoryAdjustment) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil-square"></i> Edit Adjustment
                    </a>
                    <a href="{{ route('items.show', $inventoryAdjustment->item) }}" class="btn btn-outline-info">
                        <i class="bi bi-box-seam"></i> View Item Details
                    </a>
                    <a href="{{ route('inventory-adjustments.create') }}?item={{ $inventoryAdjustment->item->id }}" class="btn btn-outline-success">
                        <i class="bi bi-plus-circle"></i> New Adjustment for Item
                    </a>
                </div>
            </div>
        </div>

        <!-- Calculation Details -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Calculation Details</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label small">Adjustment Calculation</label>
                    <div class="p-2 bg-light rounded">
                        <code>{{ $inventoryAdjustment->actual_quantity }} - {{ $inventoryAdjustment->system_quantity }} = {{ $inventoryAdjustment->actual_quantity - $inventoryAdjustment->system_quantity }}</code>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small">Financial Impact</label>
                    <div class="p-2 bg-light rounded">
                        <code>{{ abs($inventoryAdjustment->actual_quantity - $inventoryAdjustment->system_quantity) }} × ${{ number_format($inventoryAdjustment->item->price ?? 0, 2) }} = ${{ number_format($inventoryAdjustment->financial_impact, 2) }}</code>
                    </div>
                </div>

                <div class="alert alert-info alert-sm">
                    <small><i class="bi bi-info-circle"></i> Financial impact represents the value of inventory variance at item's current price.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-item:not(:last-child):before {
    content: '';
    position: absolute;
    left: -22px;
    top: 20px;
    height: calc(100% + 10px);
    width: 2px;
    background-color: #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: -26px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content h6 {
    margin-bottom: 5px;
    color: #495057;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.alert-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

.badge {
    font-size: 0.8em;
}
</style>
@endsection