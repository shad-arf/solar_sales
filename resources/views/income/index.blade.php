@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Income Records</h2>
        <a href="{{ route('income.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Add Income
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Income History</h5>
        </div>
        <div class="card-body">
            @if($incomes->isEmpty())
                <div class="text-center py-4">
                    <i class="bi bi-cash-stack fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No income recorded yet. <a href="{{ route('income.create') }}">Add your first income</a></p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Reference</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($incomes as $income)
                                <tr>
                                    <td>{{ $income->date->format('M d, Y') }}</td>
                                    <td>{{ $income->description }}</td>
                                    <td>
                                        <span class="badge bg-success">
                                            {{ $income->category_name }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($income->reference_number)
                                            <span class="badge bg-secondary">{{ $income->reference_number }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-success fw-bold fs-6">
                                            ${{ number_format($income->amount, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('income.show', $income) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('income.edit', $income) }}" class="btn btn-outline-warning btn-sm">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('income.destroy', $income) }}" 
                                                  onsubmit="return confirm('Are you sure you want to delete this income record?')" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($incomes->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $incomes->links('pagination.bootstrap-5') }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">This Month</h5>
                    <h3>${{ number_format(\App\Models\Income::getTotalThisMonth(), 2) }}</h3>
                    <small>Total Income</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">This Year</h5>
                    <h3>${{ number_format(\App\Models\Income::getTotalThisYear(), 2) }}</h3>
                    <small>Total Income</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Average</h5>
                    <h3>${{ number_format(\App\Models\Income::getTotalThisYear() / max(1, date('n')), 2) }}</h3>
                    <small>Monthly Average</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Records</h5>
                    <h3>{{ $incomes->total() }}</h3>
                    <small>Income Entries</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection