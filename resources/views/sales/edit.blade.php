@extends('layouts.admin')

@section('content')
<h2>Edit Sale</h2>
<form action="{{ route('sales.update', $sale) }}" method="POST" class="mt-3">
    @csrf @method('PUT')
    <div class="mb-3">
        <label class="form-label">Customer</label>
        <select name="customer_id" class="form-select" required>
            @foreach($customers as $cust)
            <option value="{{ $cust->id }}" {{ $cust->id == old('customer_id', $sale->customer_id) ? 'selected' : '' }}>{{ $cust->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Item</label>
        <select name="item_id" class="form-select" required>
            @foreach($items as $itm)
            <option value="{{ $itm->id }}" {{ $itm->id == old('item_id', $sale->item_id) ? 'selected' : '' }}>{{ $itm->name }} - ${{ number_format($itm->price,2) }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Quantity</label>
        <input type="number" name="quantity" value="{{ old('quantity', $sale->quantity) }}" min="1" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Amount Paid ($)</label>
        <input type="number" name="paid" value="{{ old('paid', $sale->paid) }}" step="0.01" min="0" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Date</label>
        <input type="date" name="date" value="{{ old('date', $sale->date->format('Y-m-d')) }}" class="form-control" required>
    </div>
    <button class="btn btn-primary">Update</button>
    <a href="{{ route('sales.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
