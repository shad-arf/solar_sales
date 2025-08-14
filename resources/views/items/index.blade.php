@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Items Inventory</h2>
    <div>
        <a href="{{ route('items.create') }}" class="btn btn-success me-2">
            <i class="bi bi-plus-circle"></i> Add Item
        </a>
        <a href="{{ route('items.lowStock') }}" class="btn btn-warning me-2">
            <i class="bi bi-exclamation-triangle"></i> Low Stock
        </a>
        <a href="{{ route('items.outOfStock') }}" class="btn btn-danger me-2">
            <i class="bi bi-x-circle"></i> Out of Stock
        </a>
        <a href="{{ route('items.trashed') }}" class="btn btn-secondary">
            <i class="bi bi-trash"></i> Deleted Items
        </a>
    </div>
</div>

<!-- Search and Filters Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('items.index') }}" id="searchForm">
            <div class="row g-3">
                <!-- Search Box -->
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text"
                               class="form-control"
                               id="search"
                               name="search"
                               placeholder="Item name, code, description..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Stock Status Filter -->
                <div class="col-md-2">
                    <label for="stock_status" class="form-label">Stock Status</label>
                    <select class="form-select" id="stock_status" name="stock_status">
                        <option value="">All Items</option>
                        <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock (&lt;10)</option>
                        <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                        <option value="overstocked" {{ request('stock_status') == 'overstocked' ? 'selected' : '' }}>Overstocked (&gt;100)</option>
                    </select>
                </div>

                <!-- Price Range -->
                <div class="col-md-2">
                    <label for="price_min" class="form-label">Min Price</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number"
                               class="form-control"
                               id="price_min"
                               name="price_min"
                               step="0.01"
                               placeholder="0.00"
                               value="{{ request('price_min') }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <label for="price_max" class="form-label">Max Price</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number"
                               class="form-control"
                               id="price_max"
                               name="price_max"
                               step="0.01"
                               placeholder="999.99"
                               value="{{ request('price_max') }}">
                    </div>
                </div>

                <!-- Sort Options -->
                <div class="col-md-2">
                    <label for="sort_by" class="form-label">Sort By</label>
                    <select class="form-select" id="sort_by" name="sort_by">
                        <option value="name" {{ request('sort_by', 'name') == 'name' ? 'selected' : '' }}>Name</option>
                        <option value="code" {{ request('sort_by') == 'code' ? 'selected' : '' }}>Code</option>
                        <option value="price" {{ request('sort_by') == 'price' ? 'selected' : '' }}>End user</option>
                        <option value="quantity" {{ request('sort_by') == 'quantity' ? 'selected' : '' }}>Quantity</option>
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date Added</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <!-- Quantity Range -->
                <div class="col-md-2">
                    <label for="quantity_min" class="form-label">Min Quantity</label>
                    <input type="number"
                           class="form-control"
                           id="quantity_min"
                           name="quantity_min"
                           placeholder="0"
                           value="{{ request('quantity_min') }}">
                </div>

                <div class="col-md-2">
                    <label for="quantity_max" class="form-label">Max Quantity</label>
                    <input type="number"
                           class="form-control"
                           id="quantity_max"
                           name="quantity_max"
                           placeholder="1000"
                           value="{{ request('quantity_max') }}">
                </div>

                <!-- Sort Direction -->
                <div class="col-md-2">
                    <label for="sort_direction" class="form-label">Direction</label>
                    <select class="form-select" id="sort_direction" name="sort_direction">
                        <option value="asc" {{ request('sort_direction', 'asc') == 'asc' ? 'selected' : '' }}>A-Z / Low-High</option>
                        <option value="desc" {{ request('sort_direction') == 'desc' ? 'selected' : '' }}>Z-A / High-Low</option>
                    </select>
                </div>

                <!-- Results Per Page -->
                <div class="col-md-2">
                    <label for="per_page" class="form-label">Per Page</label>
                    <select class="form-select" id="per_page" name="per_page">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                    <button type="button" class="btn btn-outline-info" onclick="toggleAdvanced()">
                        <i class="bi bi-gear"></i> Advanced
                    </button>
                    <a href="{{ route('items.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                       class="btn btn-outline-success"
                       title="Export filtered results to CSV">
                        <i class="bi bi-download"></i> Export CSV
                    </a>
                </div>
            </div>

            <!-- Advanced Filters (Initially Hidden) -->
            <div id="advancedFilters" class="row g-3 mt-2" style="display: none;">
                <div class="col-12">
                    <hr>
                    <h6 class="text-muted">Advanced Filters</h6>
                </div>

                <!-- Price Type Filters -->
                <div class="col-md-3">
                    <label for="price_type" class="form-label">Price Type</label>
                    <select class="form-select" id="price_type" name="price_type">
                        <option value="">All Prices</option>
                        <option value="regular" {{ request('price_type') == 'regular' ? 'selected' : '' }}>End user</option>
                        <option value="base" {{ request('price_type') == 'base' ? 'selected' : '' }}>Reseller</option>
                        <option value="operator" {{ request('price_type') == 'operator' ? 'selected' : '' }}>Installer</option>
                    </select>
                </div>

                <!-- Include Deleted -->
                <div class="col-md-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="include_deleted" name="include_deleted" value="1"
                               {{ request('include_deleted') ? 'checked' : '' }}>
                        <label class="form-check-label" for="include_deleted">
                            Include Deleted Items
                        </label>
                    </div>
                </div>

                <!-- Date Added Range -->
                <div class="col-md-2">
                    <label for="created_from" class="form-label">Added From</label>
                    <input type="date"
                           class="form-control"
                           id="created_from"
                           name="created_from"
                           value="{{ request('created_from') }}">
                </div>

                <div class="col-md-2">
                    <label for="created_to" class="form-label">Added To</label>
                    <input type="date"
                           class="form-control"
                           id="created_to"
                           name="created_to"
                           value="{{ request('created_to') }}">
                </div>

                <!-- Quick Stock Actions -->
                <div class="col-md-2">
                    <label class="form-label">Quick Filters</label>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="filterByStock('low_stock')">
                            Low Stock
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="filterByStock('out_of_stock')">
                            No Stock
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Results Summary -->
@if(request()->hasAny(['search', 'stock_status', 'price_min', 'price_max', 'quantity_min', 'quantity_max']))
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    Showing {{ $items->total() }} result(s)
    @if(request('search'))
        for search: "<strong>{{ request('search') }}</strong>"
    @endif
    @if(request('stock_status'))
        | Status: <strong>{{ ucfirst(str_replace('_', ' ', request('stock_status'))) }}</strong>
    @endif
    @if(request('price_min') || request('price_max'))
        | Price:
        @if(request('price_min')) from <strong>${{ request('price_min') }}</strong> @endif
        @if(request('price_max')) to <strong>${{ request('price_max') }}</strong> @endif
    @endif
    @if(request('quantity_min') || request('quantity_max'))
        | Quantity:
        @if(request('quantity_min')) from <strong>{{ request('quantity_min') }}</strong> @endif
        @if(request('quantity_max')) to <strong>{{ request('quantity_max') }}</strong> @endif
    @endif
</div>
@endif

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total Items</h5>
                        <h3>{{ $stats['total_items'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-box-seam fs-1"></i>
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
                        <h5 class="card-title">Low Stock</h5>
                        <h3>{{ $stats['low_stock'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Out of Stock</h5>
                        <h3>{{ $stats['out_of_stock'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-x-circle fs-1"></i>
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
                        <h5 class="card-title">Total Value</h5>
                        <h3>${{ number_format($stats['total_value'], 2) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-currency-dollar fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'code', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}"
                           class="text-decoration-none text-dark">
                            Code
                            @if(request('sort_by') == 'code')
                                <i class="bi bi-chevron-{{ request('sort_direction', 'asc') == 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}"
                           class="text-decoration-none text-dark">
                            Name
                            @if(request('sort_by', 'name') == 'name')
                                <i class="bi bi-chevron-{{ request('sort_direction', 'asc') == 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-end">
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'price', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}"
                           class="text-decoration-none text-dark">
                            Pricing ($)
                            @if(request('sort_by') == 'price')
                                <i class="bi bi-chevron-{{ request('sort_direction', 'asc') == 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-end">
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'quantity', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}"
                           class="text-decoration-none text-dark">
                            Stock
                            @if(request('sort_by') == 'quantity')
                                <i class="bi bi-chevron-{{ request('sort_direction', 'asc') == 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-end">Value</th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}"
                           class="text-decoration-none text-dark">
                            Added
                            @if(request('sort_by') == 'created_at')
                                <i class="bi bi-chevron-{{ request('sort_direction', 'asc') == 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr class="{{ $item->trashed() ? 'table-secondary' : '' }}">
                    <td>
                        <code class="bg-light px-2 py-1 rounded">{{ $item->code }}</code>
                        @if($item->trashed())
                            <span class="badge bg-warning ms-1">Deleted</span>
                        @endif
                    </td>
                    <td>
                        <div>
                            <strong>{{ $item->name }}</strong>
                            @if($item->description)
                                <br><small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                            @endif
                        </div>
                    </td>
                    <td class="text-end">
                        <div class="small">
                            <div><strong>${{ number_format($item->price, 2) }}</strong> <span class="text-muted">End user</span></div>
                            @if($item->base_price)
                                <div><span class="text-info">${{ number_format($item->base_price, 2) }}</span> <span class="text-muted">Reseller</span></div>
                            @endif
                            @if($item->operator_price)
                                <div><span class="text-warning">${{ number_format($item->operator_price, 2) }}</span> <span class="text-muted">Installer</span></div>
                            @endif
                        </div>
                    </td>
                    <td class="text-end">
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
                            <i class="{{ $stockIcon }} me-1"></i>{{ $item->quantity }}
                        </span>
                    </td>
                    <td class="text-end">
                        <strong>${{ number_format($item->price * $item->quantity, 2) }}</strong>
                    </td>
                    <td>
                        <small class="text-muted">{{ $item->created_at->format('M d, Y') }}</small>
                    </td>
                    <td class="text-center">
                        @if($item->trashed())
                            <div class="btn-group" role="group">
                                <form action="{{ route('items.restore', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-warning" title="Restore Item">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </form>
                                <form action="{{ route('items.forceDelete', $item->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Permanently delete this item?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete Forever">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="btn-group" role="group">
                                <a href="{{ route('items.show', $item) }}"
                                   class="btn btn-sm btn-outline-info"
                                   title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('items.edit', $item) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="Edit Item">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        title="Quick Stock Update"
                                        onclick="showStockModal({{ $item->id }}, '{{ $item->name }}', {{ $item->quantity }})">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </button>
                                <form action="{{ route('items.destroy', $item) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Soft delete {{ $item->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete Item">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        @if(request()->hasAny(['search', 'stock_status', 'price_min', 'price_max']))
                            No items found matching your criteria.
                            <br><a href="{{ route('items.index') }}" class="btn btn-sm btn-outline-primary mt-2">Clear Filters</a>
                        @else
                            No items found.
                            <br><a href="{{ route('items.create') }}" class="btn btn-sm btn-success mt-2">Add First Item</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted">
        Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} results
    </div>
    <div>
        {{ $items->appends(request()->query())->links() }}
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
// Main functionality
function toggleAdvanced() {
    const advanced = document.getElementById('advancedFilters');
    const btn = event.target.closest('button');

    if (advanced.style.display === 'none') {
        advanced.style.display = 'flex';
        btn.innerHTML = '<i class="bi bi-gear-fill"></i> Hide Advanced';
        btn.classList.remove('btn-outline-info');
        btn.classList.add('btn-info');
    } else {
        advanced.style.display = 'none';
        btn.innerHTML = '<i class="bi bi-gear"></i> Advanced';
        btn.classList.remove('btn-info');
        btn.classList.add('btn-outline-info');
    }
}

function filterByStock(status) {
    document.getElementById('stock_status').value = status;
    document.getElementById('searchForm').submit();
}

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

// Auto-submit functionality and event listeners
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('search');
    let searchTimeout;

    // Auto-submit on dropdown changes
    const autoSubmitFields = ['stock_status', 'sort_by', 'sort_direction', 'per_page', 'price_type'];

    autoSubmitFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
            field.addEventListener('change', function() {
                searchForm.submit();
            });
        }
    });

    // Debounced search input
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                if (searchInput.value.length >= 2 || searchInput.value.length === 0) {
                    searchForm.submit();
                }
            }, 500);
        });
    }

    // Price range validation
    const priceMin = document.getElementById('price_min');
    const priceMax = document.getElementById('price_max');

    if (priceMin && priceMax) {
        priceMin.addEventListener('change', function() {
            if (priceMax.value && parseFloat(priceMin.value) > parseFloat(priceMax.value)) {
                priceMax.value = priceMin.value;
            }
        });

        priceMax.addEventListener('change', function() {
            if (priceMin.value && parseFloat(priceMax.value) < parseFloat(priceMin.value)) {
                priceMin.value = priceMax.value;
            }
        });
    }

    // Quantity range validation
    const quantityMin = document.getElementById('quantity_min');
    const quantityMax = document.getElementById('quantity_max');

    if (quantityMin && quantityMax) {
        quantityMin.addEventListener('change', function() {
            if (quantityMax.value && parseInt(quantityMin.value) > parseInt(quantityMax.value)) {
                quantityMax.value = quantityMin.value;
            }
        });

        quantityMax.addEventListener('change', function() {
            if (quantityMin.value && parseInt(quantityMax.value) < parseInt(quantityMin.value)) {
                quantityMin.value = quantityMax.value;
            }
        });
    }

    // Date range validation
    const createdFrom = document.getElementById('created_from');
    const createdTo = document.getElementById('created_to');

    if (createdFrom && createdTo) {
        createdFrom.addEventListener('change', function() {
            if (createdTo.value && createdFrom.value > createdTo.value) {
                createdTo.value = createdFrom.value;
            }
        });

        createdTo.addEventListener('change', function() {
            if (createdFrom.value && createdTo.value < createdFrom.value) {
                createdFrom.value = createdTo.value;
            }
        });
    }

    // Show advanced filters if any advanced field has value
    const advancedFields = ['include_deleted', 'created_from', 'created_to', 'price_type'];
    const hasAdvancedValue = advancedFields.some(field => {
        const element = document.querySelector(`[name="${field}"]`);
        return element && (element.type === 'checkbox' ? element.checked : element.value);
    });

    if (hasAdvancedValue) {
        toggleAdvanced();
    }

    // Stock modal preview functionality
    const stockActionSelect = document.getElementById('stock_action');
    const quantityInput = document.getElementById('quantity');

    if (stockActionSelect && quantityInput) {
        stockActionSelect.addEventListener('change', updateStockPreview);
        quantityInput.addEventListener('input', updateStockPreview);
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }

        // Escape to clear search
        if (e.key === 'Escape' && document.activeElement === searchInput) {
            searchInput.value = '';
            searchForm.submit();
        }
    });
});
</script>

@endsection