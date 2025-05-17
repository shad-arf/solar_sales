<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        // Show only active items
        $items = Item::all();
        return view('items.index', compact('items'));
    }

    public function lowStock()
    {
        $items = Item::where('quantity', '<', 10)->get();
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
    return view('items.show', compact('item'));
}


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'code'     => 'required|string|max:255|unique:items,code',
            'price'    => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
        ]);

        Item::create($validated);
        return redirect()->route('items.index')->with('success', 'Item created.');
    }

    public function edit(Item $item)
    {
        return view('items.edit', compact('item'));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'code'     => 'required|string|max:255|unique:items,code,' . $item->id,
            'price'    => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
        ]);

        $item->update($validated);
        return redirect()->route('items.index')->with('success', 'Item updated.');
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->route('items.index')->with('success', 'Item soft deleted.');
    }

    public function restore($id)
    {
        $item = Item::withTrashed()->findOrFail($id);
        $item->restore();
        return redirect()->route('items.trashed')->with('success', 'Item restored.');
    }

    public function forceDelete($id)
    {
        $item = Item::withTrashed()->findOrFail($id);
        $item->forceDelete();
        return redirect()->route('items.trashed')->with('success', 'Item permanently deleted.');
    }
}
