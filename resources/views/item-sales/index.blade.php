@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Item Sales</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('item-sales.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Sale
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Sales</h5>
                <h3 class="text-primary">{{ $stats['total_sales'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Revenue</h5>
                <h3 class="text-success">${{ number_format($stats['total_revenue'], 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Monthly Revenue</h5>
                <h3 class="text-info">${{ number_format($stats['monthly_revenue'], 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td>{{ $sale->sale_date->format('Y-m-d') }}</td>
                        <td>{{ $sale->customer->name ?? 'N/A' }}</td>
                        <td>{{ $sale->items_count ?? 1 }} items</td>
                        <td>${{ number_format($sale->total_amount, 2) }}</td>
                        <td>
                            <a href="{{ route('item-sales.show', $sale) }}" class="btn btn-sm btn-outline-primary">View</a>
                            <a href="{{ route('item-sales.edit', $sale) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No sales recorded yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $sales->links('pagination.bootstrap-5') }}
    </div>
</div>
@endsection