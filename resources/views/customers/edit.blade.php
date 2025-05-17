@extends('layouts.admin')

@section('content')
<h2>Edit Customer</h2>
<form action="{{ route('customers.update', $customer) }}" method="POST" class="mt-3">
    @csrf @method('PUT')
    <div class="mb-3">
        <label class="form-label">Customer Name</label>
        <input type="text" name="name" value="{{ old('name', $customer->name) }}" class="form-control" required>
    </div>
    <button class="btn btn-primary">Update</button>
    <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
