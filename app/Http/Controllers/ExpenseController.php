<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Category;
use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        DB::beginTransaction();

        try {
            $expense = Expense::create($data);

            // Create accounting entries
            $this->createExpenseAccountingEntries($expense);

            DB::commit();

            return redirect()->route('expenses.index')
                ->with('success', 'Expense recorded successfully and accounting entries created!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create expense record: ' . $e->getMessage()])->withInput();
        }
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

        DB::beginTransaction();

        try {
            // Delete old transactions
            $this->deleteExpenseTransactions($expense);
            
            $expense->update($data);

            // Create new accounting entries
            $this->createExpenseAccountingEntries($expense);

            DB::commit();

            return redirect()->route('expenses.index')
                ->with('success', 'Expense updated successfully and accounting entries updated!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update expense record: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Expense $expense)
    {
        DB::beginTransaction();

        try {
            // Delete related transactions first
            $this->deleteExpenseTransactions($expense);
            
            $expense->delete();

            DB::commit();

            return redirect()->route('expenses.index')
                ->with('success', 'Expense deleted successfully and accounting entries removed!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete expense record: ' . $e->getMessage()]);
        }
    }

    /**
     * Create accounting entries for expense transactions
     */
    private function createExpenseAccountingEntries(Expense $expense)
    {
        // Get required accounts
        $cashAccount = Account::where('code', '1000')->first(); // Cash
        $expenseAccount = Account::where('code', '5000')->first(); // Expenses

        if (!$cashAccount || !$expenseAccount) {
            throw new \Exception('Required accounts (Cash or Expenses) not found. Please run database seeders.');
        }

        $amount = $expense->amount;
        $referenceNumber = "EX-{$expense->id}";
        $description = "Expense: {$expense->description}";

        // Expense decreases cash (Credit Cash) and increases expenses (Debit Expenses)

        // Credit Cash (decrease cash)
        Transaction::create([
            'account_id' => $cashAccount->id,
            'description' => $description,
            'debit_amount' => 0,
            'credit_amount' => $amount,
            'transaction_date' => $expense->date,
            'reference_number' => $referenceNumber,
            'transaction_type' => 'expense'
        ]);

        // Debit Expenses (increase expenses)
        Transaction::create([
            'account_id' => $expenseAccount->id,
            'description' => $description,
            'debit_amount' => $amount,
            'credit_amount' => 0,
            'transaction_date' => $expense->date,
            'reference_number' => $referenceNumber,
            'transaction_type' => 'expense'
        ]);
    }

    /**
     * Delete transactions related to an expense record
     */
    private function deleteExpenseTransactions(Expense $expense)
    {
        $referenceNumber = "EX-{$expense->id}";
        Transaction::where('reference_number', $referenceNumber)->delete();
    }
}