<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Category;
use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        DB::beginTransaction();

        try {
            $income = Income::create($data);

            // Create accounting entries
            $this->createIncomeAccountingEntries($income);

            DB::commit();

            return redirect()->route('income.index')
                ->with('success', 'Income recorded successfully and accounting entries created!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create income record: ' . $e->getMessage()])->withInput();
        }
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

        DB::beginTransaction();

        try {
            // Delete old transactions
            $this->deleteIncomeTransactions($income);
            
            $income->update($data);

            // Create new accounting entries
            $this->createIncomeAccountingEntries($income);

            DB::commit();

            return redirect()->route('income.index')
                ->with('success', 'Income updated successfully and accounting entries updated!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update income record: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Income $income)
    {
        DB::beginTransaction();

        try {
            // Delete related transactions first
            $this->deleteIncomeTransactions($income);
            
            $income->delete();

            DB::commit();

            return redirect()->route('income.index')
                ->with('success', 'Income deleted successfully and accounting entries removed!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete income record: ' . $e->getMessage()]);
        }
    }

    /**
     * Create accounting entries for income transactions
     */
    private function createIncomeAccountingEntries(Income $income)
    {
        // Get required accounts
        $cashAccount = Account::where('code', '1000')->first(); // Cash
        $revenueAccount = Account::where('code', '4000')->first(); // Revenue

        if (!$cashAccount || !$revenueAccount) {
            throw new \Exception('Required accounts (Cash or Revenue) not found. Please run database seeders.');
        }

        $amount = $income->amount;
        $referenceNumber = "IN-{$income->id}";
        $description = "Income: {$income->description}";

        // Income increases cash (Debit Cash) and increases revenue (Credit Revenue)

        // Debit Cash (increase cash)
        Transaction::create([
            'account_id' => $cashAccount->id,
            'description' => $description,
            'debit_amount' => $amount,
            'credit_amount' => 0,
            'transaction_date' => $income->date,
            'reference_number' => $referenceNumber,
            'transaction_type' => 'revenue'
        ]);

        // Credit Revenue (increase revenue)
        Transaction::create([
            'account_id' => $revenueAccount->id,
            'description' => $description,
            'debit_amount' => 0,
            'credit_amount' => $amount,
            'transaction_date' => $income->date,
            'reference_number' => $referenceNumber,
            'transaction_type' => 'revenue'
        ]);
    }

    /**
     * Delete transactions related to an income record
     */
    private function deleteIncomeTransactions(Income $income)
    {
        $referenceNumber = "IN-{$income->id}";
        Transaction::where('reference_number', $referenceNumber)->delete();
    }
}