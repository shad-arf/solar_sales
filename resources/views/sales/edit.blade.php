
@extends('layouts.admin')
@section('content')
<h2>Edit Sale</h2>
<form action="{{ route('sales.update', $sale) }}" method="POST" class="mt-3">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label class="form-label">Customer</label>
        <select name="customer_id" class="form-select" required>
            @foreach($customers as $cust)
                <option value="{{ $cust->id }}" {{ $cust->id == $sale->customer_id ? 'selected' : '' }}>
                    {{ $cust->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Item</label>
        <select name="item_id" class="form-select" required onchange="updateTotal()">
            @foreach($items as $itm)
                <option value="{{ $itm->id }}" data-price="{{ $itm->price }}" {{ $itm->id == $sale->item_id ? 'selected' : '' }}>
                    {{ $itm->name }} — ${{ number_format($itm->price, 2) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Quantity</label>
        <input type="number" name="quantity" value="{{ $sale->quantity }}" min="1" class="form-control" oninput="updateTotal()" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Amount Paid ($)</label>
        <input type="number" name="paid" value="{{ $sale->paid }}" step="0.01" min="0" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Discount (%)</label>
        <input type="number" name="Discount" value="{{ $sale->Discount }}" step="0.01" min="0" class="form-control" oninput="updateTotal()">
    </div>
    <div class="mb-3">
        <label class="form-label">Total ($)</label>
        <input type="number" name="total" value="{{ $sale->total }}" step="0.01" min="0" class="form-control" required readonly>
    </div>
    <div class="mb-3">
        <label class="form-label">Sale Date</label>
        <input type="date" name="date" value="{{ $sale->date }}" class="form-control" required>
    </div>

    <h4>Payment History</h4>
    <ul class="list-group mb-3">
        @forelse($payments as $pay)
            <li class="list-group-item d-flex justify-content-between">
                <span>${{ number_format($pay->amount, 2) }}</span>
                <small>{{ $pay->paid_at }}</small>
            </li>
        @empty
            <li class="list-group-item text-muted">No payments recorded</li>
        @endforelse
    </ul>

    <button class="btn btn-primary">Update</button>
    <a href="{{ route('sales.index') }}" class="btn btn-secondary">Cancel</a>
    <form action="{{ route('customers.clearLoan', $sale->customer_id) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-warning float-end" onclick="return confirm('Mark all as fully paid and clear loan?')">
            Mark Fully Paid
        </button>
    </form>
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
