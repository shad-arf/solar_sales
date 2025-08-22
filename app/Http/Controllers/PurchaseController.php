<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\Expense;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Display a listing of purchases with search and filters
     */
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'creator', 'purchaseItems']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('purchase_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhere('notes', 'LIKE', "%{$search}%");
            });
        }

        // Supplier filter
        if ($request->filled('supplier_id')) {
            $query->bySupplier($request->supplier_id);
        }

        // Date range filter
        if ($request->filled('date_from') || $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'purchase_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $purchases = $query->paginate($perPage);

        // Get suppliers for filter dropdown
        $suppliers = Supplier::active()->orderBy('name')->get();

        // Calculate statistics
        $stats = [
            'total_purchases' => Purchase::count(),
            'pending_purchases' => Purchase::where('status', 'pending')->count(),
            'total_amount' => Purchase::sum('total_amount'),
            'this_month_amount' => Purchase::whereMonth('purchase_date', now()->month)
                                         ->whereYear('purchase_date', now()->year)
                                         ->sum('total_amount'),
        ];

        return view('purchases.index', compact('purchases', 'suppliers', 'stats'));
    }

    /**
     * Show the form for creating a new purchase
     */
    public function create()
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $purchaseNumber = Purchase::generatePurchaseNumber();

        return view('purchases.create', compact('suppliers', 'items', 'purchaseNumber'));
    }

    /**
     * Store a newly created purchase
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity_purchased' => 'required|integer|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            // Create the purchase
            $purchase = Purchase::create([
                'purchase_number' => Purchase::generatePurchaseNumber(),
                'supplier_id' => $validated['supplier_id'],
                'purchase_date' => $validated['purchase_date'],
                'notes' => $validated['notes'],
                'status' => 'pending',
                'created_by' => auth()->id(),
                'total_amount' => 0, // Will be calculated automatically
            ]);

            // Create purchase items
            foreach ($validated['items'] as $itemData) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'item_id' => $itemData['item_id'],
                    'quantity_purchased' => $itemData['quantity_purchased'],
                    'purchase_price' => $itemData['purchase_price'],
                ]);

                // Update item stock quantity
                $item = Item::find($itemData['item_id']);
                $item->increment('quantity', $itemData['quantity_purchased']);
            }

            DB::commit();

            return redirect()->route('purchases.show', $purchase)
                           ->with('success', "Purchase order {$purchase->purchase_number} created successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create purchase: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified purchase
     */
    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'creator', 'purchaseItems.item']);
        
        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified purchase
     */
    public function edit(Purchase $purchase)
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $purchase->load(['purchaseItems.item']);

        return view('purchases.edit', compact('purchase', 'suppliers', 'items'));
    }

    /**
     * Update the specified purchase
     */
    public function update(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity_purchased' => 'required|integer|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            // Revert stock changes from old purchase items
            foreach ($purchase->purchaseItems as $oldItem) {
                $item = $oldItem->item;
                $item->decrement('quantity', $oldItem->quantity_purchased);
            }

            // Delete old purchase items
            $purchase->purchaseItems()->delete();

            // Update purchase details
            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'purchase_date' => $validated['purchase_date'],
                'notes' => $validated['notes'],
            ]);

            // Create new purchase items
            foreach ($validated['items'] as $itemData) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'item_id' => $itemData['item_id'],
                    'quantity_purchased' => $itemData['quantity_purchased'],
                    'purchase_price' => $itemData['purchase_price'],
                ]);

                // Update item stock quantity with new amounts
                $item = Item::find($itemData['item_id']);
                $item->increment('quantity', $itemData['quantity_purchased']);
            }

            DB::commit();

            return redirect()->route('purchases.show', $purchase)
                           ->with('success', "Purchase order {$purchase->purchase_number} updated successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update purchase: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified purchase
     */
    public function destroy(Purchase $purchase)
    {
        if ($purchase->status === 'completed') {
            return redirect()->route('purchases.index')
                           ->with('error', 'Completed purchases cannot be deleted.');
        }

        DB::beginTransaction();
        
        try {
            // Revert stock changes if purchase was pending
            if ($purchase->status === 'pending') {
                foreach ($purchase->purchaseItems as $purchaseItem) {
                    $item = $purchaseItem->item;
                    $item->decrement('quantity', $purchaseItem->quantity_purchased);
                }
            }

            $purchaseNumber = $purchase->purchase_number;
            $purchase->delete();

            DB::commit();

            return redirect()->route('purchases.index')
                           ->with('success', "Purchase order {$purchaseNumber} deleted successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete purchase: ' . $e->getMessage()]);
        }
    }

    /**
     * Mark purchase as completed
     */
    public function complete(Purchase $purchase)
    {
        if ($purchase->status !== 'pending') {
            return redirect()->route('purchases.show', $purchase)
                           ->with('error', 'Only pending purchases can be completed.');
        }

        DB::beginTransaction();
        
        try {
            // Update purchase status
            $purchase->update(['status' => 'completed']);
            
            // Create accounting entries
            $this->createAccountingEntries($purchase);
            
            DB::commit();

            return redirect()->route('purchases.show', $purchase)
                           ->with('success', "Purchase order {$purchase->purchase_number} marked as completed and accounting entries created.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to complete purchase: ' . $e->getMessage()]);
        }
    }

    /**
     * Create accounting entries for completed purchase
     */
    private function createAccountingEntries(Purchase $purchase)
    {
        // Get Cash and Inventory accounts
        $cashAccount = Account::where('code', '1000')->first();
        $inventoryAccount = Account::where('code', '1200')->first();
        
        if (!$cashAccount || !$inventoryAccount) {
            throw new \Exception('Required accounts (Cash or Inventory) not found. Please run database seeders.');
        }

        $totalAmount = $purchase->total_amount;
        $referenceNumber = "PUR-{$purchase->purchase_number}";
        $description = "Purchase from {$purchase->supplier->name} - {$purchase->purchase_number}";

        // Credit Cash (decrease cash)
        Transaction::create([
            'account_id' => $cashAccount->id,
            'description' => $description,
            'debit_amount' => 0,
            'credit_amount' => $totalAmount,
            'transaction_date' => $purchase->purchase_date,
            'reference_number' => $referenceNumber,
            'transaction_type' => 'purchase'
        ]);

        // Debit Inventory (increase inventory)
        Transaction::create([
            'account_id' => $inventoryAccount->id,
            'description' => $description,
            'debit_amount' => $totalAmount,
            'credit_amount' => 0,
            'transaction_date' => $purchase->purchase_date,
            'reference_number' => $referenceNumber,
            'transaction_type' => 'purchase'
        ]);

        // Initialize inventory account balance if this is the first transaction
        $this->initializeInventoryAccountBalance();
    }

    /**
     * Initialize inventory account balance to match actual item values
     */
    private function initializeInventoryAccountBalance()
    {
        $inventoryAccount = Account::where('code', '1200')->first();
        if (!$inventoryAccount) return;

        // Calculate current inventory value from items
        $actualInventoryValue = Item::sum(DB::raw('quantity * COALESCE(price, 0)')) ?: 0;
        
        // Get current account balance
        $accountBalance = $inventoryAccount->balance;
        
        // If there's a significant discrepancy, create an adjustment entry
        $difference = $actualInventoryValue - $accountBalance;
        
        if (abs($difference) > 0.01) { // Only adjust if difference is more than 1 cent
            $referenceNumber = "INIT-INV-" . date('Ymd');
            $description = "Inventory account initialization - align with actual item values";
            
            if ($difference > 0) {
                // Need to increase inventory account balance
                Transaction::create([
                    'account_id' => $inventoryAccount->id,
                    'description' => $description,
                    'debit_amount' => $difference,
                    'credit_amount' => 0,
                    'transaction_date' => now(),
                    'reference_number' => $referenceNumber,
                    'transaction_type' => 'other'
                ]);
            } else {
                // Need to decrease inventory account balance
                Transaction::create([
                    'account_id' => $inventoryAccount->id,
                    'description' => $description,
                    'debit_amount' => 0,
                    'credit_amount' => abs($difference),
                    'transaction_date' => now(),
                    'reference_number' => $referenceNumber,
                    'transaction_type' => 'other'
                ]);
            }
        }
    }

    /**
     * Show purchase history for a specific supplier
     */
    public function supplierHistory($supplierId)
    {
        $supplier = Supplier::findOrFail($supplierId);
        $purchases = Purchase::where('supplier_id', $supplierId)
                            ->with(['purchaseItems'])
                            ->orderBy('purchase_date', 'desc')
                            ->paginate(15);

        $stats = [
            'total_purchases' => $purchases->total(),
            'total_amount' => Purchase::where('supplier_id', $supplierId)->sum('total_amount'),
            'last_purchase_date' => Purchase::where('supplier_id', $supplierId)
                                          ->orderBy('purchase_date', 'desc')
                                          ->value('purchase_date'),
        ];

        return view('purchases.supplier-history', compact('supplier', 'purchases', 'stats'));
    }
}