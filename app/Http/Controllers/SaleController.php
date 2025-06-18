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
     // eager‑load relations to avoid N+1
        $sale->load(['customer', 'orderItems.item']);

        return view('sales.invoice-pdf', [
            'sale' => $sale,
        ]);
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

            'price_type'      => 'required|array',
            'price_type.*'    => 'required|in:regular,operator,base',

            'quantity'        => 'required|array',
            'quantity.*'      => 'required|integer|min:1',

            'line_discount'   => 'nullable|array',
            'line_discount.*' => 'nullable|numeric|min:0|max:100',

            'paid'            => 'nullable|numeric',
        ]);

        DB::beginTransaction();

        try {
            // 1) Compute net stock changes per item
            $adjustments = [];

            // restore stock from old orderItems
            foreach ($sale->orderItems as $old) {
                $adjustments[$old->item_id] = ($adjustments[$old->item_id] ?? 0) + $old->quantity;
            }

            // subtract new quantities
            foreach ($validated['item_id'] as $i => $itemId) {
                $adjustments[$itemId] = ($adjustments[$itemId] ?? 0) - $validated['quantity'][$i];
            }

            // 2) Check availability before applying
            foreach ($adjustments as $itemId => $delta) {
                if ($delta < 0) {
                    $item = Item::findOrFail($itemId);
                    if ($item->quantity < abs($delta)) {
                        throw new \Exception("Insufficient stock for {$item->name} ({$item->quantity} available)");
                    }
                }
            }

            // 3) Apply stock adjustments
            foreach ($adjustments as $itemId => $delta) {
                if ($delta !== 0) {
                    Item::find($itemId)->increment('quantity', $delta);
                }
            }

            // 4) Update sale core fields
            $sale->update([
                'customer_id' => $validated['customer_id'],
                'code'        => $validated['code'],
                'sale_date'   => $validated['sale_date'],
            ]);

            // 5) Rebuild orderItems, recalc subtotal
            $sale->orderItems()->delete();
            $subtotal = 0;

            foreach ($validated['item_id'] as $i => $itemId) {
                $qty    = $validated['quantity'][$i];
                $disc   = $validated['line_discount'][$i] ?? 0;
                $type   = $validated['price_type'][$i];
                $item   = Item::findOrFail($itemId);

                // choose unit price by type
                $unit = match ($type) {
                    'operator' => $item->operator_price,
                    'base'     => $item->base_price,
                    default    => $item->price,
                };

                $lineTotal = round($unit ) ;
                $subtotal += $lineTotal;

                dd($subtotal);
                Order::create([
                    'sale_id'       => $sale->id,
                    'item_id'       => $itemId,
                    'quantity'      => $qty,
                    'unit_price'    => $unit,
                    'price_type'    => $type,
                    'line_discount' => $disc,
                    'line_total'    => $lineTotal,
                ]);
            }

            // 6) Update total and record payment
            $sale->update(['total' => $subtotal]);

            $newPay = floatval($validated['paid'] ?? 0);
                Payment::create([
                    'sale_id' => $sale->id,
                    'amount'  => $newPay,
                    'paid_at' => now(),
                    'note'    => 'Edit: additional payment',
                ]);

                $sale->increment('paid_amount', $newPay);

            DB::commit();

            return redirect()->route('sales.index')
                             ->with('success', 'Sale updated successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Update failed: ' . $e->getMessage()])
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
