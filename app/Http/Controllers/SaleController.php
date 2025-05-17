<?php

// CONTROLLER: SaleController.php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;

class SaleController extends Controller
{
   public function index()
{
    $sales = Sale::with(['item' => fn($q) => $q->withTrashed(), 'customer' => fn($q) => $q->withTrashed()])
        ->orderByDesc('date')
        ->get();

    return view('sales.index', compact('sales'));
}


    public function history(Customer $customer)
    {
        $sales = $customer->sales()->with(['item', 'payments'])->get();
        return view('sales.history', compact('customer', 'sales'));
    }

    public function create()
    {
        $items = Item::whereNull('deleted_at')->get();
        $customers = Customer::whereNull('deleted_at')->get();
        return view('sales.create', compact('items', 'customers'));
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'item_id'     => 'required|exists:items,id',
        'customer_id' => 'required|exists:customers,id',
        'quantity'    => 'required|integer|min:1',
        'paid'        => 'required|numeric|min:0',
        'Discount'    => 'nullable|numeric|min:0',
        'total'       => 'required|numeric|min:0',
        'date'        => 'required|date',
    ]);

    // Fetch the item and check stock
    $item = Item::findOrFail($validated['item_id']);

    if ($item->quantity < $validated['quantity']) {
        return back()->withErrors([
            'quantity' => "Only {$item->quantity} unit(s) in stock. You can't sell more than available.",
        ])->withInput();
    }

    // Create sale
    $sale = Sale::create($validated);

    // Reduce item stock
    $item->decrement('quantity', $validated['quantity']);

    // Record initial payment
    Payment::create([
        'sale_id' => $sale->id,
        'amount' => $validated['paid'],
        'paid_at' => now(),
    ]);

    return redirect()->route('sales.index')->with('success', 'Sale recorded.');
}


    public function edit(Sale $sale)
    {
        $items = Item::whereNull('deleted_at')->get();
        $customers = Customer::whereNull('deleted_at')->get();
        $payments = Payment::where('sale_id', $sale->id)->latest()->get();
        return view('sales.edit', compact('sale', 'items', 'customers', 'payments'));
    }

public function update(Request $request, Sale $sale)
{
    $validated = $request->validate([
        'item_id'     => 'required|exists:items,id',
        'customer_id' => 'required|exists:customers,id',
        'quantity'    => 'required|integer|min:1',
        'paid'        => 'required|numeric|min:0',
        'Discount'    => 'nullable|numeric|min:0',
        'total'       => 'required|numeric|min:0',
        'date'        => 'required|date',
    ]);

    // Restore previous stock
    $oldItem = Item::find($sale->item_id);
    if ($oldItem) {
        $oldItem->increment('quantity', $sale->quantity);
    }

    // Check if new quantity is valid
    $newItem = Item::findOrFail($validated['item_id']);

    if ($newItem->quantity < $validated['quantity']) {
        return back()->withErrors([
            'quantity' => "Only {$newItem->quantity} unit(s) available in stock.",
        ])->withInput();
    }

    // Update sale
    $sale->update($validated);

    // Deduct stock from new item
    $newItem->decrement('quantity', $validated['quantity']);

    // Record payment versioning
    Payment::create([
        'sale_id' => $sale->id,
        'amount' => $validated['paid'],
        'paid_at' => now(),
    ]);

    return redirect()->route('sales.index')->with('success', 'Sale updated.');
}


    public function destroy(Sale $sale)
    {
        Item::find($sale->item_id)?->increment('quantity', $sale->quantity);
        $sale->delete();
        return redirect()->route('sales.index')->with('success', 'Sale deleted.');
    }

    public function restore($id)
    {
        $sale = Sale::withTrashed()->findOrFail($id);
        Item::find($sale->item_id)?->decrement('quantity', $sale->quantity);
        $sale->restore();
        return redirect()->route('sales.index')->with('success', 'Sale restored.');
    }

    public function forceDelete($id)
    {
        $sale = Sale::withTrashed()->findOrFail($id);
        $sale->forceDelete();
        return redirect()->route('sales.index')->with('success', 'Sale permanently deleted.');
    }

    public function clearLoan(Customer $customer)
    {
        $customer->update(['loan' => 0]);
        return back()->with('success', 'Customer loan cleared.');
    }
}
