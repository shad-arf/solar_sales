<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Download invoice as PDF (unchanged)
     */
    public function downloadPDF(Sale $sale)
    {
        $sale->load(['orderItems.item', 'customer', 'payments']);

        $pdf = Pdf::loadView('sales.invoice-pdf', compact('sale'))
                  ->setPaper('A4', 'portrait');

        return $pdf->download('invoice-' . $sale->code . '.pdf');
    }

    /**
     * List all sales
     */
    public function index()
    {
        $sales = Sale::with([
                'orderItems.item',
                'customer'
            ])
            ->orderByDesc('sale_date')
            ->paginate(15);

        return view('sales.index', compact('sales'));
    }

    /**
     * Show payment history by customer
     */
    public function history(Customer $customer)
    {
        $sales = $customer->sales()->with(['orderItems.item', 'payments'])->get();
        return view('sales.history', compact('customer', 'sales'));
    }

    /**
     * Show “Record Sale” form
     */
    public function create()
    {
        $items       = Item::whereNull('deleted_at')->get();
        $customers   = Customer::whereNull('deleted_at')->get();
        // get the highest item id (i.e. the last one added)
        $saleId  = Sale::whereNull('deleted_at')->max(column: 'id');

        return view('sales.create', compact('items', 'customers', 'saleId'));
    }


    /**
     * Store a new sale, plus initial payment record.
     */
   public function store(Request $request)
{
    $validated = $request->validate([
        'customer_id'       => 'required|exists:customers,id',
        'sale_date'         => 'required|date',
        'paid_amount'       => 'required|numeric|min:0',
        'subtotal'          => 'required|numeric|min:0',
        'code'              => 'required|unique:sales,code',

        'item_id'           => 'required|array|min:1',
        'item_id.*'         => 'required|exists:items,id',

        'quantity'          => 'required|array',
        'quantity.*'        => 'required|integer|min:1',

        'line_discount'     => 'nullable|array',
        'line_discount.*'   => 'nullable|numeric|min:0|max:100',

        'line_total'        => 'required|array',
        'line_total.*'      => 'required|numeric|min:0',
    ]);

    DB::beginTransaction();

    try {
        // 1) Create sale
        $sale = Sale::create([
            'customer_id' => $validated['customer_id'],
            'code'        => $validated['code'],
            'sale_date'   => $validated['sale_date'],
            'total'       => $validated['subtotal'],
            'paid_amount' => $validated['paid_amount'],
        ]);

        // 2) Create order items & decrement stock
        foreach ($validated['item_id'] as $i => $itemId) {
            $quantity   = $validated['quantity'][$i];
            $discount   = $validated['line_discount'][$i] ?? 0;
            $lineTotal  = $validated['line_total'][$i];

            $item = Item::findOrFail($itemId);
            if ($item->quantity < $quantity) {
                throw new \Exception("Not enough stock for {$item->name}. Available: {$item->quantity}");
            }

            Order::create([
                'sale_id'       => $sale->id,
                'item_id'       => $itemId,
                'quantity'      => $quantity,
                'unit_price'    => $item->price,
                'line_discount' => $discount,
                'line_total'    => $lineTotal,
            ]);

            $item->decrement('quantity', $quantity);
        }

        // 3) Record payment
        if ($validated['paid_amount'] > 0) {
            Payment::create([
                'sale_id' => $sale->id,
                'amount'  => $validated['paid_amount'],
                'paid_at' => now(),
            ]);
        }

        DB::commit();
        return redirect()->route('sales.index')
                         ->with('success', 'Sale recorded successfully.');
    } catch (\Throwable $e) {
        DB::rollBack();
        return back()
            ->withErrors(['error' => 'Failed to record sale: '.$e->getMessage()])
            ->withInput();
    }
}
    /**
     * Show “Edit Sale” form, including item list and payment history
     */
    public function edit(Sale $sale)
    {
        $items     = Item::whereNull('deleted_at')->get();
        $customers = Customer::whereNull('deleted_at')->get();
        $payments  = Payment::where('sale_id', $sale->id)->latest()->get();

        return view('sales.edit', compact('sale', 'items', 'customers', 'payments'));
    }

    public function update(Request $request, Sale $sale)
{
    $validated = $request->validate([
        'customer_id'     => 'required|exists:customers,id',
        'code'            => "required|unique:sales,code,{$sale->id}",
        'sale_date'       => 'required|date',
        'item_id'         => 'required|array|min:1',
        'item_id.*'       => 'required|exists:items,id',
        'price_type'      => 'required|array', // Added validation
        'price_type.*'    => 'required|in:regular,operator,base', // Added validation
        'quantity'        => 'required|array',
        'quantity.*'      => 'required|integer|min:1',
        'line_discount'   => 'nullable|array',
        'line_discount.*' => 'nullable|numeric|min:0|max:100',
        'paid'            => 'nullable|numeric',
    ]);

    DB::beginTransaction();

    try {
        // 1) Calculate stock adjustments without changing database
        $stockAdjustments = [];
        $oldItems = $sale->orderItems;

        // Track old quantities
        foreach ($oldItems as $oldOrder) {
            $stockAdjustments[$oldOrder->item_id] = ($stockAdjustments[$oldOrder->item_id] ?? 0) + $oldOrder->quantity;
        }

        // Track new quantities
        foreach ($validated['item_id'] as $index => $itemId) {
            $quantity = $validated['quantity'][$index];
            $stockAdjustments[$itemId] = ($stockAdjustments[$itemId] ?? 0) - $quantity;
        }

        // 2) Validate stock availability before making changes
        foreach ($stockAdjustments as $itemId => $adjustment) {
            if ($adjustment < 0) { // Only check when we need to remove stock
                $item = Item::findOrFail($itemId);
                $requiredStock = abs($adjustment);

                if ($item->quantity < $requiredStock) {
                    throw new \Exception("Not enough stock for {$item->name}. Available: {$item->quantity}, Needed: {$requiredStock}");
                }
            }
        }

        // 3) Perform actual stock adjustments
        foreach ($stockAdjustments as $itemId => $adjustment) {
            if ($adjustment !== 0) {
                $item = Item::find($itemId);
                $item->increment('quantity', $adjustment);
            }
        }

        // 4) Update the Sale's basic fields
        $sale->update([
            'customer_id' => $validated['customer_id'],
            'code'        => $validated['code'],
            'sale_date'   => $validated['sale_date'],
        ]);

        // 5) Delete old orderItems
        $sale->orderItems()->delete();

        // 6) Create new orderItems
        $newSubtotal = 0;

        foreach ($validated['item_id'] as $index => $itemId) {
            $quantity = $validated['quantity'][$index];
            $discount = $validated['line_discount'][$index] ?? 0;
            $priceType = $validated['price_type'][$index];
            $item = Item::findOrFail($itemId);

            // Determine unit price based on price type
            $unitPrice = match($priceType) {
                'operator' => $item->operator_price,
                'base' => $item->base_price,
                default => $item->price,
            };

            $lineTotal = $unitPrice * $quantity * (1 - $discount/100);
            $lineTotal = round($lineTotal, 2);
            $newSubtotal += $lineTotal;

            Order::create([
                'sale_id'       => $sale->id,
                'item_id'       => $itemId,
                'quantity'      => $quantity,
                'unit_price'    => $unitPrice, // Store actual price used
                'price_type'    => $priceType, // Store price type
                'line_discount' => $discount,
                'line_total'    => $lineTotal,
            ]);
        }

        // 7) Update sale total
        $sale->update(['total' => $newSubtotal]);

        // 8) Handle new payments
        $newPayment = floatval($validated['paid'] ?? 0);

        if ($newPayment != 0) {
            Payment::create([
                'sale_id' => $sale->id,
                'amount'  => $newPayment,
                'paid_at' => now(),
                'note'    => 'Additional payment from edit',
            ]);

            $sale->paid_amount += $newPayment;
            $sale->save();
        }

        DB::commit();
        return redirect()->route('sales.index')
                         ->with('success', 'Sale updated successfully.');
    }
    catch (\Throwable $e) {
        DB::rollBack();
        return back()
            ->withErrors(['error' => 'Failed to update sale: ' . $e->getMessage()])
            ->withInput();
    }
}


    /**
     * Show single sale (unchanged)
     */
    public function show(Sale $sale)
    {
        $sale->load(['orderItems.item', 'customer', 'payments']);
        return view('sales.show', compact('sale'));
    }

    /**
     * Soft-delete sale & restore stock if needed
     */
    public function destroy(Sale $sale)
    {
        // Restore stock for each order item
        foreach ($sale->orderItems as $order) {
            Item::find($order->item_id)?->increment('quantity', $order->quantity);
        }

        $sale->delete();
        return redirect()->route('sales.index')->with('success', 'Sale deleted.');
    }

    /**
     * Restore sale (and re‐decrement stock)
     */
    public function restore($id)
    {
        $sale = Sale::withTrashed()->findOrFail($id);

        foreach ($sale->orderItems as $order) {
            Item::find($order->item_id)?->decrement('quantity', $order->quantity);
        }

        $sale->restore();
        return redirect()->route('sales.index')->with('success', 'Sale restored.');
    }

    /**
     * Permanently delete sale (no stock change)
     */
    public function forceDelete($id)
    {
        $sale = Sale::withTrashed()->findOrFail($id);
        $sale->forceDelete();
        return redirect()->route('sales.index')->with('success', 'Sale permanently deleted.');
    }

    /**
     * Mark all unpaid amounts as settled and clear customer loan
     */
    public function clearLoan(Customer $customer)
    {
        // Mark each sale as fully paid if there is an outstanding
        foreach ($customer->sales as $sale) {
            $outstanding = $sale->total - $sale->paid_amount;
            if ($outstanding > 0) {
                // Create a payment for the outstanding difference
                Payment::create([
                    'sale_id' => $sale->id,
                    'amount'  => $outstanding,
                    'paid_at' => now(),
                    'note'    => 'Cleared via Clear Loan button',
                ]);
                $sale->increment('paid_amount', $outstanding);
            }
        }

        // Reset loan field on customer record
        $customer->update(['loan' => 0]);

        return back()->with('success', 'All outstanding amounts marked paid and customer loan cleared.');
    }
}
