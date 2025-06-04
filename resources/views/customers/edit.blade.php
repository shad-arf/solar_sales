@extends('layouts.admin')

@section('content')
<h2>Edit Customer</h2>

<form action="{{ route('customers.update', $customer) }}" method="POST" class="mt-3">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" value="{{ old('name', $customer->name) }}"
                   class="form-control @error('name') is-invalid @enderror" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                   class="form-control @error('email') is-invalid @enderror">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}"
                   class="form-control @error('phone') is-invalid @enderror">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">City</label>
            <input type="text" name="city" value="{{ old('city', $customer->city) }}"
                   class="form-control @error('city') is-invalid @enderror">
            @error('city')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">State</label>
            <input type="text" name="state" value="{{ old('state', $customer->state) }}"
                   class="form-control @error('state') is-invalid @enderror">
            @error('state')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Country</label>
            <input type="text" name="country" value="{{ old('country', $customer->country) }}"
                   class="form-control @error('country') is-invalid @enderror">
            @error('country')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" rows="2"
                      class="form-control @error('address') is-invalid @enderror">{{ old('address', $customer->address) }}</textarea>
            @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 mb-3">
            <label class="form-label">Note</label>
            <textarea name="note" rows="3"
                      class="form-control @error('note') is-invalid @enderror">{{ old('note', $customer->note) }}</textarea>
            @error('note')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <button class="btn btn-primary">Update</button>
    <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
