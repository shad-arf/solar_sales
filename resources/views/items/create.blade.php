@extends('layouts.admin')

@section('content')
<h2>Add Item</h2>
<form action="{{ route('items.store') }}" method="POST" class="mt-3">
    @csrf
    <div class="mb-3">
        <label class="form-label">Item Name</label>
        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Code</label>
        <input type="text" name="code" value="{{ old('code') }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Price ($)</label>
        <input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0" class="form-control" required>
    </div>
    <button class="btn btn-primary">Save</button>
    <a href="{{ route('items.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
