@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>{{ $item->name }}</h2>
        <div class="text-muted">
            <code class="bg-light px-2 py-1 rounded">{{ $item->code }}</code>
            @if($item->trashed())
                <span class="badge bg-warning ms-2">Deleted Item</span>
            @endif
        </div>
    </div>
    <div>
        <a href="{{ route('items.index') }}" class="btn btn-secondary">
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
    <!-- Main Item Information -->
    <div class="col-md-8">
        <!-- Basic Information Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle"></i> Basic Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Name:</th>
                                <td>{{ $item->name }}</td>
                            </tr>
                            <tr>
                                <th>Code:</th>
                                <td><code class="bg-light px-2 py-1 rounded">{{ $item->code }}</code></td>
                            </tr>
                            <tr>
                                <th>Description:</th>
                                <td>{{ $item->description ?: 'No description provided' }}</td>
                            </tr>
                            <tr>
                                <th>Current Stock:</th>
                                <td>
                                    <span class="badge fs-6 {{ $item->stock_badge_class }}">
                                        {{ $item->quantity }} units
                                    </span>
                                    <br><small class="text-muted">Status: {{ $item->stock_status }}</small>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Date Added:</th>
                                <td>{{ $item->created_at->format('M d, Y g:i A') }}</td>
                            </tr>
                            <tr>
                                <th>Last Updated:</th>
                                <td>{{ $item->updated_at->format('M d, Y g:i A') }}</td>
                            </tr>
                            <tr>
                                <th>Total Value:</th>
                                <td>
                                    <strong class="text-success">${{ number_format($item->total_value, 2) }}</strong>
                                    <br><small class="text-muted">{{ $item->quantity }} × ${{ number_format($item->primary_price, 2) }}</small>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing Information Card -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-currency-dollar"></i> Pricing Information
                </h5>
           
            </div>
            <div class="card-body">
                @if($item->itemPrices->count() > 0)
                    <div class="row">
                        @foreach($item->itemPrices->where('is_active', true) as $price)
                            <div class="col-md-4 mb-3">
                                <div class="card border {{ $price->is_default ? 'border-warning bg-warning bg-opacity-10' : 'border-light' }}">
                                    <div class="card-body text-center">
                                        @if($price->is_default)
                                            <div class="mb-2">
                                                <i class="bi bi-star-fill text-warning"></i>
                                                <small class="text-warning fw-bold">DEFAULT</small>
                                            </div>
                                        @endif
                                        <h6 class="card-title">{{ $price->name }}</h6>
                                        <h4 class="text-primary">${{ number_format($price->price, 2) }}</h4>
                                        @if($price->unit)
                                            <small class="text-muted">per {{ $price->unit }}</small>
                                        @endif
                                        @if($price->description)
                                            <p class="small text-muted mt-2 mb-0">{{ $price->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($item->itemPrices->count() > 1)
                        <div class="alert alert-info">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <strong>Lowest Price</strong><br>
                                    ${{ number_format($item->itemPrices->where('is_active', true)->min('price'), 2) }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Highest Price</strong><br>
                                    ${{ number_format($item->itemPrices->where('is_active', true)->max('price'), 2) }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Average Price</strong><br>
                                    ${{ number_format($item->itemPrices->where('is_active', true)->avg('price'), 2) }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Price Range</strong><br>
                                    {{ number_format((($item->itemPrices->where('is_active', true)->max('price') - $item->itemPrices->where('is_active', true)->min('price')) / $item->itemPrices->where('is_active', true)->min('price')) * 100, 1) }}%
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <!-- Legacy Pricing Display -->
                    <div class="alert alert-warning">
                        <h6><i class="bi bi-exclamation-triangle"></i> Legacy Pricing System</h6>
                        <p class="mb-0">This item is using the old pricing system. Consider migrating to the new flexible pricing structure.</p>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Regular Price</h6>
                                    <h4 class="text-primary">${{ number_format($item->price, 2) }}</h4>
                                    <small class="text-muted">Standard retail price</small>
                                </div>
                            </div>
                        </div>
                        @if($item->operator_price && $item->operator_price > 0)
                        <div class="col-md-4">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Installer Price</h6>
                                    <h4 class="text-info">${{ number_format($item->operator_price, 2) }}</h4>
                                    <small class="text-muted">For certified installers</small>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($item->base_price && $item->base_price > 0)
                        <div class="col-md-4">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Base Price</h6>
                                    <h4 class="text-success">${{ number_format($item->base_price, 2) }}</h4>
                                    <small class="text-muted">Wholesale price</small>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Sales History Card -->
        @if($item->orderItems->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-graph-up"></i> Sales History
                </h5>
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
                                <th>Price Type</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($item->orderItems->sortByDesc('created_at')->take(10) as $orderItem)
                            <tr>
                                <td>{{ $orderItem->sale->sale_date }}</td>
                                <td>
                                    <a href="{{ route('sales.show', $orderItem->sale) }}" class="text-decoration-none">
                                        {{ $orderItem->sale->customer->name ?? 'Unknown Customer' }}
                                    </a>
                                </td>
                                <td>{{ $orderItem->quantity }}</td>
                                <td>${{ number_format($orderItem->unit_price, 2) }}</td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $orderItem->price_type ?? 'Regular' }}</span>
                                </td>
                                <td class="text-end">${{ number_format($orderItem->line_total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <th colspan="2">Total Sales</th>
                                <th>{{ $item->orderItems->sum('quantity') }} units</th>
                                <th>-</th>
                                <th>-</th>
                                <th class="text-end">${{ number_format($item->orderItems->sum('line_total'), 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @if($item->orderItems->count() > 10)
                    <div class="text-center mt-2">
                        <small class="text-muted">Showing latest 10 sales. Total {{ $item->orderItems->count() }} sales recorded.</small>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Quick Stats Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Quick Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 border-end">
                        <h4 class="text-primary">{{ $item->orderItems->sum('quantity') }}</h4>
                        <small class="text-muted">Total Sold</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">${{ number_format($item->orderItems->sum('line_total'), 2) }}</h4>
                        <small class="text-muted">Revenue</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6 border-end">
                        <h5 class="text-info">{{ $item->quantity }}</h5>
                        <small class="text-muted">In Stock</small>
                    </div>
                    <div class="col-6">
                        <h5 class="text-warning">${{ number_format($item->total_value, 2) }}</h5>
                        <small class="text-muted">Stock Value</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        @if(!$item->trashed())
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('items.edit', $item) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil-square"></i> Edit Item Details
                    </a>


                    <a href="{{ route('sales.create') }}?item={{ $item->id }}" class="btn btn-outline-info">
                        <i class="bi bi-plus-circle"></i> Create Sale
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Performance Insights -->
        @if($item->orderItems->count() > 0)
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Performance Insights</h6>
            </div>
            <div class="card-body">
                @php
                    $totalSold = $item->orderItems->sum('quantity');
                    $totalRevenue = $item->orderItems->sum('line_total');
                    $avgSalePrice = $totalSold > 0 ? $totalRevenue / $totalSold : 0;
                    $lastSale = $item->orderItems->sortByDesc('created_at')->first();
                    $monthsSinceLastSale = $lastSale ? $lastSale->created_at->diffInMonths(now()) : 0;
                @endphp

                <div class="mb-3">
                    <label class="form-label small">Average Sale Price</label>
                    <h5 class="text-primary">${{ number_format($avgSalePrice, 2) }}</h5>
                </div>

                <div class="mb-3">
                    <label class="form-label small">Last Sale</label>
                    @if($lastSale)
                        <h6>{{ $lastSale->created_at->diffForHumans() }}</h6>
                        <small class="text-muted">{{ $lastSale->created_at}}</small>
                    @else
                        <h6 class="text-muted">No sales yet</h6>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label small">Stock Turn Rate</label>
                    @if($item->quantity > 0 && $totalSold > 0)
                        @php $turnRate = ($totalSold / $item->quantity) * 100; @endphp
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ min($turnRate, 100) }}%"></div>
                        </div>
                        <small class="text-muted">{{ number_format($turnRate, 1) }}% of current stock sold historically</small>
                    @else
                        <p class="text-muted">No turn rate data</p>
                    @endif
                </div>

                @if($monthsSinceLastSale > 3 && $item->quantity > 0)
                    <div class="alert alert-warning alert-sm">
                        <small><i class="bi bi-exclamation-triangle"></i> Slow moving item - Last sale {{ $monthsSinceLastSale }} months ago</small>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Stock Update Modal -->
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('items.updateStock', $item) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <input type="text" id="stockItemName" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="text" id="currentStock" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Stock Quantity</label>
                        <input type="number" name="quantity" id="newStock" class="form-control" min="0" required>
                    </div>
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
function showStockModal(itemId, itemName, currentQuantity) {
    document.getElementById('stockItemName').value = itemName;
    document.getElementById('currentStock').value = currentQuantity + ' units';
    document.getElementById('newStock').value = currentQuantity;

    const modal = new bootstrap.Modal(document.getElementById('stockModal'));
    modal.show();
}
</script>
@endsection
