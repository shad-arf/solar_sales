<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemPrice;
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

        // Price range filters - check both new pricing structure and legacy fields
        if ($request->filled('price_min')) {
            $priceMin = $request->price_min;
            $query->where(function($q) use ($priceMin) {
                // Check new pricing structure
                $q->whereHas('itemPrices', function($priceQuery) use ($priceMin) {
                    $priceQuery->where('is_active', true)->where('price', '>=', $priceMin);
                })
                // Fallback to legacy pricing
                ->orWhere('price', '>=', $priceMin);
            });
        }

        if ($request->filled('price_max')) {
            $priceMax = $request->price_max;
            $query->where(function($q) use ($priceMax) {
                // Check new pricing structure
                $q->whereHas('itemPrices', function($priceQuery) use ($priceMax) {
                    $priceQuery->where('is_active', true)->where('price', '<=', $priceMax);
                })
                // Fallback to legacy pricing
                ->orWhere('price', '<=', $priceMax);
            });
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

        // Pagination with optimized eager loading
        $perPage = $request->get('per_page', 15);
        $items = $query->with([
            'activePrices',
            'orderItems' => function($q) {
                $q->select('id', 'item_id', 'sale_id', 'quantity', 'unit_price');
            },
            'orderItems.sale' => function($q) {
                $q->select('id', 'customer_id', 'sale_date', 'total');
            },
            'orderItems.sale.customer' => function($q) {
                $q->select('id', 'name')->withTrashed();
            }
        ])->paginate($perPage);

        // Calculate comprehensive statistics with profit analysis
        $stats = [
            'total_items' => Item::count(),
            'low_stock' => Item::where('quantity', '>', 0)->where('quantity', '<', 10)->count(),
            'out_of_stock' => Item::where('quantity', 0)->count(),
            'total_value' => 0,
            'realized_profit' => 0,
            'unrealized_profit' => 0,
            'total_cost' => 0,
        ];

        // Calculate realized profit (from actual sales)
        $realizedProfitQuery = DB::table('orders')
            ->join('items', 'orders.item_id', '=', 'items.id')
            ->leftJoin('purchase_items', function($join) {
                $join->on('purchase_items.item_id', '=', 'orders.item_id');
            })
            ->leftJoin('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->selectRaw('
                SUM(orders.quantity * orders.unit_price) as total_sales_value,
                SUM(orders.quantity * COALESCE(purchase_items.purchase_price, items.price * 0.7)) as total_cost_value
            ')
            ->whereNull('orders.deleted_at')
            ->first();

        if ($realizedProfitQuery) {
            $stats['realized_profit'] = ($realizedProfitQuery->total_sales_value ?? 0) - ($realizedProfitQuery->total_cost_value ?? 0);
        }

        // Calculate unrealized profit (current inventory value vs estimated cost)
        $unrealizedQuery = DB::table('items')
            ->leftJoin('purchase_items', 'purchase_items.item_id', '=', 'items.id')
            ->selectRaw('
                SUM(items.quantity * COALESCE(
                    (SELECT price FROM item_prices WHERE item_id = items.id AND is_default = 1 AND is_active = 1 LIMIT 1),
                    items.price
                )) as inventory_selling_value,
                SUM(items.quantity * COALESCE(purchase_items.purchase_price, items.price * 0.7)) as inventory_cost_value
            ')
            ->whereNull('items.deleted_at')
            ->where('items.quantity', '>', 0)
            ->first();

        if ($unrealizedQuery) {
            $stats['total_value'] = $unrealizedQuery->inventory_selling_value ?? 0;
            $stats['total_cost'] = $unrealizedQuery->inventory_cost_value ?? 0;
            $stats['unrealized_profit'] = $stats['total_value'] - $stats['total_cost'];
        }

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

        // Get all results for export with pricing
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $items = $query->with(['itemPrices' => function($q) {
            $q->where('is_active', true)->orderBy('sort_order');
        }])->orderBy($sortBy, $sortDirection)->get();

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
                'Primary Price',
                'All Prices',
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

                // Get pricing information
                $primaryPrice = $item->primary_price;
                $allPrices = $item->itemPrices->map(function($price) {
                    return $price->name . ': $' . number_format($price->price, 2);
                })->implode('; ') ?: 'Legacy: $' . number_format($item->price, 2);

                fputcsv($file, [
                    $item->code,
                    $item->name,
                    $item->description ?? '',
                    number_format($primaryPrice, 2),
                    $allPrices,
                    $item->quantity,
                    $stockStatus,
                    number_format($primaryPrice * $item->quantity, 2),
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
        $item->load('activePrices');
        return view('items.edit', compact('item'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'code'            => 'required|string|max:255|unique:items,code',
            'description'     => 'nullable|string',
            'pricing'         => 'required|array|min:1',
            'pricing.*.unit_name' => 'required|string|max:100',
            'pricing.*.unit_description' => 'nullable|string|max:255',
            'pricing.*.price' => 'required|numeric|min:0',
            'default_pricing' => 'required|integer|min:0'
        ]);

        DB::beginTransaction();
        
        try {
            // Create the item first
            $item = Item::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'description' => $validated['description'],
                'quantity' => 0, // Default quantity to 0 for new items
                // Keep legacy price field for backward compatibility
                'price' => $validated['pricing'][$validated['default_pricing']]['price'] ?? 0,
            ]);

            // Create pricing records
            foreach ($validated['pricing'] as $index => $priceData) {
                ItemPrice::create([
                    'item_id' => $item->id,
                    'name' => $priceData['unit_name'],
                    'price' => $priceData['price'],
                    'unit' => $this->extractUnit($priceData['unit_name']),
                    'description' => $priceData['unit_description'],
                    'category_id' => null, // For future use
                    'is_default' => $index == $validated['default_pricing'],
                    'is_active' => true,
                    'sort_order' => $index
                ]);
            }

            // Also maintain backward compatibility by storing in legacy fields
            $pricingData = $validated['pricing'];
            $updateData = [];
            
            if (isset($pricingData[0])) {
                $updateData['price'] = $pricingData[0]['price'];
            }
            if (isset($pricingData[1])) {
                $updateData['base_price'] = $pricingData[1]['price'];
            }
            if (isset($pricingData[2])) {
                $updateData['operator_price'] = $pricingData[2]['price'];
            }
            
            if (!empty($updateData)) {
                $item->update($updateData);
            }

            DB::commit();
            
            return redirect()->route('items.index')->with('success', "Item '{$item->name}' created successfully with " . count($validated['pricing']) . " pricing options.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create item: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Extract unit from pricing name (e.g., "Per piece" -> "piece")
     */
    private function extractUnit($unitName)
    {
        $unitName = strtolower($unitName);
        if (str_contains($unitName, 'per ')) {
            return trim(str_replace('per ', '', $unitName));
        }
        if (str_contains($unitName, 'price')) {
            return null; // No specific unit for general price
        }
        return $unitName;
    }

    public function update(Request $request, Item $item)
    {
        // Check if this is a multi-pricing update or traditional update
        if ($request->has('pricing')) {
            // Multi-pricing update from edit form
            $validated = $request->validate([
                'name'            => 'required|string|max:255',
                'code'            => 'required|string|max:255|unique:items,code,' . $item->id,
                'description'     => 'nullable|string',
                'quantity'        => 'required|integer|min:0',
                'pricing'         => 'required|array|min:1',
                'pricing.*.unit_name' => 'required|string|max:100',
                'pricing.*.unit_description' => 'nullable|string|max:255',
                'pricing.*.price' => 'required|numeric|min:0',
                'default_pricing' => 'required|integer|min:0'
            ]);

            DB::beginTransaction();
            
            try {
                // Update item basic info
                $item->update([
                    'name' => $validated['name'],
                    'code' => $validated['code'],
                    'description' => $validated['description'],
                    'quantity' => $validated['quantity'],
                    // Keep legacy price field for backward compatibility
                    'price' => $validated['pricing'][$validated['default_pricing']]['price'] ?? 0,
                ]);

                // Delete existing prices and create new ones
                $item->prices()->delete();
                
                foreach ($validated['pricing'] as $index => $priceData) {
                    ItemPrice::create([
                        'item_id' => $item->id,
                        'name' => $priceData['unit_name'],
                        'price' => $priceData['price'],
                        'unit' => $this->extractUnit($priceData['unit_name']),
                        'description' => $priceData['unit_description'],
                        'category_id' => null,
                        'is_default' => $index == $validated['default_pricing'],
                        'is_active' => true,
                        'sort_order' => $index
                    ]);
                }

                // Also maintain backward compatibility by storing in legacy fields
                $pricingData = $validated['pricing'];
                $updateData = [];
                
                if (isset($pricingData[0])) {
                    $updateData['price'] = $pricingData[0]['price'];
                }
                if (isset($pricingData[1])) {
                    $updateData['base_price'] = $pricingData[1]['price'];
                }
                if (isset($pricingData[2])) {
                    $updateData['operator_price'] = $pricingData[2]['price'];
                }
                
                if (!empty($updateData)) {
                    $item->update($updateData);
                }

                DB::commit();
                
                return redirect()->route('items.index')->with('success', "Item '{$item->name}' updated successfully with " . count($validated['pricing']) . " pricing options.");
                
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Failed to update item: ' . $e->getMessage()])->withInput();
            }
            
        } else {
            // Traditional update or quick pricing update
            $validated = $request->validate([
                'name'            => 'sometimes|required|string|max:255',
                'code'            => 'sometimes|required|string|max:255|unique:items,code,' . $item->id,
                'description'     => 'nullable|string',
                'price'           => 'nullable|numeric|min:0',
                'base_price'      => 'nullable|numeric|min:0',
                'operator_price'  => 'nullable|numeric|min:0',
                'quantity'        => 'sometimes|required|integer|min:0',
            ]);

            DB::beginTransaction();
            
            try {
                $item->update($validated);
                
                // If this is a pricing update, update the prices table too
                if ($request->hasAny(['price', 'base_price', 'operator_price'])) {
                    $this->updatePricingTable($item, $validated);
                }
                
                DB::commit();
                
                // Check if this was a pricing-only update
                if ($request->hasAny(['price', 'base_price', 'operator_price']) && !$request->has('name')) {
                    return redirect()->route('items.index')->with('success', "Pricing updated for '{$item->name}' successfully.");
                }
                
                return redirect()->route('items.index')->with('success', "Item '{$item->name}' updated successfully.");
                
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Failed to update item: ' . $e->getMessage()]);
            }
        }
    }

    /**
     * Update pricing table based on legacy price fields
     */
    private function updatePricingTable(Item $item, array $validated)
    {
        $pricesToUpdate = [];
        
        if (isset($validated['price']) && $validated['price'] > 0) {
            $pricesToUpdate[] = [
                'name' => 'Regular Price',
                'price' => $validated['price'],
                'unit' => null,
                'description' => 'Standard selling price',
                'sort_order' => 0,
                'is_default' => true
            ];
        }
        
        if (isset($validated['base_price']) && $validated['base_price'] > 0) {
            $pricesToUpdate[] = [
                'name' => 'Wholesale Price',
                'price' => $validated['base_price'],
                'unit' => null,
                'description' => 'Bulk/wholesale pricing',
                'sort_order' => 1,
                'is_default' => false
            ];
        }
        
        if (isset($validated['operator_price']) && $validated['operator_price'] > 0) {
            $pricesToUpdate[] = [
                'name' => 'Operator Price',
                'price' => $validated['operator_price'],
                'unit' => null,
                'description' => 'Special operator pricing',
                'sort_order' => 2,
                'is_default' => false
            ];
        }
        
        if (!empty($pricesToUpdate)) {
            // Delete existing prices from this legacy update
            $item->prices()->whereIn('name', ['Regular Price', 'Wholesale Price', 'Operator Price'])->delete();
            
            // Create new prices
            foreach ($pricesToUpdate as $priceData) {
                ItemPrice::create(array_merge($priceData, [
                    'item_id' => $item->id,
                    'category_id' => null,
                    'is_active' => true
                ]));
            }
        }
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

    /**
     * Show pricing management page
     */
    public function pricing()
    {
        $items = Item::select('id', 'name', 'code')->orderBy('name')->get();
        return view('items.pricing', compact('items'));
    }

    /**
     * Store pricing data
     */
    public function pricingStore(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'unit' => 'required|string|max:100',
            'price_types' => 'required|array|min:1',
            'price_types.*.name' => 'required|string|max:100',
            'price_types.*.price' => 'required|numeric|min:0',
            'price_types.*.description' => 'nullable|string|max:255'
        ]);

        $item = Item::findOrFail($validated['item_id']);
        
        // Create a pricing data structure
        $pricingData = [
            'unit' => $validated['unit'],
            'price_types' => $validated['price_types'],
            'updated_at' => now()
        ];

        // Store in a JSON field or create a separate pricing table
        // For now, we'll update the item with the first 3 prices in existing fields
        $updateData = [];
        
        foreach ($validated['price_types'] as $index => $priceType) {
            switch($index) {
                case 0:
                    $updateData['price'] = $priceType['price'];
                    break;
                case 1:
                    $updateData['base_price'] = $priceType['price'];
                    break;
                case 2:
                    $updateData['operator_price'] = $priceType['price'];
                    break;
            }
        }

        // If you want to store all pricing data, you could add a JSON field to items table
        // $updateData['pricing_data'] = json_encode($pricingData);

        $item->update($updateData);

        return redirect()->route('items.pricing')
            ->with('success', "Pricing updated for {$item->name}. Unit: {$validated['unit']}");
    }
}
