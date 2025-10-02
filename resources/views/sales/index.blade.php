@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Sales</h2>
    <a href="{{ route('sales.create') }}" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Record Sale
    </a>
</div>

<!-- Search and Filters Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('sales.index') }}" id="searchForm">
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
                               placeholder="Customer name, invoice code, item name..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Customer Filter -->
                <div class="col-md-3">
                    <label for="customer_id" class="form-label">Customer</label>
                    <select class="form-select" id="customer_id" name="customer_id">
                        <option value="">All Customers</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}"
                                    {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range -->
                <div class="col-md-2">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date"
                           class="form-control"
                           id="date_from"
                           name="date_from"
                           value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date"
                           class="form-control"
                           id="date_to"
                           name="date_to"
                           value="{{ request('date_to') }}">
                </div>

                <!-- Payment Status -->
                <div class="col-md-1">
                    <label for="payment_status" class="form-label">Status</label>
                    <select class="form-select" id="payment_status" name="payment_status">
                        <option value="">All</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="overpaid" {{ request('payment_status') == 'overpaid' ? 'selected' : '' }}>Overpaid</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <!-- Amount Range -->
                <div class="col-md-2">
                    <label for="amount_min" class="form-label">Min Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number"
                               class="form-control"
                               id="amount_min"
                               name="amount_min"
                               step="0.01"
                               placeholder="0.00"
                               value="{{ request('amount_min') }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <label for="amount_max" class="form-label">Max Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number"
                               class="form-control"
                               id="amount_max"
                               name="amount_max"
                               step="0.01"
                               placeholder="9999.99"
                               value="{{ request('amount_max') }}">
                    </div>
                </div>

                <!-- Sort Options -->
                <div class="col-md-2">
                    <label for="sort_by" class="form-label">Sort By</label>
                    <select class="form-select" id="sort_by" name="sort_by">
                        <option value="sale_date" {{ request('sort_by', 'sale_date') == 'sale_date' ? 'selected' : '' }}>Date</option>
                        <option value="total" {{ request('sort_by') == 'total' ? 'selected' : '' }}>Amount</option>
                        <option value="customer_name" {{ request('sort_by') == 'customer_name' ? 'selected' : '' }}>Customer</option>
                        <option value="code" {{ request('sort_by') == 'code' ? 'selected' : '' }}>Invoice Code</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="sort_direction" class="form-label">Direction</label>
                    <select class="form-select" id="sort_direction" name="sort_direction">
                        <option value="desc" {{ request('sort_direction', 'desc') == 'desc' ? 'selected' : '' }}>Newest First</option>
                        <option value="asc" {{ request('sort_direction') == 'asc' ? 'selected' : '' }}>Oldest First</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                    <button type="button" class="btn btn-outline-info" onclick="toggleAdvanced()">
                        <i class="bi bi-gear"></i> Advanced
                    </button>
                    <a href="{{ route('sales.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
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

                <!-- Include Deleted -->
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="include_deleted" name="include_deleted" value="1"
                               {{ request('include_deleted') ? 'checked' : '' }}>
                        <label class="form-check-label" for="include_deleted">
                            Include Deleted Sales
                        </label>
                    </div>
                </div>

                <!-- Outstanding Amount Range -->
                <div class="col-md-2">
                    <label for="outstanding_min" class="form-label">Min Outstanding</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number"
                               class="form-control"
                               id="outstanding_min"
                               name="outstanding_min"
                               step="0.01"
                               value="{{ request('outstanding_min') }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <label for="outstanding_max" class="form-label">Max Outstanding</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number"
                               class="form-control"
                               id="outstanding_max"
                               name="outstanding_max"
                               step="0.01"
                               value="{{ request('outstanding_max') }}">
                    </div>
                </div>

                <!-- Results Per Page -->
                <div class="col-md-2">
                    <label for="per_page" class="form-label">Results Per Page</label>
                    <select class="form-select" id="per_page" name="per_page">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Results Summary -->
@if(request()->hasAny(['search', 'customer_id', 'date_from', 'date_to', 'payment_status', 'amount_min', 'amount_max']))
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    Showing {{ $sales->total() }} result(s)
    @if(request('search'))
        for search: "<strong>{{ request('search') }}</strong>"
    @endif
    @if(request('customer_id'))
        @php $selectedCustomer = $customers->find(request('customer_id')) @endphp
        | Customer: <strong>{{ $selectedCustomer->name ?? 'Unknown' }}</strong>
    @endif
    @if(request('date_from') || request('date_to'))
        | Date:
        @if(request('date_from')) from <strong>{{ request('date_from') }}</strong> @endif
        @if(request('date_to')) to <strong>{{ request('date_to') }}</strong> @endif
    @endif
    @if(request('payment_status'))
        | Status: <strong>{{ ucfirst(request('payment_status')) }}</strong>
    @endif
</div>
@endif

<table class="table table-striped table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th>
                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'sale_date', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}"
                   class="text-decoration-none text-dark">
                    Date
                    @if(request('sort_by', 'sale_date') == 'sale_date')
                        <i class="bi bi-chevron-{{ request('sort_direction', 'desc') == 'desc' ? 'down' : 'up' }}"></i>
                    @endif
                </a>
            </th>
            <th>
                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'customer_name', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}"
                   class="text-decoration-none text-dark">
                    Customer
                    @if(request('sort_by') == 'customer_name')
                        <i class="bi bi-chevron-{{ request('sort_direction', 'desc') == 'desc' ? 'down' : 'up' }}"></i>
                    @endif
                </a>
            </th>
            <th>
                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'customer_type', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}"
                   class="text-decoration-none text-dark">
                    Type
                    @if(request('sort_by') == 'customer_type')
                        <i class="bi bi-chevron-{{ request('sort_direction', 'desc') == 'desc' ? 'down' : 'up' }}"></i>
                    @endif
                </a>
            </th>
            <th>Items</th>
            <th class="text-end">
                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'total', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}"
                   class="text-decoration-none text-dark">
                    Total ($)
                    @if(request('sort_by') == 'total')
                        <i class="bi bi-chevron-{{ request('sort_direction', 'desc') == 'desc' ? 'down' : 'up' }}"></i>
                    @endif
                </a>
            </th>
            <th class="text-end">
                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'discount', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}"
                   class="text-decoration-none text-dark">
                    Discount ($)
                    @if(request('sort_by') == 'discount')
                        <i class="bi bi-chevron-{{ request('sort_direction', 'desc') == 'desc' ? 'down' : 'up' }}"></i>
                    @endif
                </a>
            </th>
            <th class="text-end">Paid ($)</th>
            <th class="text-end">Outstanding ($)</th>
            <th>Invoice</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sales as $sale)
            @php
                $totalAmount    = (float) $sale->total;
                $paidAmount     = (float) $sale->paid_amount;
                $outstandingAmt = $totalAmount - $paidAmount;
                $itemsDisplay   = $sale->orderItems->map(fn($oi) => "{$oi->item->name} (×{$oi->quantity})")->implode(', ');
            @endphp

            <tr class="{{ $sale->trashed() ? 'table-secondary' : '' }}">
                <td>
                    {{ $sale->sale_date }}
                    @if($sale->trashed())
                        <span class="badge bg-warning ms-1">Deleted</span>
                    @endif
                </td>
                <td>{{ $sale->customer->name ?? '— Deleted Customer —' }}</td>
                <td>
                    @php
                        $customerTypeLabel = match($sale->customer_type ?? 'end_user') {
                            'installer' => 'Installer',
                            'reseller' => 'Reseller',
                            default => 'End User'
                        };
                        $badgeClass = match($sale->customer_type ?? 'end_user') {
                            'installer' => 'bg-warning',
                            'reseller' => 'bg-info',
                            default => 'bg-success'
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $customerTypeLabel }}</span>
                </td>
                <td style="min-width: 200px;">{{ $itemsDisplay ?: '—' }}</td>
                <td class="text-end">${{ number_format($totalAmount, 2) }}</td>
                <td class="text-end">${{ number_format($sale->discount ?? 0, 2) }}</td>
                <td class="text-end">${{ number_format($paidAmount, 2) }}</td>
                <td class="text-end
                    @if($outstandingAmt > 0) text-danger fw-bold
                    @elseif($outstandingAmt < 0) text-success fw-bold
                    @endif
                ">
                    ${{ number_format($outstandingAmt, 2) }}
                </td>

                <td>
                    @if(! $sale->trashed())
                        <a href="{{ route('sales.show', $sale->id) }}"
                           class="btn btn-sm btn-outline-primary"
                           title="View / Print Invoice">
                            <i class="bi bi-receipt"></i>
                        </a>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    @if($sale->trashed())
                        <form action="{{ route('sales.restore', $sale->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-warning" title="Restore Sale">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </form>
                        <form action="{{ route('sales.forceDelete', $sale->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Permanently delete this sale?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" title="Delete Forever">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('sales.edit', $sale->id) }}"
                           class="btn btn-sm btn-primary"
                           title="Edit Sale">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Soft delete this sale?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" title="Soft Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        <a href="{{ route('sales.history', $sale->customer_id) }}"
                           class="btn btn-sm btn-info"
                           title="View Customer History">
                            <i class="bi bi-clock-history"></i>
                        </a>
                        <form action="{{ route('customers.clearLoan', $sale->customer_id) }}" method="POST"
                              class="d-inline" onsubmit="return confirm('Clear all loan for this customer?');">
                            @csrf
                            <button class="btn btn-sm btn-warning" title="Clear Loan">
                                <i class="bi bi-cash-stack"></i>
                            </button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center text-muted py-4">
                    @if(request()->hasAny(['search', 'customer_id', 'date_from', 'date_to', 'payment_status']))
                        No sales found matching your criteria.
                    @else
                        No sales recorded yet.
                    @endif
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="d-flex justify-content-between align-items-center">
    <div class="text-muted">
        Showing {{ $sales->firstItem() ?? 0 }} to {{ $sales->lastItem() ?? 0 }} of {{ $sales->total() }} results
    </div>
    <div>
        {{ $sales->appends(request()->query())->links('pagination.bootstrap-5') }}
    </div>
</div>

<script>
function toggleAdvanced() {
    const advanced = document.getElementById('advancedFilters');
    const btn = event.target;

    if (advanced.style.display === 'none') {
        advanced.style.display = 'flex';
        btn.innerHTML = '<i class="bi bi-gear-fill"></i> Hide Advanced';
    } else {
        advanced.style.display = 'none';
        btn.innerHTML = '<i class="bi bi-gear"></i> Advanced';
    }
}

// Auto-submit on some field changes (optional)
document.addEventListener('DOMContentLoaded', function() {
    const autoSubmitFields = ['customer_id', 'payment_status', 'sort_by', 'sort_direction', 'per_page'];

    autoSubmitFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
            field.addEventListener('change', function() {
                document.getElementById('searchForm').submit();
            });
        }
    });

    // Show advanced filters if any advanced field has value
    const advancedFields = ['include_deleted', 'outstanding_min', 'outstanding_max'];
    const hasAdvancedValue = advancedFields.some(field => {
        const element = document.querySelector(`[name="${field}"]`);
        return element && (element.type === 'checkbox' ? element.checked : element.value);
    });

    if (hasAdvancedValue) {
        toggleAdvanced();
    }
});
// Add this to your main JavaScript file or include in the blade template

document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('search');
    let searchTimeout;

    // Auto-submit on dropdown changes
    const autoSubmitFields = ['customer_id', 'payment_status', 'sort_by', 'sort_direction', 'per_page'];

    autoSubmitFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
            field.addEventListener('change', function() {
                searchForm.submit();
            });
        }
    });

    // Debounced search input (optional - waits 500ms after user stops typing)
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                // Only submit if user has typed at least 2 characters or cleared the field
                if (searchInput.value.length >= 2 || searchInput.value.length === 0) {
                    searchForm.submit();
                }
            }, 500);
        });
    }

    // Date range validation
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');

    if (dateFrom && dateTo) {
        dateFrom.addEventListener('change', function() {
            if (dateTo.value && dateFrom.value > dateTo.value) {
                dateTo.value = dateFrom.value;
            }
        });

        dateTo.addEventListener('change', function() {
            if (dateFrom.value && dateTo.value < dateFrom.value) {
                dateFrom.value = dateTo.value;
            }
        });
    }

    // Amount range validation
    const amountMin = document.getElementById('amount_min');
    const amountMax = document.getElementById('amount_max');

    if (amountMin && amountMax) {
        amountMin.addEventListener('change', function() {
            if (amountMax.value && parseFloat(amountMin.value) > parseFloat(amountMax.value)) {
                amountMax.value = amountMin.value;
            }
        });

        amountMax.addEventListener('change', function() {
            if (amountMin.value && parseFloat(amountMax.value) < parseFloat(amountMin.value)) {
                amountMin.value = amountMax.value;
            }
        });
    }

    // Show advanced filters if any advanced field has value
    const advancedFields = ['include_deleted', 'outstanding_min', 'outstanding_max'];
    const hasAdvancedValue = advancedFields.some(field => {
        const element = document.querySelector(`[name="${field}"]`);
        return element && (element.type === 'checkbox' ? element.checked : element.value);
    });

    if (hasAdvancedValue) {
        toggleAdvanced();
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

// Utility function to clear all filters
function clearAllFilters() {
    window.location.href = window.location.pathname;
}

// Utility function to bookmark current filter state
function bookmarkFilters() {
    const url = window.location.href;
    if (navigator.share) {
        navigator.share({
            title: 'Sales Filter',
            url: url
        });
    } else {
        navigator.clipboard.writeText(url).then(function() {
            // Show a temporary success message
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
            btn.classList.add('btn-success');

            setTimeout(function() {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
            }, 2000);
        });
    }
}
</script>

@endsection
