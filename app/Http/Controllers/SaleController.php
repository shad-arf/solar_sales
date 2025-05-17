<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Item;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['item', 'customer'])->orderByDesc('date')->get();
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $items = Item::all();
        $customers = Customer::all();
        return view('sales.create', compact('items', 'customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id'     => 'required|exists:items,id',
            'customer_id' => 'required|exists:customers,id',
            'quantity'    => 'required|integer|min:1',
            'paid'        => 'required|numeric|min:0',
            'date'        => 'required|date',
        ]);

        DB::transaction(function () use ($validated) {
            $item = Item::findOrFail($validated['item_id']);
            $total = $item->price * $validated['quantity'];
            $outstanding = $total - $validated['paid'];

            $sale = Sale::create($validated);
            if ($outstanding > 0) {
                $sale->customer->increment('loan', $outstanding);
            }
        });

        return redirect()->route('sales.index')->with('success', 'Sale recorded.');
    }

    public function edit(Sale $sale)
    {
        $items = Item::all();
        $customers = Customer::all();
        return view('sales.edit', compact('sale', 'items', 'customers'));
    }

    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'item_id'     => 'required|exists:items,id',
            'customer_id' => 'required|exists:customers,id',
            'quantity'    => 'required|integer|min:1',
            'paid'        => 'required|numeric|min:0',
            'date'        => 'required|date',
        ]);

        DB::transaction(function () use ($validated, $sale) {
            // original amounts
            $orig_total        = $sale->item->price * $sale->quantity;
            $orig_outstanding  = $orig_total - $sale->paid;

            // update sale
            $sale->update($validated);

            $new_total         = $sale->item->price * $sale->quantity;
            $new_outstanding   = $new_total - $sale->paid;
            $diff              = $new_outstanding - $orig_outstanding;

            if ($diff !== 0) {
                $sale->customer->increment('loan', $diff);
            }
        });

        return redirect()->route('sales.index')->with('success', 'Sale updated.');
    }

    public function destroy(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            $outstanding = ($sale->item->price * $sale->quantity) - $sale->paid;
            if ($outstanding > 0) {
                $sale->customer->decrement('loan', $outstanding);
            }
            $sale->delete();
        });

        return redirect()->route('sales.index')->with('success', 'Sale deleted.');
    }
}
