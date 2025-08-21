<?php

namespace App\Http\Controllers;

use App\Models\InventoryAdjustment;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryAdjustmentController extends Controller
{
    public function index()
    {
        $adjustments = InventoryAdjustment::with('item')
            ->orderBy('adjustment_date', 'desc')
            ->paginate(20);

        $stats = [
            'total_adjustments' => InventoryAdjustment::count(),
            'total_financial_impact' => InventoryAdjustment::getTotalFinancialImpact(),
            'monthly_financial_impact' => InventoryAdjustment::getTotalFinancialImpactThisMonth(),
            'adjustments_by_reason' => InventoryAdjustment::getAdjustmentsByReason()
        ];

        return view('inventory-adjustments.index', compact('adjustments', 'stats'));
    }

    public function create()
    {
        $items = Item::all();
        return view('inventory-adjustments.create', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'system_quantity' => 'required|integer|min:0',
            'actual_quantity' => 'required|integer|min:0',
            'reason' => 'required|in:' . implode(',', array_keys(InventoryAdjustment::REASONS)),
            'notes' => 'nullable|string',
            'adjustment_date' => 'required|date'
        ]);

        DB::transaction(function () use ($request) {
            // Create adjustment record
            $adjustment = InventoryAdjustment::create($request->all());

            // Update item quantity to actual quantity
            $item = Item::findOrFail($request->item_id);
            $item->update(['quantity' => $request->actual_quantity]);
        });

        return redirect()->route('inventory-adjustments.index')
            ->with('success', 'Inventory adjustment recorded successfully!');
    }

    public function show(InventoryAdjustment $inventoryAdjustment)
    {
        $inventoryAdjustment->load('item');
        return view('inventory-adjustments.show', compact('inventoryAdjustment'));
    }

    public function edit(InventoryAdjustment $inventoryAdjustment)
    {
        $items = Item::all();
        return view('inventory-adjustments.edit', compact('inventoryAdjustment', 'items'));
    }

    public function update(Request $request, InventoryAdjustment $inventoryAdjustment)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'system_quantity' => 'required|integer|min:0',
            'actual_quantity' => 'required|integer|min:0',
            'reason' => 'required|in:' . implode(',', array_keys(InventoryAdjustment::REASONS)),
            'notes' => 'nullable|string',
            'adjustment_date' => 'required|date'
        ]);

        DB::transaction(function () use ($request, $inventoryAdjustment) {
            // Update adjustment record
            $inventoryAdjustment->update($request->all());

            // Update item quantity to new actual quantity
            $item = Item::findOrFail($request->item_id);
            $item->update(['quantity' => $request->actual_quantity]);
        });

        return redirect()->route('inventory-adjustments.show', $inventoryAdjustment)
            ->with('success', 'Inventory adjustment updated successfully!');
    }

    public function destroy(InventoryAdjustment $inventoryAdjustment)
    {
        $inventoryAdjustment->delete();

        return redirect()->route('inventory-adjustments.index')
            ->with('success', 'Inventory adjustment deleted!');
    }

    public function quickAdjust(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'actual_quantity' => 'required|integer|min:0',
            'reason' => 'required|in:' . implode(',', array_keys(InventoryAdjustment::REASONS)),
            'notes' => 'nullable|string'
        ]);

        $item = Item::findOrFail($request->item_id);
        $systemQuantity = $item->quantity;

        DB::transaction(function () use ($request, $item, $systemQuantity) {
            // Create adjustment record
            InventoryAdjustment::create([
                'item_id' => $request->item_id,
                'system_quantity' => $systemQuantity,
                'actual_quantity' => $request->actual_quantity,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'adjustment_date' => now()->toDateString()
            ]);

            // Update item quantity
            $item->update(['quantity' => $request->actual_quantity]);
        });

        return back()->with('success', 'Inventory adjusted successfully!');
    }
}