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
        <select name="item_id" class="form-select" required onchange="updateTotal()">
            <option value="" disabled selected>Select item...</option>
            @foreach($items as $itm)
                <option value="{{ $itm->id }}" data-price="{{ $itm->price }}">
                    {{ $itm->name }} — ${{ number_format($itm->price, 2) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Quantity</label>
        <input type="number" name="quantity" value="1" min="1" class="form-control" oninput="updateTotal()" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Amount Paid ($)</label>
        <input type="number" name="paid" value="0" step="0.01" min="0" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Discount (%)</label>
        <input type="number" name="Discount" value="0" step="0.01" min="0" class="form-control" oninput="updateTotal()">
    </div>
    <div class="mb-3">
        <label class="form-label">Total ($)</label>
        <input type="number" name="total" value="0" step="0.01" min="0" class="form-control" readonly required>
    </div>
    <div class="mb-3">
        <label class="form-label">Sale Date</label>
        <input type="date" name="date" value="{{ now()->toDateString() }}" class="form-control" required>
    </div>
    <button class="btn btn-primary">Save</button>
    <a href="{{ route('sales.index') }}" class="btn btn-secondary">Cancel</a>
</form>

<script>
function updateTotal() {
    const price = parseFloat(document.querySelector('select[name=item_id] option:checked')?.dataset.price || 0);
    const quantity = parseInt(document.querySelector('input[name=quantity]').value || 0);
    const discountPercent = parseFloat(document.querySelector('input[name=Discount]').value || 0);
    const discount = (price * quantity) * discountPercent;
    const total = (price * quantity) - discount;
    document.querySelector('input[name=total]').value = total.toFixed(2);
}
</script>
@endsection
