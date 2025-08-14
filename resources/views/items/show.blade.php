@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">{{ $item->name }}</h2>
        <div class="text-muted">
            <code class="bg-light px-2 py-1 rounded">{{ $item->code }}</code>
            @if($item->trashed())
                <span class="badge bg-warning ms-2">Deleted Item</span>
            @endif
        </div>
    </div>
    <div class="btn-group">
        <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Items
        </a>
        @if(!$item->trashed())
            <a href="{{ route('items.edit', $item) }}" class="btn btn-primary">
                <i class="bi bi-pencil-square"></i> Edit Item
            </a>
        @endif
    </div>
</div>

<div class="row">
    <!-- Item Details Card -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Item Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Item Name</label>
                            <div class="fw-bold">{{ $item->name }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Item Code</label>
                            <div><code class="bg-light px-2 py-1 rounded">{{ $item->code }}</code></div>
                        </div>
                        @if($item->description)
                            <div class="mb-3">
                                <label class="form-label text-muted">Description</label>
                                <div>{{ $item->description }}</div>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Current Stock</label>
                            <div>
                                @php
                                    $stockClass = '';
                                    $stockIcon = 'bi-check-circle';
                                    if ($item->quantity == 0) {
                                        $stockClass = 'text-danger';
                                        $stockIcon = 'bi-x-circle';
                                    } elseif ($item->quantity < 10) {
                                        $stockClass = 'text-warning';
                                        $stockIcon = 'bi-exclamation-triangle';
                                    } elseif ($item->quantity > 100) {
                                        $stockClass = 'text-info';
                                        $stockIcon = 'bi-arrow-up-circle';
                                    }
                                @endphp
                                <span class="badge fs-6 {{ $item->quantity == 0 ? 'bg-danger' : ($item->quantity < 10 ? 'bg-warning text-dark' : 'bg-success') }}">
                                    <i class="{{ $stockIcon }} me-1"></i>{{ $item->quantity }} units
                                </span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Date Added</label>
                            <div>{{ $item->created_at->format('M d, Y \a\t g:i A') }}</div>
                        </div>
                        @if($item->updated_at != $item->created_at)
                            <div class="mb-3">
                                <label class="form-label text-muted">Last Updated</label>
                                <div>{{ $item->updated_at->format('M d, Y \a\t g:i A') }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing Information Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-currency-dollar"></i> Pricing Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <div class="text-muted small">End User Price</div>
                            <div class="fs-4 fw-bold text-primary">${{ number_format($item->price, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <div class="text-muted small">Reseller Price</div>
                            <div class="fs-4 fw-bold text-info">
                                @if($item->base_price)
                                    ${{ number_format($item->base_price, 2) }}
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <div class="text-muted small">Installer Price</div>
                            <div class="fs-4 fw-bold text-warning">
                                @if($item->operator_price)
                                    ${{ number_format($item->operator_price, 2) }}
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($item->base_price || $item->operator_price)
                    <div class="mt-3">
                        <h6>Price Margins</h6>
                        <div class="row">
                            @if($item->base_price)
                                <div class="col-md-6">
                                    <small class="text-muted">Reseller Discount:</small>
                                    <span class="badge bg-info">{{ number_format((($item->price - $item->base_price) / $item->price) * 100, 1) }}%</span>
                                </div>
                            @endif
                            @if($item->operator_price)
                                <div class="col-md-6">
                                    <small class="text-muted">Installer Discount:</small>
                                    <span class="badge bg-warning">{{ number_format((($item->price - $item->operator_price) / $item->price) * 100, 1) }}%</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sales History Card -->
        @if($item->orderItems->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Recent Sales History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($item->orderItems->take(10) as $orderItem)
                                    <tr>
                                        <td>{{ $orderItem->created_at->format('M d, Y') }}</td>
                                        <td>
                                            @if($orderItem->sale && $orderItem->sale->customer)
                                                {{ $orderItem->sale->customer->name }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $orderItem->quantity }}</td>
                                        <td>${{ number_format($orderItem->unit_price, 2) }}</td>
                                        <td>${{ number_format($orderItem->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($item->orderItems->count() > 10)
                        <div class="text-center mt-2">
                            <small class="text-muted">Showing 10 most recent sales</small>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Statistics Sidebar -->
    <div class="col-md-4">
        <!-- Inventory Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Inventory Stats</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-12 mb-3">
                        <div class="border rounded p-3">
                            <div class="text-muted small">Current Value</div>
                            <div class="fs-5 fw-bold text-success">
                                ${{ number_format($item->price * $item->quantity, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Statistics -->
        @if($salesStats['total_sold'] > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-graph-up-arrow"></i> Sales Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Total Sold:</span>
                            <span class="fw-bold">{{ number_format($salesStats['total_sold']) }} units</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Total Revenue:</span>
                            <span class="fw-bold text-success">${{ number_format($salesStats['total_revenue'], 2) }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Avg Sale Price:</span>
                            <span class="fw-bold">${{ number_format($salesStats['avg_sale_price'], 2) }}</span>
                        </div>
                    </div>
                    @if($salesStats['last_sale_date'])
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Last Sale:</span>
                                <span class="fw-bold">{{ \Carbon\Carbon::parse($salesStats['last_sale_date'])->format('M d, Y') }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Quick Actions -->
        @if(!$item->trashed())
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" 
                                class="btn btn-outline-primary btn-sm"
                                onclick="showStockModal({{ $item->id }}, '{{ $item->name }}', {{ $item->quantity }})">
                            <i class="bi bi-box-arrow-up-right"></i> Update Stock
                        </button>
                        <a href="{{ route('items.edit', $item) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-pencil-square"></i> Edit Item
                        </a>
                        <form action="{{ route('items.destroy', $item) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Are you sure you want to delete {{ $item->name }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                <i class="bi bi-trash"></i> Delete Item
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Quick Stock Update Modal -->
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="stockForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <input type="text" class="form-control" id="stockItemName" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="current_stock" class="form-label">Current Stock</label>
                        <input type="number" class="form-control" id="current_stock" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="stock_action" class="form-label">Action</label>
                        <select class="form-select" id="stock_action" name="action">
                            <option value="add">Add Stock</option>
                            <option value="remove">Remove Stock</option>
                            <option value="set">Set Stock</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="note" class="form-label">Note (Optional)</label>
                        <input type="text" class="form-control" id="note" name="note" placeholder="Reason for stock change">
                    </div>
                    <div id="stock_preview" class="alert" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showStockModal(itemId, itemName, currentStock) {
    document.getElementById('stockItemName').value = itemName;
    document.getElementById('current_stock').value = currentStock;
    document.getElementById('stockForm').action = `/items/${itemId}/update-stock`;
    document.getElementById('quantity').value = '';
    document.getElementById('note').value = '';
    document.getElementById('stock_action').value = 'add';
    
    // Hide preview
    const preview = document.getElementById('stock_preview');
    preview.style.display = 'none';

    const modal = new bootstrap.Modal(document.getElementById('stockModal'));
    modal.show();
}

function updateStockPreview() {
    const action = document.getElementById('stock_action').value;
    const quantity = parseInt(document.getElementById('quantity').value) || 0;
    const currentStock = parseInt(document.getElementById('current_stock').value) || 0;
    const preview = document.getElementById('stock_preview');

    if (quantity === 0) {
        preview.style.display = 'none';
        return;
    }

    let newStock;
    let actionText;

    switch (action) {
        case 'add':
            newStock = currentStock + quantity;
            actionText = `Adding ${quantity} units`;
            break;
        case 'remove':
            newStock = Math.max(0, currentStock - quantity);
            actionText = `Removing ${quantity} units`;
            if (currentStock < quantity) {
                actionText += ` (limited to available stock)`;
            }
            break;
        case 'set':
            newStock = quantity;
            actionText = `Setting stock to ${quantity} units`;
            break;
    }

    preview.innerHTML = `
        <strong>${actionText}</strong><br>
        Current Stock: ${currentStock} → New Stock: ${newStock}
        ${newStock === 0 ? '<br><span class="text-danger">⚠️ This will result in zero stock</span>' : ''}
        ${newStock < 10 && newStock > 0 ? '<br><span class="text-warning">⚠️ This will result in low stock</span>' : ''}
    `;

    preview.className = `alert ${newStock === 0 ? 'alert-danger' : (newStock < 10 ? 'alert-warning' : 'alert-success')}`;
    preview.style.display = 'block';
}

// Add event listeners when page loads
document.addEventListener('DOMContentLoaded', function() {
    const stockActionSelect = document.getElementById('stock_action');
    const quantityInput = document.getElementById('quantity');

    if (stockActionSelect && quantityInput) {
        stockActionSelect.addEventListener('change', updateStockPreview);
        quantityInput.addEventListener('input', updateStockPreview);
    }
});
</script>

@endsection