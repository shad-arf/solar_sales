<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('account')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $accounts = Account::where('is_active', true)
            ->orderBy('code')
            ->get()
            ->groupBy('type');

        return view('transactions.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:debit,credit',
            'transaction_date' => 'required|date',
            'reference_number' => 'nullable|string|max:50',
            'transaction_type' => 'required|in:revenue,expense,owner_investment,owner_drawing,purchase,sale,other'
        ]);

        $data = $request->all();
        
        // Set debit or credit amount based on type
        if ($request->type === 'debit') {
            $data['debit_amount'] = $request->amount;
            $data['credit_amount'] = 0;
        } else {
            $data['credit_amount'] = $request->amount;
            $data['debit_amount'] = 0;
        }

        unset($data['amount'], $data['type']);

        Transaction::create($data);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction recorded successfully!');
    }

    public function show(Transaction $transaction)
    {
        $transaction->load('account');
        return view('transactions.show', compact('transaction'));
    }

    public function edit(Transaction $transaction)
    {
        $accounts = Account::where('is_active', true)
            ->orderBy('code')
            ->get()
            ->groupBy('type');

        return view('transactions.edit', compact('transaction', 'accounts'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:debit,credit',
            'transaction_date' => 'required|date',
            'reference_number' => 'nullable|string|max:50',
            'transaction_type' => 'required|in:revenue,expense,owner_investment,owner_drawing,purchase,sale,other'
        ]);

        $data = $request->all();
        
        // Set debit or credit amount based on type
        if ($request->type === 'debit') {
            $data['debit_amount'] = $request->amount;
            $data['credit_amount'] = 0;
        } else {
            $data['credit_amount'] = $request->amount;
            $data['debit_amount'] = 0;
        }

        unset($data['amount'], $data['type']);

        $transaction->update($data);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully!');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully!');
    }

    // Helper method to record automatic transactions from purchases/sales
    public static function recordSaleTransaction($saleAmount, $description = 'Sale transaction')
    {
        // Find sales revenue account
        $salesAccount = Account::where('code', '4000')->first();
        if ($salesAccount) {
            Transaction::create([
                'account_id' => $salesAccount->id,
                'description' => $description,
                'debit_amount' => 0,
                'credit_amount' => $saleAmount,
                'transaction_date' => Carbon::now(),
                'transaction_type' => 'sale'
            ]);
        }
    }

    public static function recordPurchaseTransaction($purchaseAmount, $description = 'Purchase transaction')
    {
        // Find cost of goods sold account
        $cogsAccount = Account::where('code', '5000')->first();
        if ($cogsAccount) {
            Transaction::create([
                'account_id' => $cogsAccount->id,
                'description' => $description,
                'debit_amount' => $purchaseAmount,
                'credit_amount' => 0,
                'transaction_date' => Carbon::now(),
                'transaction_type' => 'purchase'
            ]);
        }
    }

    public static function recordExpenseTransaction($accountCode, $amount, $description)
    {
        $account = Account::where('code', $accountCode)->first();
        if ($account) {
            Transaction::create([
                'account_id' => $account->id,
                'description' => $description,
                'debit_amount' => $amount,
                'credit_amount' => 0,
                'transaction_date' => Carbon::now(),
                'transaction_type' => 'expense'
            ]);
        }
    }

    public static function recordRevenueTransaction($accountCode, $amount, $description)
    {
        $account = Account::where('code', $accountCode)->first();
        if ($account) {
            Transaction::create([
                'account_id' => $account->id,
                'description' => $description,
                'debit_amount' => 0,
                'credit_amount' => $amount,
                'transaction_date' => Carbon::now(),
                'transaction_type' => 'revenue'
            ]);
        }
    }
}