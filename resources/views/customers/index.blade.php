@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Customers</h2>
    <a href="{{ route('customers.create') }}" class="btn btn-success">Add Customer</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th class="text-end">Loan ($)</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($customers as $cust)
        <tr>
            <td>{{ $cust->name }}</td>
            <td class="text-end {{ $cust->loan > 0 ? 'text-danger' : '' }}">{{ number_format($cust->loan, 2) }}</td>
            <td>
                <a href="{{ route('customers.edit', $cust) }}" class="btn btn-sm btn-primary">Edit</a>
                <form action="{{ route('customers.destroy', $cust) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete customer?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="3" class="text-center">No customers.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
