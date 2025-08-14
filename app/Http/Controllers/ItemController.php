<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    /**
     * Display a listing of items with search and filters
     */
    public function index(Request $request)
    {
        // Build the query
        $query = Item::query();

        // Include deleted items if requested
        if ($request->filled('include_deleted')) {
            $query->withTrashed();
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Stock status filter
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->where('quantity', '>', 0);
                    break;
                case 'low_stock':
                    $query->where('quantity', '>', 0)->where('quantity', '<', 10);
                    break;
                case 'out_of_stock':
                    $query->where('quantity', 0);
                    break;
                case 'overstocked':
                    $query->where('quantity', '>', 100);
                    break;
            }
        }

        // Price range filters
        if ($request->filled('price_min')) {
            $priceType = $request->get('price_type', 'regular');
            $priceColumn = match($priceType) {
                'base' => 'base_price',
                'operator' => 'operator_price',
                default => 'price'
            };
            $query->where($priceColumn, '>=', $request->price_min);
        }

        if ($request->filled('price_max')) {
            $priceType = $request->get('price_type', 'regular');
            $priceColumn = match($priceType) {
                'base' => 'base_price',
                'operator' => 'operator_price',
                default => 'price'
            };
            $query->where($priceColumn, '<=', $request->price_max);
        }

        // Quantity range filters
        if ($request->filled('quantity_min')) {
            $query->where('quantity', '>=', $request->quantity_min);
        }

        if ($request->filled('quantity_max')) {
            $query->where('quantity', '<=', $request->quantity_max);
        }

        // Date range filters
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }

        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');

        $query->orderBy($sortBy, $sortDirection);

        // Secondary sort by ID for consistency
        $query->orderBy('id', 'asc');

        // Pagination
        $perPage = $request->get('per_page', 15);
        $items = $query->paginate($perPage);

        // Calculate statistics
        $stats = [
            'total_items' => Item::count(),
            'low_stock' => Item::where('quantity', '>', 0)->where('quantity', '<', 10)->count(),
            'out_of_stock' => Item::where('quantity', 0)->count(),
            'total_value' => Item::selectRaw('SUM(price * quantity) as total')->value('total') ?? 0
        ];

        return view('items.index', compact('items', 'stats'));
    }

    /**
     * Export filtered items to CSV
     */
    public function export(Request $request)
    {
        // Use the same query logic as index but without pagination
        $query = Item::query();

        // Apply all the same filters...
        if ($request->filled('include_deleted')) {
            $query->withTrashed();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->where('quantity', '>', 0);
                    break;
                case 'low_stock':
                    $query->where('quantity', '>', 0)->where('quantity', '<', 10);
                    break;
                case 'out_of_stock':
                    $query->where('quantity', 0);
                    break;
                case 'overstocked':
                    $query->where('quantity', '>', 100);
                    break;
            }
        }

        if ($request->filled('price_min')) {
            $priceType = $request->get('price_type', 'regular');
            $priceColumn = match($priceType) {
                'base' => 'base_price',
                'operator' => 'operator_price',
                default => 'price'
            };
            $query->where($priceColumn, '>=', $request->price_min);
        }

        if ($request->filled('price_max')) {
            $priceType = $request->get('price_type', 'regular');
            $priceColumn = match($priceType) {
                'base' => 'base_price',
                'operator' => 'operator_price',
                default => 'price'
            };
            $query->where($priceColumn, '<=', $request->price_max);
        }

        if ($request->filled('quantity_min')) {
            $query->where('quantity', '>=', $request->quantity_min);
        }

        if ($request->filled('quantity_max')) {
            $query->where('quantity', '<=', $request->quantity_max);
        }

        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }

        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        // Get all results for export
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $items = $query->orderBy($sortBy, $sortDirection)->get();

        $filename = 'items_export_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'Code',
                'Name',
                'Description',
                'Regular Price',
                'Base Price',
                'Operator Price',
                'Quantity',
                'Stock Status',
                'Total Value',
                'Date Added',
                'Status'
            ]);

            foreach ($items as $item) {
                // Determine stock status
                if ($item->quantity == 0) {
                    $stockStatus = 'Out of Stock';
                } elseif ($item->quantity < 10) {
                    $stockStatus = 'Low Stock';
                } elseif ($item->quantity > 100) {
                    $stockStatus = 'Overstocked';
                } else {
                    $stockStatus = 'In Stock';
                }

                fputcsv($file, [
                    $item->code,
                    $item->name,
                    $item->description ?? '',
                    number_format($item->price, 2),
                    number_format($item->base_price ?? 0, 2),
                    number_format($item->operator_price ?? 0, 2),
                    $item->quantity,
                    $stockStatus,
                    number_format($item->price * $item->quantity, 2),
                    $item->created_at->format('Y-m-d'),
                    $item->trashed() ? 'Deleted' : 'Active'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Quick stock update
     */
    public function updateStock(Request $request, Item $item)
    {
        $validated = $request->validate([
            'action' => 'required|in:add,remove,set',
            'quantity' => 'required|integer|min:0',
            'note' => 'nullable|string|max:255'
        ]);

        $oldQuantity = $item->quantity;

        switch ($validated['action']) {
            case 'add':
                $newQuantity = $oldQuantity + $validated['quantity'];
                break;
            case 'remove':
                $newQuantity = max(0, $oldQuantity - $validated['quantity']);
                break;
            case 'set':
                $newQuantity = $validated['quantity'];
                break;
        }

        $item->update(['quantity' => $newQuantity]);

        // Log the stock change (optional - you could create a stock_movements table)
        // StockMovement::create([
        //     'item_id' => $item->id,
        //     'old_quantity' => $oldQuantity,
        //     'new_quantity' => $newQuantity,
        //     'action' => $validated['action'],
        //     'quantity_changed' => $validated['quantity'],
        //     'note' => $validated['note'],
        //     'user_id' => auth()->id()
        // ]);

        return back()->with('success', "Stock updated for {$item->name}. Changed from {$oldQuantity} to {$newQuantity}.");
    }

    public function lowStock()
    {
        $items = Item::where('quantity', '<', 10)->where('quantity', '>', 0)->get();
        return view('items.low_stock', compact('items'));
    }

    public function outOfStock()
    {
        $items = Item::where('quantity', 0)->get();
        return view('items.out_of_stock', compact('items'));
    }

    public function trashed()
    {
        $items = Item::onlyTrashed()->get();
        return view('items.trashed', compact('items'));
    }

    public function create()
    {
        return view('items.create');
    }

    public function show(Item $item)
    {
        // Load order items with their sales and customers
        $item->load(['orderItems.sale.customer']);

        // Calculate sales statistics
        $salesStats = [
            'total_sold' => $item->orderItems->sum('quantity'),
            'total_revenue' => $item->orderItems->sum('line_total'),
            'avg_sale_price' => $item->orderItems->avg('unit_price'),
            'last_sale_date' => $item->orderItems->max('created_at')
        ];

        return view('items.show', compact('item', 'salesStats'));
    }

    public function edit(Item $item)
    {
        return view('items.edit', compact('item'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'code'            => 'required|string|max:255|unique:items,code',
            'description'     => 'nullable|string',
            'price'           => 'nullable|numeric|min:0',
            'base_price'      => 'nullable|numeric|min:0',
            'operator_price'  => 'nullable|numeric|min:0',
            'quantity'        => 'required|integer|min:0',
        ]);

        Item::create($validated);
        return redirect()->route('items.index')->with('success', 'Item created successfully.');
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'code'            => 'required|string|max:255|unique:items,code,' . $item->id,
            'description'     => 'nullable|string',
            'price'           => 'nullable|numeric|min:0',
            'base_price'      => 'nullable|numeric|min:0',
            'operator_price'  => 'nullable|numeric|min:0',
            'quantity'        => 'required|integer|min:0',
        ]);

        $item->update($validated);
        return redirect()->route('items.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
    }

    public function restore($id)
    {
        $item = Item::withTrashed()->findOrFail($id);
        $item->restore();
        return redirect()->route('items.trashed')->with('success', 'Item restored successfully.');
    }

    public function forceDelete($id)
    {
        $item = Item::withTrashed()->findOrFail($id);
        $item->forceDelete();
        return redirect()->route('items.trashed')->with('success', 'Item permanently deleted.');
    }

    /**
     * Bulk stock update
     */
    public function bulkStockUpdate(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:0'
        ]);

        DB::beginTransaction();

        try {
            foreach ($validated['items'] as $itemData) {
                Item::where('id', $itemData['id'])
                    ->update(['quantity' => $itemData['quantity']]);
            }

            DB::commit();
            return back()->with('success', 'Bulk stock update completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Bulk update failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Items dashboard/analytics
     */
    public function dashboard()
    {
        $stats = [
            'total_items' => Item::count(),
            'low_stock' => Item::where('quantity', '>', 0)->where('quantity', '<', 10)->count(),
            'out_of_stock' => Item::where('quantity', 0)->count(),
            'total_value' => Item::selectRaw('SUM(price * quantity) as total')->value('total') ?? 0,
            'avg_price' => Item::avg('price') ?? 0,
            'most_expensive' => Item::max('price') ?? 0,
            'total_quantity' => Item::sum('quantity') ?? 0
        ];

        $topItems = Item::with(['orderItems'])
                       ->get()
                       ->map(function($item) {
                           $item->total_sold = $item->orderItems->sum('quantity');
                           $item->total_revenue = $item->orderItems->sum('line_total');
                           return $item;
                       })
                       ->sortByDesc('total_sold')
                       ->take(10);

        $recentItems = Item::latest()->take(5)->get();

        $lowStockItems = Item::where('quantity', '>', 0)
                           ->where('quantity', '<', 10)
                           ->orderBy('quantity')
                           ->take(10)
                           ->get();

        return view('items.dashboard', compact('stats', 'topItems', 'recentItems', 'lowStockItems'));
    }
}
