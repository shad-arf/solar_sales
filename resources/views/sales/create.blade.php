@extends('layouts.admin')

@section('content')
<h2>Record Sale</h2>
<form action="{{ route('sales.store') }}" method="POST" class="mt-3">
    @csrf
    <div class="mb-3">
        <label class="form-label">Customer</label>
        <select name="customer_id" class="form-select" required>
            <option value="" disabled selected>Select customer...</option>
            @foreach($customers as $cust)
            <option value="{{ $cust->id }}">{{ $cust->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Item</label>
        <select name="item_id" class="form-select" required>
            <option value="" disabled selected>Select item...</option>
            @foreach($items as $itm)
            <option value="{{ $itm->id }}">{{ $itm->name }} - ${{ number_format($itm->price,2) }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Quantity</label>
        <input type="number" name="quantity" value="{{ old('quantity',1) }}" min="1" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Amount Paid ($)</label>
        <input type="number" name="paid" value="{{ old('paid',0) }}" step="0.01" min="0" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Date</label>
        <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" class="form-control" required>
    </div>
    <button class="btn btn-primary">Save</button>
    <a href="{{ route('sales.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
