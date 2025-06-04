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
        $items = Item::whereNull('deleted_at')->get();
        $customers = Customer::whereNull('deleted_at')->get();
        return view('sales.create', compact('items', 'customers'));
    }

    /**
     * Store a new sale, plus initial payment record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'     => 'required|exists:customers,id',
            'sale_date'       => 'required|date',
            'paid_amount'     => 'required|numeric|min:0',
            'subtotal'        => 'required|numeric|min:0',
            'code'            => 'required|unique:sales,code',

            'item_id'         => 'required|array|min:1',
            'item_id.*'       => 'required|exists:items,id',

            'quantity'        => 'required|array',
            'quantity.*'      => 'required|integer|min:1',

            'line_discount'   => 'nullable|array',
            'line_discount.*' => 'nullable|numeric|min:0|max:100',

            'line_total'      => 'required|array',
            'line_total.*'    => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // 1) Create the sale record
            $sale = Sale::create([
                'customer_id'  => $validated['customer_id'],
                'code'         => $validated['code'],
                'sale_date'    => $validated['sale_date'],
                'total'        => $validated['subtotal'],
                'paid_amount'  => $validated['paid_amount'],
            ]);

            // 2) Create each order item & decrement stock
            foreach ($validated['item_id'] as $index => $itemId) {
                $quantity  = $validated['quantity'][$index];
                $discount  = $validated['line_discount'][$index] ?? 0;
                $lineTotal = $validated['line_total'][$index];

                $item = Item::findOrFail($itemId);
                if ($item->quantity < $quantity) {
                    throw new \Exception("Not enough stock for item: {$item->name} (Available: {$item->quantity})");
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

            // 3) Record initial payment history
            if ($validated['paid_amount'] > 0) {
                Payment::create([
                    'sale_id' => $sale->id,
                    'amount'  => $validated['paid_amount'],
                    'paid_at' => now(),
                    'note'    => null,
                ]);
            }

            DB::commit();
            return redirect()->route('sales.index')->with('success', 'Sale recorded successfully.');
        }
        catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Failed to record sale: ' . $e->getMessage()])
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
        'quantity'        => 'required|array',
        'quantity.*'      => 'required|integer|min:1',
        'line_discount'   => 'nullable|array',
        'line_discount.*' => 'nullable|numeric|min:0|max:100',
        'line_total'      => 'required|array',
        'line_total.*'    => 'required|numeric|min:0',
        'paid'            => 'nullable|numeric', // allow negative too
    ]);

    DB::beginTransaction();

    try {
        // 1) Restore stock from old order items
        foreach ($sale->orderItems as $oldOrder) {
            Item::find($oldOrder->item_id)?->increment('quantity', $oldOrder->quantity);
        }

        // 2) Update the Sale’s basic fields (customer, code, date)
        $sale->update([
            'customer_id' => $validated['customer_id'],
            'code'        => $validated['code'],
            'sale_date'   => $validated['sale_date'],
        ]);

        // 3) Delete old orderItems
        $sale->orderItems()->delete();

        // 4) Create new orderItems & decrement stock
        $newSubtotal = 0;
        foreach ($validated['item_id'] as $index => $itemId) {
            $quantity  = $validated['quantity'][$index];
            $discount  = $validated['line_discount'][$index] ?? 0;
            $lineTotal = $validated['line_total'][$index];
            $item      = Item::findOrFail($itemId);

            if ($item->quantity < $quantity) {
                throw new \Exception("Not enough stock for item: {$item->name} (Available: {$item->quantity})");
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
            $newSubtotal += $lineTotal;
        }

        // 5) Update sale 'total' to new subtotal
        $sale->update(['total' => $newSubtotal]);

        // 6) If user entered a new payment (positive or negative), record it
        if (isset($validated['paid']) && floatval($validated['paid']) != 0) {
            Payment::create([
                'sale_id' => $sale->id,
                'amount'  => $validated['paid'],
                'paid_at' => now(),
                'note'    => null,
            ]);

            // increment() accepts a negative value to subtract
            $sale->increment('paid_amount', $validated['paid']);
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
