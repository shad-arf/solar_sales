<?php

namespace App\Http\Controllers;

use App\Models\InventoryAdjustment;
use App\Models\Item;
use App\Models\Account;
use App\Models\Transaction;
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

            // Create accounting entries for inventory adjustments
            $this->createInventoryAdjustmentAccountingEntries($adjustment);
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
            // Store old adjustment for reversal
            $oldAdjustment = $inventoryAdjustment->replicate();
            $oldAdjustment->adjustment_quantity = -$inventoryAdjustment->adjustment_quantity;
            $oldAdjustment->financial_impact = $inventoryAdjustment->financial_impact;

            // Update adjustment record
            $inventoryAdjustment->update($request->all());

            // Update item quantity to new actual quantity
            $item = Item::findOrFail($request->item_id);
            $item->update(['quantity' => $request->actual_quantity]);

            // Reverse old accounting entries and create new ones
            $this->createInventoryAdjustmentAccountingEntries($oldAdjustment, true);
            $this->createInventoryAdjustmentAccountingEntries($inventoryAdjustment);
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
            $adjustment = InventoryAdjustment::create([
                'item_id' => $request->item_id,
                'system_quantity' => $systemQuantity,
                'actual_quantity' => $request->actual_quantity,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'adjustment_date' => now()->toDateString()
            ]);

            // Update item quantity
            $item->update(['quantity' => $request->actual_quantity]);

            // Create accounting entries for inventory adjustments
            $this->createInventoryAdjustmentAccountingEntries($adjustment);
        });

        return back()->with('success', 'Inventory adjusted successfully!');
    }

    /**
     * Create accounting entries for inventory adjustments
     */
    private function createInventoryAdjustmentAccountingEntries(InventoryAdjustment $adjustment, $isReversal = false)
    {
        // Get required accounts
        $inventoryAccount = Account::where('code', '1200')->first(); // Inventory
        $expenseAccount = Account::where('code', '5000')->first(); // Cost of Goods Sold (for losses)

        if (!$inventoryAccount || !$expenseAccount) {
            throw new \Exception('Required accounts (Inventory or Cost of Goods Sold) not found. Please run database seeders.');
        }

        $financialImpact = $adjustment->financial_impact;
        $adjustmentQuantity = $adjustment->adjustment_quantity;
        $referenceNumber = "IA-{$adjustment->id}-" . strtoupper($adjustment->reason);
        $description = "Inventory adjustment: " . (isset($adjustment->item->name) ? $adjustment->item->name : 'Unknown') . " - " . InventoryAdjustment::REASONS[$adjustment->reason];

        if ($isReversal) {
            $description = "Reversal: " . $description;
            $adjustmentQuantity = -$adjustmentQuantity;
        }

        // Only create entries if there's a financial impact
        if ($financialImpact > 0) {
            if ($adjustmentQuantity < 0) {
                // Inventory Decrease (Loss): Credit Inventory, Debit Expense

                // Credit Inventory (decrease inventory asset)
                Transaction::create([
                    'account_id' => $inventoryAccount->id,
                    'description' => $description,
                    'debit_amount' => 0,
                    'credit_amount' => $financialImpact,
                    'transaction_date' => $adjustment->adjustment_date,
                    'reference_number' => $referenceNumber,
                    'transaction_type' => 'other'
                ]);

                // Debit Cost of Goods Sold (record the loss as expense)
                Transaction::create([
                    'account_id' => $expenseAccount->id,
                    'description' => $description,
                    'debit_amount' => $financialImpact,
                    'credit_amount' => 0,
                    'transaction_date' => $adjustment->adjustment_date,
                    'reference_number' => $referenceNumber,
                    'transaction_type' => 'other'
                ]);

            } else {
                // Inventory Increase (Found items): Debit Inventory, Credit Cost of Goods Sold

                // Debit Inventory (increase inventory asset)
                Transaction::create([
                    'account_id' => $inventoryAccount->id,
                    'description' => $description,
                    'debit_amount' => $financialImpact,
                    'credit_amount' => 0,
                    'transaction_date' => $adjustment->adjustment_date,
                    'reference_number' => $referenceNumber,
                    'transaction_type' => 'other'
                ]);

                // Credit Cost of Goods Sold (reduce expense)
                Transaction::create([
                    'account_id' => $expenseAccount->id,
                    'description' => $description,
                    'debit_amount' => 0,
                    'credit_amount' => $financialImpact,
                    'transaction_date' => $adjustment->adjustment_date,
                    'reference_number' => $referenceNumber,
                    'transaction_type' => 'other'
                ]);
            }
        }
    }
}
