@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Deleted Customers</h2>
    <a href="{{ route('customers.index') }}" class="btn btn-secondary">All Customers</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th class="text-end">Calculated Loan ($)</th>
            <th>Deleted At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($customers as $customer)
            <tr>
                <td>{{ $customer->name }}</td>
                <td>{{ $customer->email ?? '—' }}</td>
                <td>{{ $customer->phone ?? '—' }}</td>
                <td class="text-end">{{ number_format($customer->calculated_loan, 2) }}</td>
                <td>{{ $customer->deleted_at->format('Y-m-d H:i') }}</td>
                <td>
                    <form action="{{ route('customers.restore', $customer->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-warning">Restore</button>
                    </form>
                    <form action="{{ route('customers.forceDelete', $customer->id) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Permanently delete this customer?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete Forever</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center">No deleted customers.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
