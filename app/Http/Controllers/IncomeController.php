<?php

namespace App\Http\Controllers;

use App\Models\Income;
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
        return view('income.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', array_keys(Income::CATEGORIES)),
            'date' => 'required|date',
            'reference_number' => 'nullable|string|max:50'
        ]);

        Income::create($request->all());

        return redirect()->route('income.index')
            ->with('success', 'Income recorded successfully!');
    }

    public function show(Income $income)
    {
        return view('income.show', compact('income'));
    }

    public function edit(Income $income)
    {
        return view('income.edit', compact('income'));
    }

    public function update(Request $request, Income $income)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', array_keys(Income::CATEGORIES)),
            'date' => 'required|date',
            'reference_number' => 'nullable|string|max:50'
        ]);

        $income->update($request->all());

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