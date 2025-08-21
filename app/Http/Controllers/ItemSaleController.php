<?php

namespace App\Http\Controllers;

use App\Models\ItemSale;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemSaleController extends Controller
{
    public function index()
    {
        $sales = ItemSale::with('customer')
            ->orderBy('sale_date', 'desc')
            ->paginate(20);

        $stats = [
            'total_sales' => ItemSale::count(),
            'total_revenue' => ItemSale::getTotalSalesRevenue(),
            'monthly_revenue' => ItemSale::getTotalSalesRevenueThisMonth()
        ];

        return view('item-sales.index', compact('sales', 'stats'));
    }

    public function create()
    {
        $items = Item::where('quantity', '>', 0)->get();
        return view('item-sales.create', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity_sold' => 'required|integer|min:1',
            'cost_per_item' => 'required|numeric|min:0',
            'sale_price_per_item' => 'required|numeric|min:0',
            'sale_date' => 'required|date',
            'customer_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'reference_number' => 'nullable|string|max:50'
        ]);

        // Check if we have enough inventory
        $item = Item::findOrFail($request->item_id);
        if ($item->quantity < $request->quantity_sold) {
            return back()->withErrors(['quantity_sold' => 'Not enough inventory. Available: ' . $item->quantity]);
        }

        DB::transaction(function () use ($request, $item) {
            // Create the sale record
            ItemSale::create($request->all());

            // Update item quantity
            $item->decrement('quantity', $request->quantity_sold);
        });

        return redirect()->route('item-sales.index')
            ->with('success', 'Sale recorded successfully!');
    }

    public function show(ItemSale $itemSale)
    {
        $itemSale->load('item');
        return view('item-sales.show', compact('itemSale'));
    }

    public function edit(ItemSale $itemSale)
    {
        $items = Item::all();
        return view('item-sales.edit', compact('itemSale', 'items'));
    }

    public function update(Request $request, ItemSale $itemSale)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity_sold' => 'required|integer|min:1',
            'cost_per_item' => 'required|numeric|min:0',
            'sale_price_per_item' => 'required|numeric|min:0',
            'sale_date' => 'required|date',
            'customer_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'reference_number' => 'nullable|string|max:50'
        ]);

        $oldQuantity = $itemSale->quantity_sold;
        $newQuantity = $request->quantity_sold;
        $item = Item::findOrFail($request->item_id);

        DB::transaction(function () use ($request, $itemSale, $item, $oldQuantity, $newQuantity) {
            // Restore old quantity to inventory
            $item->increment('quantity', $oldQuantity);

            // Check if we have enough for the new quantity
            if ($item->quantity < $newQuantity) {
                throw new \Exception('Not enough inventory. Available: ' . $item->quantity);
            }

            // Update sale record
            $itemSale->update($request->all());

            // Remove new quantity from inventory
            $item->decrement('quantity', $newQuantity);
        });

        return redirect()->route('item-sales.show', $itemSale)
            ->with('success', 'Sale updated successfully!');
    }

    public function destroy(ItemSale $itemSale)
    {
        DB::transaction(function () use ($itemSale) {
            // Restore quantity to inventory
            $item = $itemSale->item;
            $item->increment('quantity', $itemSale->quantity_sold);

            // Delete sale record
            $itemSale->delete();
        });

        return redirect()->route('item-sales.index')
            ->with('success', 'Sale deleted and inventory restored!');
    }
}