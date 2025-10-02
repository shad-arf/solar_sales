<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Models\OwnerEquity;
use Illuminate\Http\Request;

class OwnerController extends Controller
{
    public function index(Request $request)
    {
        $query = Owner::query();
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        $owners = $query->with(['ownerEquities'])
            ->orderBy('name')
            ->paginate(15);
        
        // Statistics
        $stats = [
            'total_owners' => Owner::count(),
            'active_owners' => Owner::active()->count(),
            'total_investments' => Owner::getTotalInvestments(),
            'total_drawings' => Owner::getTotalDrawings(),
            'net_equity' => Owner::getNetEquity()
        ];
        
        return view('owners.index', compact('owners', 'stats'));
    }

    public function create()
    {
        return view('owners.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:owners,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'ownership_percentage' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');

        Owner::create($validated);

        return redirect()->route('owners.index')
                        ->with('success', 'Owner created successfully.');
    }

    public function show(Owner $owner)
    {
        $owner->load(['ownerEquities' => function($query) {
            $query->orderBy('transaction_date', 'desc');
        }]);
        
        // Get recent transactions (last 5)
        $recentTransactions = $owner->ownerEquities()
            ->orderBy('transaction_date', 'desc')
            ->limit(5)
            ->get();
        
        return view('owners.show', compact('owner', 'recentTransactions'));
    }

    public function edit(Owner $owner)
    {
        return view('owners.edit', compact('owner'));
    }

    public function update(Request $request, Owner $owner)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:owners,email,' . $owner->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'ownership_percentage' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');

        $owner->update($validated);

        return redirect()->route('owners.index')
                        ->with('success', 'Owner updated successfully.');
    }

    public function destroy(Owner $owner)
    {
        // Check if owner has equity transactions
        $transactionCount = $owner->ownerEquities()->count();
        
        if ($transactionCount > 0) {
            return back()->withErrors(['error' => 'Cannot delete owner with existing equity transactions. Consider deactivating instead.']);
        }

        $owner->delete();

        return redirect()->route('owners.index')
                        ->with('success', 'Owner deleted successfully.');
    }

    public function toggleStatus(Owner $owner)
    {
        $owner->update(['is_active' => !$owner->is_active]);
        
        $status = $owner->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Owner {$status} successfully.");
    }

    public function equity(Owner $owner, Request $request)
    {
        $query = $owner->ownerEquities();
        
        // Filter by transaction type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }
        
        $transactions = $query->orderBy('transaction_date', 'desc')
            ->paginate(20);
            
        return view('owners.equity', compact('owner', 'transactions'));
    }
}