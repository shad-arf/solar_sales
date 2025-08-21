@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Create New Sale</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('item-sales.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Sales
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('item-sales.store') }}">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="customer_name" class="form-label">Customer Name</label>
                        <input type="text" class="form-control @error('customer_name') is-invalid @enderror" 
                               id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required>
                        @error('customer_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="sale_date" class="form-label">Sale Date</label>
                        <input type="date" class="form-control @error('sale_date') is-invalid @enderror" 
                               id="sale_date" name="sale_date" value="{{ old('sale_date', date('Y-m-d')) }}" required>
                        @error('sale_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="item_id" class="form-label">Item</label>
                <select class="form-select @error('item_id') is-invalid @enderror" id="item_id" name="item_id" required>
                    <option value="">Select an item</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->name }} - ${{ number_format($item->price, 2) }} (Qty: {{ $item->quantity }})
                        </option>
                    @endforeach
                </select>
                @error('item_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="quantity_sold" class="form-label">Quantity Sold</label>
                        <input type="number" class="form-control @error('quantity_sold') is-invalid @enderror" 
                               id="quantity_sold" name="quantity_sold" value="{{ old('quantity_sold', 1) }}" min="1" required>
                        @error('quantity_sold')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="unit_price" class="form-label">Unit Price</label>
                        <input type="number" class="form-control @error('unit_price') is-invalid @enderror" 
                               id="unit_price" name="unit_price" value="{{ old('unit_price') }}" step="0.01" min="0.01" required>
                        @error('unit_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="total_amount" class="form-label">Total Amount</label>
                        <input type="number" class="form-control" id="total_amount" name="total_amount" readonly>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">Notes (Optional)</label>
                <textarea class="form-control @error('notes') is-invalid @enderror" 
                          id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Sale
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-calculate total amount
document.getElementById('quantity_sold').addEventListener('input', calculateTotal);
document.getElementById('unit_price').addEventListener('input', calculateTotal);

function calculateTotal() {
    const quantity = parseFloat(document.getElementById('quantity_sold').value) || 0;
    const unitPrice = parseFloat(document.getElementById('unit_price').value) || 0;
    const total = quantity * unitPrice;
    document.getElementById('total_amount').value = total.toFixed(2);
}
</script>
@endsection