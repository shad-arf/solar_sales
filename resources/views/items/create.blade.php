@extends('layouts.admin')

@section('content')
<h2>Add Item</h2>
<form action="{{ route('items.store') }}" method="POST" class="mt-3">
    @csrf

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Item Name</label>
            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Code</label>
            <input type="text" name="code" value="{{ old('code') }}" class="form-control" required>
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Stock Quantity</label>
            <input type="number" name="quantity" value="{{ old('quantity') }}" min="0" class="form-control" required>
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">End user ($)</label>
            <input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0" class="form-control">
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Reseller ($)</label>
            <input type="number" name="base_price" value="{{ old('base_price') }}" step="0.01" min="0" class="form-control">
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Installer ($)</label>
            <input type="number" name="operator_price" value="{{ old('operator_price') }}" step="0.01" min="0" class="form-control">
        </div>
    </div>

    <button class="btn btn-primary">Save</button>
    <a href="{{ route('items.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
