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
     * List all sales with search and filters
     */
    public function index(Request $request)
    {
        // Get all customers for filter dropdown
        $customers = Customer::whereNull('deleted_at')
                           ->orderBy('name')
                           ->get();

        // Build the query
        $query = Sale::with(['orderItems.item', 'customer']);

        // Include deleted sales if requested
        if ($request->filled('include_deleted')) {
            $query->withTrashed();
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('orderItems.item', function($itemQuery) use ($search) {
                      $itemQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Customer filter
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Date range filters
        if ($request->filled('date_from')) {
            $query->where('sale_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('sale_date', '<=', $request->date_to);
        }

        // Amount range filters
        if ($request->filled('amount_min')) {
            $query->where('total', '>=', $request->amount_min);
        }

        if ($request->filled('amount_max')) {
            $query->where('total', '<=', $request->amount_max);
        }

        // Payment status filter
        if ($request->filled('payment_status')) {
            switch ($request->payment_status) {
                case 'paid':
                    $query->whereRaw('total <= paid_amount');
                    break;
                case 'unpaid':
                    $query->where('paid_amount', 0);
                    break;
                case 'partial':
                    $query->whereRaw('paid_amount > 0 AND paid_amount < total');
                    break;
                case 'overpaid':
                    $query->whereRaw('paid_amount > total');
                    break;
            }
        }

        // Outstanding amount filters
        if ($request->filled('outstanding_min')) {
            $query->whereRaw('(total - paid_amount) >= ?', [$request->outstanding_min]);
        }

        if ($request->filled('outstanding_max')) {
            $query->whereRaw('(total - paid_amount) <= ?', [$request->outstanding_max]);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'sale_date');
        $sortDirection = $request->get('sort_direction', 'desc');

        switch ($sortBy) {
            case 'customer_name':
                $query->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
                      ->orderBy('customers.name', $sortDirection)
                      ->select('sales.*'); // Ensure we only select sales columns
                break;
            case 'total':
                $query->orderBy('total', $sortDirection);
                break;
            case 'code':
                $query->orderBy('code', $sortDirection);
                break;
            case 'sale_date':
            default:
                $query->orderBy('sale_date', $sortDirection);
                break;
        }

        // Secondary sort by ID for consistency
        $query->orderBy('id', 'desc');

        // Pagination
        $perPage = $request->get('per_page', 15);
        $sales = $query->paginate($perPage);

        return view('sales.index', compact('sales', 'customers'));
    }

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
     * Show payment history by customer
     */
    public function history(Customer $customer)
    {
        $sales = $customer->sales()->with(['orderItems.item', 'payments'])->get();
        return view('sales.history', compact('customer', 'sales'));
    }

    /**
     * Export filtered sales to CSV
     */
    public function export(Request $request)
    {
        // Use the same query logic as index but without pagination
        $query = Sale::with(['orderItems.item', 'customer']);

        // Apply all the same filters...
        if ($request->filled('include_deleted')) {
            $query->withTrashed();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('orderItems.item', function($itemQuery) use ($search) {
                      $itemQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('date_from')) {
            $query->where('sale_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('sale_date', '<=', $request->date_to);
        }

        if ($request->filled('amount_min')) {
            $query->where('total', '>=', $request->amount_min);
        }

        if ($request->filled('amount_max')) {
            $query->where('total', '<=', $request->amount_max);
        }

        if ($request->filled('payment_status')) {
            switch ($request->payment_status) {
                case 'paid':
                    $query->whereRaw('total <= paid_amount');
                    break;
                case 'unpaid':
                    $query->where('paid_amount', 0);
                    break;
                case 'partial':
                    $query->whereRaw('paid_amount > 0 AND paid_amount < total');
                    break;
                case 'overpaid':
                    $query->whereRaw('paid_amount > total');
                    break;
            }
        }

        // Get all results for export
        $sales = $query->orderBy('sale_date', 'desc')->get();

        $filename = 'sales_export_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($sales) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'Sale Date',
                'Invoice Code',
                'Customer Name',
                'Items',
                'Total Amount',
                'Paid Amount',
                'Outstanding Amount',
                'Payment Status',
                'Status'
            ]);

            foreach ($sales as $sale) {
                $totalAmount = (float) $sale->total;
                $paidAmount = (float) $sale->paid_amount;
                $outstandingAmt = $totalAmount - $paidAmount;
                $itemsDisplay = $sale->orderItems->map(fn($oi) => "{$oi->item->name} (×{$oi->quantity})")->implode(', ');

                // Determine payment status
                if ($paidAmount == 0) {
                    $paymentStatus = 'Unpaid';
                } elseif ($paidAmount >= $totalAmount) {
                    $paymentStatus = $paidAmount > $totalAmount ? 'Overpaid' : 'Paid';
                } else {
                    $paymentStatus = 'Partial';
                }

                fputcsv($file, [
                    $sale->sale_date,
                    $sale->code,
                    $sale->customer->name ?? 'Deleted Customer',
                    $itemsDisplay,
                    number_format($totalAmount, 2),
                    number_format($paidAmount, 2),
                    number_format($outstandingAmt, 2),
                    $paymentStatus,
                    $sale->trashed() ? 'Deleted' : 'Active'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show "Record Sale" form
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
        'customer_type'     => 'required|in:end_user,installer,reseller',
        'sale_date'         => 'required|date',
        'paid_amount'       => 'required|numeric|min:0',
        'subtotal'          => 'required|numeric|min:0',
        'discount'          => 'nullable|numeric|min:0',
        'code'              => 'required|unique:sales,code',

        'item_id'           => 'required|array|min:1',
        'item_id.*'         => 'required|exists:items,id',

        'price_type'        => 'required|array',
        'price_type.*'      => 'required|in:regular,operator,base',

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
            'customer_type' => $validated['customer_type'],
            'code'        => $validated['code'],
            'sale_date'   => $validated['sale_date'],
            'total'       => $validated['subtotal'],
            'discount'    => $validated['discount'] ?? 0,
            'paid_amount' => $validated['paid_amount'],
        ]);

        // 2) Create order items & decrement stock
        foreach ($validated['item_id'] as $i => $itemId) {
            $quantity   = $validated['quantity'][$i];
            $discount   = $validated['line_discount'][$i] ?? 0;
            $lineTotal  = $validated['line_total'][$i];
            $priceType  = $validated['price_type'][$i];

            $item = Item::findOrFail($itemId);
            if ($item->quantity < $quantity) {
                throw new \Exception("Not enough stock for {$item->name}. Available: {$item->quantity}");
            }

            // Determine unit price based on price type
            $unitPrice = match($priceType) {
                'operator' => $item->operator_price ?: $item->price,
                'base' => $item->base_price ?: $item->price,
                default => $item->price
            };

            Order::create([
                'sale_id'       => $sale->id,
                'item_id'       => $itemId,
                'quantity'      => $quantity,
                'unit_price'    => $unitPrice,
                'price_type'    => $priceType,
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
     * Show "Edit Sale" form, including item list and payment history
     */
    public function edit(Sale $sale)
    {
        $items     = Item::whereNull('deleted_at')->get();
        $customers = Customer::whereNull('deleted_at')->get();
        $payments  = Payment::where('sale_id', $sale->id)->latest()->get();

        return view('sales.edit', compact('sale', 'items', 'customers', 'payments'));
    }

    /**
     * Update sale with comprehensive validation and stock management
     */
    public function update(Request $request, Sale $sale)
    {
        // 1) Validate all incoming data, including line_totals
        $validated = $request->validate([
            'customer_id'     => 'required|exists:customers,id',
            'customer_type'   => 'required|in:end_user,installer,reseller',
            'code'            => "required|unique:sales,code,{$sale->id}",
            'sale_date'       => 'required|date',
            'discount'        => 'nullable|numeric|min:0',

            'item_id'         => 'required|array|min:1',
            'item_id.*'       => 'required|exists:items,id',

            'price_type'      => 'required|array',
            'price_type.*'    => 'required|in:regular,operator,base',

            'quantity'        => 'required|array',
            'quantity.*'      => 'required|integer|min:1',

            'line_discount'   => 'nullable|array',
            'line_discount.*' => 'nullable|numeric|min:0|max:100',

            'line_total'      => 'required|array',
            'line_total.*'    => 'required|numeric|min:0',

            'paid'            => 'nullable|numeric',
        ]);

        DB::beginTransaction();

        try {
            // 2) Compute and apply stock adjustments
            $adjustments = [];

            // a) restore stock from existing items
            foreach ($sale->orderItems as $old) {
                $adjustments[$old->item_id] = ($adjustments[$old->item_id] ?? 0) + $old->quantity;
            }

            // b) subtract new quantities
            foreach ($validated['item_id'] as $i => $itemId) {
                $adjustments[$itemId] = ($adjustments[$itemId] ?? 0) - $validated['quantity'][$i];
            }

            // c) availability check
            foreach ($adjustments as $itemId => $delta) {
                if ($delta < 0) {
                    $item = Item::findOrFail($itemId);
                    if ($item->quantity < abs($delta)) {
                        throw new \Exception("Insufficient stock for {$item->name} ({$item->quantity} available)");
                    }
                }
            }

            // d) apply adjustments
            foreach ($adjustments as $itemId => $delta) {
                if ($delta !== 0) {
                    Item::find($itemId)->increment('quantity', $delta);
                }
            }

            // 3) Update sale core fields
            $sale->update([
                'customer_id' => $validated['customer_id'],
                'customer_type' => $validated['customer_type'],
                'code'        => $validated['code'],
                'sale_date'   => $validated['sale_date'],
                'discount'    => $validated['discount'] ?? 0,
            ]);

            // 4) Remove old order items and rebuild
            $sale->orderItems()->delete();
            $subtotal = 0;

            foreach ($validated['item_id'] as $i => $itemId) {
                $qty  = $validated['quantity'][$i];
                $disc = $validated['line_discount'][$i] ?? 0;
                $type = $validated['price_type'][$i];
                $item = Item::findOrFail($itemId);

                // choose correct unit price
                $unit = match ($type) {
                    'operator' => $item->operator_price,
                    'base'     => $item->base_price,
                    default    => $item->price,
                };

                // trust client-side calculation for line total
                $lineTotal = round($validated['line_total'][$i], 2);
                $subtotal += $lineTotal;

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

            // 5) Update totals and handle payment
            // Apply discount to get final total
            $discount = $validated['discount'] ?? 0;
            $finalTotal = max(0, $subtotal - $discount);
            $sale->update(['total' => $finalTotal]);

            $newPay = floatval($validated['paid'] ?? 0);
            if ($newPay > 0) {
                Payment::create([
                    'sale_id' => $sale->id,
                    'amount'  => $newPay,
                    'paid_at' => now(),
                    'note'    => 'Edit: additional payment',
                ]);
                $sale->increment('paid_amount', $newPay);
            }

            DB::commit();

            return redirect()->route('sales.index')
                             ->with('success', 'Sale updated successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                   ->withErrors(['error' => 'Update failed: ' . $e->getMessage()])
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
