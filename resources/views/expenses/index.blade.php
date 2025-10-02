@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Expense Records</h2>
        <a href="{{ route('expenses.create') }}" class="btn btn-danger">
            <i class="bi bi-plus-circle"></i> Add Expense
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Expense History</h5>
        </div>
        <div class="card-body">
            @if($expenses->isEmpty())
                <div class="text-center py-4">
                    <i class="bi bi-receipt fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No expenses recorded yet. <a href="{{ route('expenses.create') }}">Add your first expense</a></p>
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
                            @foreach($expenses as $expense)
                                <tr>
                                    <td>{{ $expense->date->format('M d, Y') }}</td>
                                    <td>{{ $expense->description }}</td>
                                    <td>
                                        <span class="badge bg-danger">
                                            {{ $expense->category_name }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($expense->reference_number)
                                            <span class="badge bg-secondary">{{ $expense->reference_number }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-danger fw-bold fs-6">
                                            ${{ number_format($expense->amount, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('expenses.show', $expense) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-outline-warning btn-sm">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('expenses.destroy', $expense) }}" 
                                                  onsubmit="return confirm('Are you sure you want to delete this expense record?')" class="d-inline">
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
                @if($expenses->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $expenses->links('pagination.bootstrap-5') }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">This Month</h5>
                    <h3>${{ number_format(\App\Models\Expense::getTotalThisMonth(), 2) }}</h3>
                    <small>Total Expenses</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">This Year</h5>
                    <h3>${{ number_format(\App\Models\Expense::getTotalThisYear(), 2) }}</h3>
                    <small>Total Expenses</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Average</h5>
                    <h3>${{ number_format(\App\Models\Expense::getTotalThisYear() / max(1, date('n')), 2) }}</h3>
                    <small>Monthly Average</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Records</h5>
                    <h3>{{ $expenses->total() }}</h3>
                    <small>Expense Entries</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection