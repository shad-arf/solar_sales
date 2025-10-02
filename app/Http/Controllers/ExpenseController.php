<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('expenses.index', compact('expenses'));
    }

    public function create()
    {
        $categories = Category::expense()->active()->get();
        return view('expenses.create', compact('categories'));
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

        Expense::create($data);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense recorded successfully!');
    }

    public function show(Expense $expense)
    {
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $categories = Category::expense()->active()->get();
        return view('expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
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

        $expense->update($data);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully!');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully!');
    }
}