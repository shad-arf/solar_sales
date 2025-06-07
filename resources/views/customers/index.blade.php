@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Customers</h2>
    <div>
        <a href="{{ route('customers.create') }}" class="btn btn-success me-2">Add Customer</a>
        <a href="{{ route('customers.trashed') }}" class="btn btn-secondary">Deleted Customers</a>
    </div>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>City</th>
            <th>Country</th>
            <th class="text-end">Loan ($)</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($customers as $customer)
            @php
                $loan = $customer->calculated_loan ?? 0;
            @endphp
            <tr>
                <td>{{ $customer->name }}</td>
                <td>{{ $customer->email ?? '—' }}</td>
                <td>{{ $customer->phone ?? '—' }}</td>
                <td>{{ $customer->city ?? '—' }}</td>
                <td>{{ $customer->country ?? '—' }}</td>
                <td class="text-end {{ $loan > 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format($loan, 2) }}
                </td>
                <td>
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-primary">
                        Edit
                    </a>

                    <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Delete this customer?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>

                    @if($loan > 0)
                        <form action="{{ route('customers.clearLoan', $customer->id) }}" method="POST"
                              class="d-inline" onsubmit="return confirm('Clear loan for {{ $customer->name }}?');">
                            @csrf
                            <button class="btn btn-sm btn-warning">Clear Loan</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center text-muted">No customers found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
