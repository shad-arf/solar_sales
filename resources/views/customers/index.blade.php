@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Customers</h2>
    <div>
        <a href="{{ route('customers.create') }}" class="btn btn-success me-2">
            <i class="bi bi-person-plus"></i> Add Customer
        </a>
        <a href="{{ route('customers.trashed') }}" class="btn btn-secondary">
            <i class="bi bi-trash"></i> Deleted Customers
        </a>
    </div>
</div>

<!-- Search and Filters Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('customers.index') }}" id="searchForm">
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
                               placeholder="Name, email, phone, city..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Loan Status Filter -->
                <div class="col-md-2">
                    <label for="loan_status" class="form-label">Loan Status</label>
                    <select class="form-select" id="loan_status" name="loan_status">
                        <option value="">All Customers</option>
                        <option value="with_loan" {{ request('loan_status') == 'with_loan' ? 'selected' : '' }}>With Loan</option>
                        <option value="no_loan" {{ request('loan_status') == 'no_loan' ? 'selected' : '' }}>No Loan</option>
                        <option value="paid_up" {{ request('loan_status') == 'paid_up' ? 'selected' : '' }}>Paid Up</option>
                    </select>
                </div>

                <!-- Country Filter -->
                <div class="col-md-2">
                    <label for="country" class="form-label">Country</label>
                    <select class="form-select" id="country" name="country">
                        <option value="">All Countries</option>
                        @foreach($countries as $countryOption)
                            <option value="{{ $countryOption }}"
                                    {{ request('country') == $countryOption ? 'selected' : '' }}>
                                {{ $countryOption }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- City Filter -->
                <div class="col-md-2">
                    <label for="city" class="form-label">City</label>
                    <select class="form-select" id="city" name="city">
                        <option value="">All Cities</option>
                        @foreach($cities as $cityOption)
                            <option value="{{ $cityOption }}"
                                    {{ request('city') == $cityOption ? 'selected' : '' }}>
                                {{ $cityOption }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sort Options -->
                <div class="col-md-2">
                    <label for="sort_by" class="form-label">Sort By</label>
                    <select class="form-select" id="sort_by" name="sort_by">
                        <option value="name" {{ request('sort_by', 'name') == 'name' ? 'selected' : '' }}>Name</option>
                        <option value="loan" {{ request('sort_by') == 'loan' ? 'selected' : '' }}>Loan Amount</option>
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date Added</option>
                        <option value="city" {{ request('sort_by') == 'city' ? 'selected' : '' }}>City</option>
                        <option value="country" {{ request('sort_by') == 'country' ? 'selected' : '' }}>Country</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <!-- Loan Amount Range -->
                <div class="col-md-2">
                    <label for="loan_min" class="form-label">Min Loan</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number"
                               class="form-control"
                               id="loan_min"
                               name="loan_min"
                               step="0.01"
                               placeholder="0.00"
                               value="{{ request('loan_min') }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <label for="loan_max" class="form-label">Max Loan</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number"
                               class="form-control"
                               id="loan_max"
                               name="loan_max"
                               step="0.01"
                               placeholder="9999.99"
                               value="{{ request('loan_max') }}">
                    </div>
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
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                    <button type="button" class="btn btn-outline-info" onclick="toggleAdvanced()">
                        <i class="bi bi-gear"></i> Advanced
                    </button>
                    <a href="{{ route('customers.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
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

                <!-- Contact Info Filters -->
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="has_email" name="has_email" value="1"
                               {{ request('has_email') ? 'checked' : '' }}>
                        <label class="form-check-label" for="has_email">
                            Has Email Address
                        </label>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="has_phone" name="has_phone" value="1"
                               {{ request('has_phone') ? 'checked' : '' }}>
                        <label class="form-check-label" for="has_phone">
                            Has Phone Number
                        </label>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="has_address" name="has_address" value="1"
                               {{ request('has_address') ? 'checked' : '' }}>
                        <label class="form-check-label" for="has_address">
                            Has Complete Address
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
            </div>
        </form>
    </div>
</div>

<!-- Results Summary -->
@if(request()->hasAny(['search', 'loan_status', 'country', 'city', 'loan_min', 'loan_max']))
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    Showing {{ $customers->total() }} result(s)
    @if(request('search'))
        for search: "<strong>{{ request('search') }}</strong>"
    @endif
    @if(request('loan_status'))
        | Status: <strong>{{ ucfirst(str_replace('_', ' ', request('loan_status'))) }}</strong>
    @endif
    @if(request('country'))
        | Country: <strong>{{ request('country') }}</strong>
    @endif
    @if(request('city'))
        | City: <strong>{{ request('city') }}</strong>
    @endif
    @if(request('loan_min') || request('loan_max'))
        | Loan:
        @if(request('loan_min')) from <strong>${{ request('loan_min') }}</strong> @endif
        @if(request('loan_max')) to <strong>${{ request('loan_max') }}</strong> @endif
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
                        <h5 class="card-title">Total Customers</h5>
                        <h3>{{ $stats['total_customers'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-people fs-1"></i>
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
                        <h5 class="card-title">With Loans</h5>
                        <h3>{{ $stats['customers_with_loans'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-cash-stack fs-1"></i>
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
                        <h5 class="card-title">Total Outstanding</h5>
                        <h3>${{ number_format($stats['total_outstanding'], 2) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
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
                        <h5 class="card-title">Avg Loan</h5>
                        <h3>${{ number_format($stats['average_loan'], 2) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-graph-up fs-1"></i>
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
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}"
                           class="text-decoration-none text-dark">
                            Name
                            @if(request('sort_by', 'name') == 'name')
                                <i class="bi bi-chevron-{{ request('sort_direction', 'asc') == 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th>Contact Info</th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'city', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}"
                           class="text-decoration-none text-dark">
                            Location
                            @if(request('sort_by') == 'city')
                                <i class="bi bi-chevron-{{ request('sort_direction', 'asc') == 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-end">
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'loan', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}"
                           class="text-decoration-none text-dark">
                            Loan ($)
                            @if(request('sort_by') == 'loan')
                                <i class="bi bi-chevron-{{ request('sort_direction', 'asc') == 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}"
                           class="text-decoration-none text-dark">
                            Added
                            @if(request('sort_by') == 'created_at')
                                <i class="bi bi-chevron-{{ request('sort_direction', 'asc') == 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    @php
                        $loan = $customer->calculated_loan ?? 0;
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-primary text-white rounded-circle me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                                <div>
                                    <strong>{{ $customer->name }}</strong>
                                    @if($customer->note)
                                        <br><small class="text-muted">{{ Str::limit($customer->note, 30) }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                @if($customer->email)
                                    <div><i class="bi bi-envelope text-muted me-1"></i> {{ $customer->email }}</div>
                                @endif
                                @if($customer->phone)
                                    <div><i class="bi bi-telephone text-muted me-1"></i> {{ $customer->phone }}</div>
                                @endif
                                @if(!$customer->email && !$customer->phone)
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                @if($customer->city)
                                    <div><i class="bi bi-geo-alt text-muted me-1"></i> {{ $customer->city }}</div>
                                @endif
                                @if($customer->country)
                                    <div><i class="bi bi-flag text-muted me-1"></i> {{ $customer->country }}</div>
                                @endif
                                @if(!$customer->city && !$customer->country)
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-end">
                            <span class="badge {{ $loan > 0 ? 'bg-danger' : 'bg-success' }} fs-6">
                                ${{ number_format($loan, 2) }}
                            </span>
                        </td>
                        <td>
                            <small class="text-muted">{{ $customer->created_at->format('M d, Y') }}</small>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('customers.edit', $customer) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="Edit Customer">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <a href="{{ route('sales.history', $customer->id) }}"
                                   class="btn btn-sm btn-outline-info"
                                   title="View Sales History">
                                    <i class="bi bi-clock-history"></i>
                                </a>

                                @if($loan > 0)
                                    <form action="{{ route('customers.clearLoan', $customer->id) }}" method="POST"
                                          class="d-inline" onsubmit="return confirm('Clear loan for {{ $customer->name }}?');">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-warning" title="Clear Loan">
                                            <i class="bi bi-cash-stack"></i>
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete customer {{ $customer->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete Customer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            @if(request()->hasAny(['search', 'loan_status', 'country', 'city']))
                                No customers found matching your criteria.
                                <br><a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-primary mt-2">Clear Filters</a>
                            @else
                                No customers found.
                                <br><a href="{{ route('customers.create') }}" class="btn btn-sm btn-success mt-2">Add First Customer</a>
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
        Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }} results
    </div>
    <div>
        {{ $customers->appends(request()->query())->links() }}
    </div>
</div>

<script>
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

// Auto-submit on field changes
document.addEventListener('DOMContentLoaded', function() {
    const autoSubmitFields = ['loan_status', 'country', 'city', 'sort_by', 'sort_direction', 'per_page'];

    autoSubmitFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
            field.addEventListener('change', function() {
                document.getElementById('searchForm').submit();
            });
        }
    });

    // Show advanced filters if any advanced field has value
    const advancedFields = ['has_email', 'has_phone', 'has_address', 'created_from', 'created_to'];
    const hasAdvancedValue = advancedFields.some(field => {
        const element = document.querySelector(`[name="${field}"]`);
        return element && (element.type === 'checkbox' ? element.checked : element.value);
    });

    if (hasAdvancedValue) {
        toggleAdvanced();
    }

    // Debounced search
    const searchInput = document.getElementById('search');
    let searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                if (searchInput.value.length >= 2 || searchInput.value.length === 0) {
                    document.getElementById('searchForm').submit();
                }
            }, 500);
        });
    }
});
// Enhanced Customer Search JavaScript
// Add this to your main JavaScript file or include in the blade template

document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('search');
    let searchTimeout;

    // Auto-submit on dropdown changes
    const autoSubmitFields = ['loan_status', 'country', 'city', 'sort_by', 'sort_direction', 'per_page'];

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

    // Loan amount range validation
    const loanMin = document.getElementById('loan_min');
    const loanMax = document.getElementById('loan_max');

    if (loanMin && loanMax) {
        loanMin.addEventListener('change', function() {
            if (loanMax.value && parseFloat(loanMin.value) > parseFloat(loanMax.value)) {
                loanMax.value = loanMin.value;
            }
        });

        loanMax.addEventListener('change', function() {
            if (loanMin.value && parseFloat(loanMax.value) < parseFloat(loanMin.value)) {
                loanMin.value = loanMax.value;
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
    const advancedFields = ['has_email', 'has_phone', 'has_address', 'created_from', 'created_to'];
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

    // Initialize tooltips for action buttons
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
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

// Quick filter functions
function filterByLoanStatus(status) {
    document.getElementById('loan_status').value = status;
    document.getElementById('searchForm').submit();
}

function filterByCountry(country) {
    document.getElementById('country').value = country;
    document.getElementById('searchForm').submit();
}

// Utility functions
function clearAllFilters() {
    window.location.href = window.location.pathname;
}

function exportCurrentView() {
    const exportUrl = document.querySelector('a[href*="export"]').href;
    window.open(exportUrl, '_blank');
}

// Bulk actions (for future enhancement)
function selectAllCustomers() {
    const checkboxes = document.querySelectorAll('input[name="selected_customers[]"]');
    const selectAllCheckbox = document.getElementById('select_all');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });

    updateBulkActions();
}

function updateBulkActions() {
    const selectedCheckboxes = document.querySelectorAll('input[name="selected_customers[]"]:checked');
    const bulkActions = document.getElementById('bulk_actions');

    if (bulkActions) {
        bulkActions.style.display = selectedCheckboxes.length > 0 ? 'block' : 'none';
    }
}

// Search suggestions (for future enhancement)
function showSearchSuggestions(query) {
    if (query.length < 2) return;

    // This could fetch suggestions via AJAX
    fetch(`/customers/search-suggestions?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            // Display suggestions dropdown
            displaySearchSuggestions(data);
        })
        .catch(error => {
            console.log('Search suggestions not available');
        });
}

function displaySearchSuggestions(suggestions) {
    // Implementation for showing search suggestions
    const suggestionsContainer = document.getElementById('search_suggestions');
    if (suggestionsContainer) {
        suggestionsContainer.innerHTML = suggestions.map(suggestion =>
            `<div class="suggestion-item" onclick="selectSuggestion('${suggestion}')">${suggestion}</div>`
        ).join('');
    }
}

function selectSuggestion(value) {
    document.getElementById('search').value = value;
    document.getElementById('searchForm').submit();
}

// Performance monitoring
function trackSearchPerformance() {
    const startTime = performance.now();

    // Track when search completes
    window.addEventListener('load', function() {
        const endTime = performance.now();
        const searchTime = endTime - startTime;

        // Could send analytics data
        console.log(`Search completed in ${searchTime.toFixed(2)}ms`);
    });
}
</script>

@endsection
