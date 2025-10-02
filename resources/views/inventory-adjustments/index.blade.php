@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Inventory Adjustments</h2>
    <div>
        <a href="{{ route('inventory-adjustments.create') }}" class="btn btn-success me-2">
            <i class="bi bi-plus-circle"></i> Record Adjustment
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Total Adjustments</h6>
                        <h4>{{ $stats['total_adjustments'] }}</h4>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-clipboard-data fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Total Impact</h6>
                        <h4>${{ number_format($stats['total_financial_impact'], 2) }}</h4>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-currency-dollar fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">This Month</h6>
                        <h4>${{ number_format($stats['monthly_financial_impact'], 2) }}</h4>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-calendar-month fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Most Common</h6>
                        <h5>{{ $stats['adjustments_by_reason']->first()->reason ?? 'N/A' }}</h5>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-graph-up fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Adjustments by Reason Chart -->
@if($stats['adjustments_by_reason']->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Adjustments by Reason</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Reason</th>
                                <th class="text-end">Count</th>
                                <th class="text-end">Total Quantity</th>
                                <th class="text-end">Financial Impact</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['adjustments_by_reason'] as $stat)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($stat->reason) }}</span>
                                </td>
                                <td class="text-end">{{ $stat->count }}</td>
                                <td class="text-end">{{ number_format($stat->total_quantity) }}</td>
                                <td class="text-end">${{ number_format($stat->total_impact, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-body">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Item</th>
                    <th class="text-end">System Qty</th>
                    <th class="text-end">Actual Qty</th>
                    <th class="text-end">Adjustment</th>
                    <th>Type</th>
                    <th>Reason</th>
                    <th class="text-end">Financial Impact</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($adjustments as $adjustment)
                <tr>
                    <td>
                        <span class="text-muted">{{ $adjustment->adjustment_date->format('M d, Y') }}</span>
                    </td>
                    <td>
                        <div>
                            <strong>{{ $adjustment->item->name }}</strong>
                            <br><small class="text-muted">Code: {{ $adjustment->item->code }}</small>
                        </div>
                    </td>
                    <td class="text-end">
                        <span class="badge bg-light text-dark">{{ $adjustment->system_quantity }}</span>
                    </td>
                    <td class="text-end">
                        <span class="badge bg-info">{{ $adjustment->actual_quantity }}</span>
                    </td>
                    <td class="text-end">
                        @php
                            $adjustmentQty = $adjustment->actual_quantity - $adjustment->system_quantity;
                            $badgeClass = $adjustmentQty >= 0 ? 'bg-success' : 'bg-danger';
                            $icon = $adjustmentQty >= 0 ? 'bi-arrow-up' : 'bi-arrow-down';
                        @endphp
                        <span class="badge {{ $badgeClass }}">
                            <i class="{{ $icon }}"></i> {{ $adjustmentQty >= 0 ? '+' : '' }}{{ $adjustmentQty }}
                        </span>
                    </td>
                    <td>
                        @php
                            $adjustmentType = ($adjustment->actual_quantity - $adjustment->system_quantity) >= 0 ? 'increase' : 'decrease';
                        @endphp
                        <span class="badge {{ $adjustmentType == 'increase' ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ ucfirst($adjustmentType) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-secondary">
                            {{ \App\Models\InventoryAdjustment::REASONS[$adjustment->reason] ?? ucfirst($adjustment->reason) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <strong>${{ number_format($adjustment->financial_impact, 2) }}</strong>
                    </td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <a href="{{ route('inventory-adjustments.show', $adjustment) }}" 
                               class="btn btn-sm btn-outline-info" title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('inventory-adjustments.edit', $adjustment) }}" 
                               class="btn btn-sm btn-outline-primary" title="Edit Adjustment">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('inventory-adjustments.destroy', $adjustment) }}" 
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this inventory adjustment?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete Adjustment">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        No inventory adjustments found.
                        <br><a href="{{ route('inventory-adjustments.create') }}" class="btn btn-sm btn-success mt-2">
                            Record First Adjustment
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted">
        Showing {{ $adjustments->firstItem() ?? 0 }} to {{ $adjustments->lastItem() ?? 0 }} of {{ $adjustments->total() }} results
    </div>
    <div>
        {{ $adjustments->links('pagination.bootstrap-5') }}
    </div>
</div>

@endsection