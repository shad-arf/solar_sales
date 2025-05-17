@extends('layouts.admin')

@section('content')
<h2>Edit Item</h2>
<form action="{{ route('items.update', $item) }}" method="POST" class="mt-3">
    @csrf @method('PUT')
    <div class="mb-3">
        <label class="form-label">Item Name</label>
        <input type="text" name="name" value="{{ old('name', $item->name) }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Code</label>
        <input type="text" name="code" value="{{ old('code', $item->code) }}" class="form-control" required>
    </div>
    <div class="mb-3">
    <label class="form-label">Stock Quantity</label>
    <input type="number" name="quantity" value="{{ old('quantity', $item->quantity) }}" min="0" class="form-control" required>
</div>

    <div class="mb-3">
        <label class="form-label">Price ($)</label>
        <input type="number" name="price" value="{{ old('price', $item->price) }}" step="0.01" min="0" class="form-control" required>
    </div>
    <button class="btn btn-primary">Update</button>
    <a href="{{ route('items.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
