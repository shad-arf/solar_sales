@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Dashboard</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ownerEquityModal">
            <i class="bi bi-plus-circle"></i> Add Owner Transaction
        </button>
    </div>

    <!-- Business Worth Summary -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h3 class="card-title mb-2">
                                <i class="bi bi-building me-2"></i>Total Business Worth
                            </h3>
                            <h1 class="display-4 mb-0">${{ number_format($businessSummary['business_worth'], 2) }}</h1>
                            <p class="mb-0 opacity-75">Your complete solar business valuation</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="mb-1">${{ number_format($actualProfitLoss['actual_profit'], 2) }}</h4>
                            <small>Real Sales Profit</small>
                            <hr class="my-2 opacity-50">
                            <div class="small">
                                <div>Revenue: ${{ number_format($actualProfitLoss['total_sales_revenue'], 2) }}</div>
                                <div>Cost: ${{ number_format($actualProfitLoss['total_item_costs'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="mb-1">{{ number_format($actualProfitLoss['profit_margin'], 1) }}%</h4>
                            <small>Profit Margin</small>
                            <hr class="my-2 opacity-50">
                            <div class="small">
                                <div>This Month: ${{ number_format($actualProfitLoss['monthly_actual_profit'], 2) }}</div>
                                <div>Inventory: ${{ number_format($businessSummary['inventory_value'], 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="bi bi-cash-stack fs-2"></i>
                    <h4>${{ number_format($financialSummary['total_revenue'], 2) }}</h4>
                    <small>Income This Month</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <i class="bi bi-receipt fs-2"></i>
                    <h4>${{ number_format($financialSummary['total_expenses'], 2) }}</h4>
                    <small>Expenses This Month</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="bi bi-box fs-2"></i>
                    <h4>{{ $inventoryStats['total_items'] }}</h4>
                    <small>Total Items</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up fs-2"></i>
                    <h4>{{ $salesStats['sales_count'] }}</h4>
                    <small>Total Sales</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <i class="bi bi-cart fs-2"></i>
                    <h4>{{ $purchaseStats['purchase_count'] }}</h4>
                    <small>Total Purchases</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-dark text-white">
                <div class="card-body text-center">
                    <i class="bi bi-person-circle fs-2"></i>
                    <h4>${{ number_format($ownerEquity['net_equity'], 2) }}</h4>
                    <small>Owner Equity</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Comprehensive Charts Section -->
    <div class="row mb-4">
        <!-- Revenue vs Expenses Trend -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-graph-up me-2"></i>Revenue vs Expenses Trend (12 Months)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueExpenseChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <!-- Owner's Equity Breakdown -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-circle me-2"></i>Owner's Equity Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="ownerEquityChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Charts Row 1 -->
    <div class="row mb-4">
        <!-- Business Worth Composition -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pie-chart me-2"></i>Business Worth Composition
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="businessWorthChart" height="150"></canvas>
                </div>
            </div>
        </div>
        <!-- Inventory Status -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-boxes me-2"></i>Inventory Status Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="inventoryStatusChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Charts Row 2 -->
    <div class="row mb-4">
        <!-- Monthly Profit Analysis -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-bar-chart me-2"></i>Monthly Profit Analysis
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="profitAnalysisChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <!-- Sales vs Purchase Comparison -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-arrow-left-right me-2"></i>Sales vs Purchases
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="salesPurchaseChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Charts Row 3 -->
    <div class="row mb-4">
        <!-- Income Categories Breakdown -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-cash-coin me-2"></i>Income Sources Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="incomeSourcesChart" height="150"></canvas>
                </div>
            </div>
        </div>
        <!-- Expense Categories Breakdown -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-receipt-cutoff me-2"></i>Expense Categories Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="expenseCategoriesChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Charts Row 4 -->
    <div class="row mb-4">
        <!-- Growth Trends -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-trending-up me-2"></i>Business Growth Trends
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="growthTrendsChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <!-- Purchase Order Status -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clipboard-check me-2"></i>Purchase Orders Status
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="purchaseStatusChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Charts Row 5 -->
    <div class="row mb-4">
        <!-- Cash Flow Analysis -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-water me-2"></i>Cash Flow Analysis (Income vs Expenses vs Net Profit)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="cashFlowChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Statements Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Balance Sheet</h5>
                </div>
                <div class="card-body">
                    <!-- Assets -->
                    <h6 class="fw-bold text-primary">Assets</h6>
                    @foreach($balanceSheet['assets'] as $asset)
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $asset['name'] }}</span>
                            <span>${{ number_format($asset['balance'], 2) }}</span>
                        </div>
                    @endforeach
                    <div class="d-flex justify-content-between fw-bold border-top pt-2 mb-3">
                        <span>Total Assets</span>
                        <span>${{ number_format($balanceSheet['totals']['assets'], 2) }}</span>
                    </div>

                    <!-- Liabilities -->
                    <h6 class="fw-bold text-danger">Liabilities</h6>
                    @foreach($balanceSheet['liabilities'] as $liability)
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $liability['name'] }}</span>
                            <span>${{ number_format($liability['balance'], 2) }}</span>
                        </div>
                    @endforeach
                    <div class="d-flex justify-content-between fw-bold border-top pt-2 mb-3">
                        <span>Total Liabilities</span>
                        <span>${{ number_format($balanceSheet['totals']['liabilities'], 2) }}</span>
                    </div>

                    <!-- Equity -->
                    <h6 class="fw-bold text-success">Equity</h6>
                    @foreach($balanceSheet['equity'] as $equity)
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $equity['name'] }}</span>
                            <span>${{ number_format($equity['balance'], 2) }}</span>
                        </div>
                    @endforeach
                    <div class="d-flex justify-content-between mb-1">
                        <span>Owner's Equity</span>
                        <span>${{ number_format($ownerEquity['net_equity'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold border-top pt-2">
                        <span>Total Equity</span>
                        <span>${{ number_format($balanceSheet['totals']['equity'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Income Statement</h5>
                </div>
                <div class="card-body">
                    <!-- Revenue -->
                    <h6 class="fw-bold text-success">Revenue</h6>
                    @foreach($incomeStatement['revenues'] as $revenue)
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $revenue['name'] }}</span>
                            <span>${{ number_format($revenue['balance'], 2) }}</span>
                        </div>
                    @endforeach
                    <div class="d-flex justify-content-between fw-bold border-top pt-2 mb-3">
                        <span>Total Revenue</span>
                        <span>${{ number_format($incomeStatement['totals']['revenue'], 2) }}</span>
                    </div>

                    <!-- Expenses -->
                    <h6 class="fw-bold text-danger">Expenses</h6>
                    @foreach($incomeStatement['expenses'] as $expense)
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $expense['name'] }}</span>
                            <span>${{ number_format($expense['balance'], 2) }}</span>
                        </div>
                    @endforeach
                    <div class="d-flex justify-content-between fw-bold border-top pt-2 mb-3">
                        <span>Total Expenses</span>
                        <span>${{ number_format($incomeStatement['totals']['expenses'], 2) }}</span>
                    </div>

                    <!-- Net Income -->
                    <div class="d-flex justify-content-between fw-bold border-top pt-2 fs-5 {{ $incomeStatement['totals']['net_income'] >= 0 ? 'text-success' : 'text-danger' }}">
                        <span>Net Income</span>
                        <span>${{ number_format($incomeStatement['totals']['net_income'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Business Sections -->
    <div class="row mb-4">
        <!-- Inventory Details -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-boxes me-2"></i>Inventory Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Total Items:</span>
                        <strong>{{ $inventoryStats['total_items'] }}</strong>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Total Quantity:</span>
                        <strong>{{ number_format($inventoryStats['total_quantity']) }}</strong>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>In Stock:</span>
                        <strong class="text-success">{{ $inventoryStats['items_in_stock'] }}</strong>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Low Stock:</span>
                        <strong class="text-warning">{{ $inventoryStats['low_stock_items'] }}</strong>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Out of Stock:</span>
                        <strong class="text-danger">{{ $inventoryStats['out_of_stock_items'] }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span><strong>Total Value:</strong></span>
                        <strong class="text-success">${{ number_format($inventoryStats['total_value'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Avg Item Value:</span>
                        <strong>${{ number_format($inventoryStats['average_item_value'], 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Details -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-graph-up me-2"></i>Sales Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Total Sales:</span>
                        <strong>${{ number_format($salesStats['total_sales'], 2) }}</strong>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>This Month:</span>
                        <strong>${{ number_format($salesStats['monthly_sales'], 2) }}</strong>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Sales Count:</span>
                        <strong>{{ $salesStats['sales_count'] }}</strong>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Average Sale:</span>
                        <strong>${{ number_format($salesStats['average_sale'], 2) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Growth:</span>
                        <strong class="{{ $salesStats['sales_growth'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($salesStats['sales_growth'], 1) }}%
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase Details -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-cart me-2"></i>Purchase Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Total Purchases:</span>
                        <strong>${{ number_format($purchaseStats['total_purchases'], 2) }}</strong>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>This Month:</span>
                        <strong>${{ number_format($purchaseStats['monthly_purchases'], 2) }}</strong>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Purchase Count:</span>
                        <strong>{{ $purchaseStats['purchase_count'] }}</strong>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Average Purchase:</span>
                        <strong>${{ number_format($purchaseStats['average_purchase'], 2) }}</strong>
                    </div>
                    <hr>
                    <div class="mb-1 d-flex justify-content-between">
                        <span>Pending:</span>
                        <strong class="text-warning">{{ $purchaseStats['pending_purchases'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Completed:</span>
                        <strong class="text-success">{{ $purchaseStats['completed_purchases'] }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Owner Equity Details -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-circle me-2"></i>Owner's Equity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Investments:</span>
                            <strong class="text-success">${{ number_format($ownerEquity['total_investments'], 2) }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Drawings:</span>
                            <strong class="text-danger">${{ number_format($ownerEquity['total_drawings'], 2) }}</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><strong>Net Equity:</strong></span>
                            <strong class="{{ $ownerEquity['net_equity'] >= 0 ? 'text-success' : 'text-danger' }}">
                                ${{ number_format($ownerEquity['net_equity'], 2) }}
                            </strong>
                        </div>
                    </div>
                    
                    <!-- Recent Transactions -->
                    <div class="border-top pt-2">
                        <small class="text-muted">Recent Transactions:</small>
                        @foreach($ownerEquity['recent_transactions']->take(3) as $transaction)
                            <div class="d-flex justify-content-between mt-1">
                                <small>{{ $transaction->transaction_date->format('M d') }}</small>
                                <small class="{{ $transaction->type == 'investment' ? 'text-success' : 'text-danger' }}">
                                    ${{ number_format($transaction->amount, 0) }}
                                </small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Adjustments & Discrepancies -->
    @if($inventoryAdjustments['total_adjustments'] > 0)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>Inventory Discrepancies Alert
                        <small class="float-end">{{ $inventoryAdjustments['total_adjustments'] }} adjustments recorded</small>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h4 class="text-danger">${{ number_format($inventoryAdjustments['total_financial_impact'], 2) }}</h4>
                            <small class="text-muted">Total Financial Impact</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-warning">${{ number_format($inventoryAdjustments['monthly_financial_impact'], 2) }}</h4>
                            <small class="text-muted">This Month Impact</small>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                @foreach($inventoryAdjustments['adjustments_by_reason']->take(3) as $adjustment)
                                <div class="col-4 text-center">
                                    <strong>{{ $adjustment->count }}</strong><br>
                                    <small class="text-muted">{{ ucfirst($adjustment->reason) }}</small>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-2">
                                <a href="{{ route('inventory-adjustments.index') }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-clipboard-data"></i> View All Adjustments
                                </a>
                            </div>
                        </div>
                    </div>

                    @if($inventoryAdjustments['recent_adjustments']->count() > 0)
                    <hr>
                    <h6 class="text-muted">Recent Adjustments:</h6>
                    <div class="row">
                        @foreach($inventoryAdjustments['recent_adjustments'] as $adjustment)
                        <div class="col-md-4 mb-2">
                            <div class="small border-start border-warning ps-2">
                                <strong>{{ $adjustment->item->name ?? 'Item' }}</strong><br>
                                <span class="text-{{ $adjustment->adjustment_type == 'increase' ? 'success' : 'danger' }}">
                                    {{ $adjustment->adjustment_type == 'increase' ? '+' : '' }}{{ $adjustment->adjustment_quantity }}
                                </span>
                                ({{ ucfirst($adjustment->reason) }})
                                <br><small class="text-muted">{{ $adjustment->adjustment_date->format('M d, Y') }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Activity and Business Worth Breakdown -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Financial Activity</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>Expense</th>
                                    <th>Income</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentActivity as $activity)
                                    <tr>
                                        <td>{{ $activity->date->format('M d, Y') }}</td>
                                        <td>{{ $activity->description }}</td>
                                        <td>{{ $activity->category }}</td>
                                        <td class="text-danger">
                                            @if($activity->type === 'expense')
                                                ${{ number_format($activity->amount, 2) }}
                                            @endif
                                        </td>
                                        <td class="text-success">
                                            @if($activity->type === 'income')
                                                ${{ number_format($activity->amount, 2) }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Business Worth Breakdown</h5>
                </div>
                <div class="card-body">
                    <h6 class="text-success">Assets:</h6>
                    @foreach($businessWorth['assets'] as $name => $value)
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ ucwords(str_replace('_', ' ', $name)) }}:</span>
                            <strong class="text-success">${{ number_format($value, 2) }}</strong>
                        </div>
                    @endforeach
                    
                    <div class="d-flex justify-content-between border-top pt-2 mb-3">
                        <span><strong>Total Assets:</strong></span>
                        <strong class="text-success">${{ number_format($businessWorth['total_assets'], 2) }}</strong>
                    </div>

                    <h6 class="text-danger">Liabilities:</h6>
                    @foreach($businessWorth['liabilities'] as $name => $value)
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ ucwords(str_replace('_', ' ', $name)) }}:</span>
                            <strong class="text-danger">${{ number_format($value, 2) }}</strong>
                        </div>
                    @endforeach
                    
                    <div class="d-flex justify-content-between border-top pt-2 mb-3">
                        <span><strong>Total Liabilities:</strong></span>
                        <strong class="text-danger">${{ number_format($businessWorth['total_liabilities'], 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between border-top pt-2 fs-5">
                        <span><strong>Net Worth:</strong></span>
                        <strong class="{{ $businessWorth['net_worth'] >= 0 ? 'text-success' : 'text-danger' }}">
                            ${{ number_format($businessWorth['net_worth'], 2) }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Owner Equity Modal -->
<div class="modal fade" id="ownerEquityModal" tabindex="-1" aria-labelledby="ownerEquityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ownerEquityModalLabel">Owner Equity Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('dashboard.owner-equity') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="type" class="form-label">Transaction Type</label>
                        <select class="form-control" id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="investment">Owner Investment</option>
                            <option value="drawing">Owner Drawing</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="transaction_date" class="form-label">Transaction Date</label>
                        <input type="date" class="form-control" id="transaction_date" name="transaction_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="reference_number" class="form-label">Reference Number (Optional)</label>
                        <input type="text" class="form-control" id="reference_number" name="reference_number">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Common chart options
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ': $' + context.parsed.toLocaleString();
                    }
                }
            }
        }
    };

    // 1. Revenue vs Expenses Trend Chart
    new Chart(document.getElementById('revenueExpenseChart'), {
        type: 'line',
        data: {
            labels: @json($monthlyData['months']),
            datasets: [{
                label: 'Income',
                data: @json($monthlyData['revenues']),
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Expenses',
                data: @json($monthlyData['expenses']),
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            ...chartOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // 2. Owner's Equity Breakdown
    new Chart(document.getElementById('ownerEquityChart'), {
        type: 'doughnut',
        data: {
            labels: ['Investments', 'Drawings'],
            datasets: [{
                data: [{{ $ownerEquity['total_investments'] }}, {{ $ownerEquity['total_drawings'] }}],
                backgroundColor: ['#28a745', '#dc3545'],
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            ...chartOptions,
            cutout: '60%'
        }
    });

    // 3. Business Worth Composition
    new Chart(document.getElementById('businessWorthChart'), {
        type: 'pie',
        data: {
            labels: ['Inventory', 'Cash from Operations', 'Owner Investment'],
            datasets: [{
                data: [
                    {{ $businessWorth['assets']['inventory'] }},
                    {{ $businessWorth['assets']['cash_from_operations'] }},
                    {{ $businessWorth['assets']['owner_investment'] }}
                ],
                backgroundColor: ['#17a2b8', '#28a745', '#6f42c1'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: chartOptions
    });

    // 4. Inventory Status Distribution
    new Chart(document.getElementById('inventoryStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['In Stock', 'Low Stock', 'Out of Stock'],
            datasets: [{
                data: [
                    {{ $inventoryStats['items_in_stock'] }},
                    {{ $inventoryStats['low_stock_items'] }},
                    {{ $inventoryStats['out_of_stock_items'] }}
                ],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            ...chartOptions,
            plugins: {
                ...chartOptions.plugins,
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + ' items';
                        }
                    }
                }
            }
        }
    });

    // 5. Monthly Profit Analysis
    const profitData = @json($monthlyData['revenues']).map((revenue, index) => 
        revenue - @json($monthlyData['expenses'])[index]
    );
    
    new Chart(document.getElementById('profitAnalysisChart'), {
        type: 'bar',
        data: {
            labels: @json($monthlyData['months']),
            datasets: [{
                label: 'Monthly Profit',
                data: profitData,
                backgroundColor: profitData.map(profit => profit >= 0 ? '#28a745' : '#dc3545'),
                borderColor: '#fff',
                borderWidth: 1
            }]
        },
        options: {
            ...chartOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // 6. Sales vs Purchases Comparison
    new Chart(document.getElementById('salesPurchaseChart'), {
        type: 'doughnut',
        data: {
            labels: ['Sales', 'Purchases'],
            datasets: [{
                data: [{{ $salesStats['total_sales'] }}, {{ $purchaseStats['total_purchases'] }}],
                backgroundColor: ['#28a745', '#ffc107'],
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            ...chartOptions,
            cutout: '50%'
        }
    });

    // 7. Income Sources Breakdown (real data)
    const incomeSourcesChart = document.getElementById('incomeSourcesChart');
    const incomeData = @json($categoryBreakdown['income']);
    
    if (incomeData.labels.length > 0) {
        new Chart(incomeSourcesChart, {
            type: 'pie',
            data: {
                labels: incomeData.labels,
                datasets: [{
                    data: incomeData.data,
                    backgroundColor: ['#007bff', '#28a745', '#17a2b8', '#6f42c1', '#6c757d', '#fd7e14'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: chartOptions
        });
    } else {
        incomeSourcesChart.parentElement.innerHTML = '<p class="text-center text-muted p-4">No income data available</p>';
    }

    // 8. Expense Categories Breakdown (real data)
    const expenseCategoriesChart = document.getElementById('expenseCategoriesChart');
    const expenseData = @json($categoryBreakdown['expense']);
    
    if (expenseData.labels.length > 0) {
        new Chart(expenseCategoriesChart, {
            type: 'pie',
            data: {
                labels: expenseData.labels,
                datasets: [{
                    data: expenseData.data,
                    backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#6f42c1', '#6c757d'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: chartOptions
        });
    } else {
        expenseCategoriesChart.parentElement.innerHTML = '<p class="text-center text-muted p-4">No expense data available</p>';
    }

    // 9. Business Growth Trends
    const growthData = @json($monthlyData['revenues']).map((revenue, index) => {
        if (index === 0) return 0;
        const prevRevenue = @json($monthlyData['revenues'])[index - 1];
        return prevRevenue > 0 ? ((revenue - prevRevenue) / prevRevenue * 100) : 0;
    });

    new Chart(document.getElementById('growthTrendsChart'), {
        type: 'line',
        data: {
            labels: @json($monthlyData['months']),
            datasets: [{
                label: 'Growth Rate (%)',
                data: growthData,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            ...chartOptions,
            scales: {
                y: {
                    ticks: {
                        callback: function(value) {
                            return value.toFixed(1) + '%';
                        }
                    }
                }
            },
            plugins: {
                ...chartOptions.plugins,
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Growth: ' + context.parsed.y.toFixed(1) + '%';
                        }
                    }
                }
            }
        }
    });

    // 10. Purchase Orders Status
    new Chart(document.getElementById('purchaseStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Pending'],
            datasets: [{
                data: [{{ $purchaseStats['completed_purchases'] }}, {{ $purchaseStats['pending_purchases'] }}],
                backgroundColor: ['#28a745', '#ffc107'],
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            ...chartOptions,
            cutout: '60%',
            plugins: {
                ...chartOptions.plugins,
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + ' orders';
                        }
                    }
                }
            }
        }
    });

    // 11. Cash Flow Analysis
    new Chart(document.getElementById('cashFlowChart'), {
        type: 'line',
        data: {
            labels: @json($monthlyData['months']),
            datasets: [{
                label: 'Income',
                data: @json($monthlyData['revenues']),
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: false,
                tension: 0.4
            }, {
                label: 'Expenses',
                data: @json($monthlyData['expenses']),
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                fill: false,
                tension: 0.4
            }, {
                label: 'Net Profit',
                data: profitData,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: false,
                tension: 0.4,
                borderWidth: 3
            }]
        },
        options: {
            ...chartOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>
@endsection
