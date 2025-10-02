<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function index()
    {
        $incomes = Income::orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('income.index', compact('incomes'));
    }

    public function create()
    {
        $categories = Category::income()->active()->get();
        return view('income.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'date' => 'required|date',
            'reference_number' => 'nullable|string|max:50'
        ]);

        // Get the category for legacy support
        $category = Category::find($request->category_id);
        $data = $request->all();
        $data['category'] = $category->name; // Store legacy category name

        Income::create($data);

        return redirect()->route('income.index')
            ->with('success', 'Income recorded successfully!');
    }

    public function show(Income $income)
    {
        return view('income.show', compact('income'));
    }

    public function edit(Income $income)
    {
        $categories = Category::income()->active()->get();
        return view('income.edit', compact('income', 'categories'));
    }

    public function update(Request $request, Income $income)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'date' => 'required|date',
            'reference_number' => 'nullable|string|max:50'
        ]);

        // Get the category for legacy support
        $category = Category::find($request->category_id);
        $data = $request->all();
        $data['category'] = $category->name; // Store legacy category name

        $income->update($data);

        return redirect()->route('income.index')
            ->with('success', 'Income updated successfully!');
    }

    public function destroy(Income $income)
    {
        $income->delete();

        return redirect()->route('income.index')
            ->with('success', 'Income deleted successfully!');
    }
}