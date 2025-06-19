<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers with search and filters
     */
    public function index(Request $request)
    {
        // Build the query
        $query = Customer::with('sales');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%")
                  ->orWhere('state', 'LIKE', "%{$search}%")
                  ->orWhere('country', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%")
                  ->orWhere('note', 'LIKE', "%{$search}%");
            });
        }

        // Country filter
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        // City filter
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        // Contact info filters
        if ($request->filled('has_email')) {
            $query->whereNotNull('email')->where('email', '!=', '');
        }

        if ($request->filled('has_phone')) {
            $query->whereNotNull('phone')->where('phone', '!=', '');
        }

        if ($request->filled('has_address')) {
            $query->whereNotNull('address')
                  ->where('address', '!=', '')
                  ->whereNotNull('city')
                  ->where('city', '!=', '');
        }

        // Date range filters
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }

        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        // Get all customers first to calculate loans
        $allCustomers = $query->get();

        // Calculate loan for each customer
        foreach ($allCustomers as $customer) {
            $customer->calculated_loan = $customer->sales->sum(fn($sale) => $sale->total - $sale->paid_amount);
        }

        // Apply loan-based filters
        if ($request->filled('loan_status')) {
            $allCustomers = $allCustomers->filter(function($customer) use ($request) {
                $loan = $customer->calculated_loan;
                return match($request->loan_status) {
                    'with_loan' => $loan > 0,
                    'no_loan' => $loan <= 0,
                    'paid_up' => $loan == 0,
                    default => true
                };
            });
        }

        // Loan amount filters
        if ($request->filled('loan_min')) {
            $allCustomers = $allCustomers->filter(fn($customer) => $customer->calculated_loan >= $request->loan_min);
        }

        if ($request->filled('loan_max')) {
            $allCustomers = $allCustomers->filter(fn($customer) => $customer->calculated_loan <= $request->loan_max);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');

        $allCustomers = $allCustomers->sortBy(function($customer) use ($sortBy) {
            return match($sortBy) {
                'loan' => $customer->calculated_loan,
                'created_at' => $customer->created_at,
                'city' => $customer->city ?? '',
                'country' => $customer->country ?? '',
                'name' => $customer->name,
                default => $customer->name
            };
        });

        if ($sortDirection === 'desc') {
            $allCustomers = $allCustomers->reverse();
        }

        // Convert back to collection and paginate manually
        $perPage = $request->get('per_page', 15);
        $currentPage = request()->get('page', 1);
        $total = $allCustomers->count();

        $customers = $allCustomers->slice(($currentPage - 1) * $perPage, $perPage);

        // Create paginator
        $customers = new \Illuminate\Pagination\LengthAwarePaginator(
            $customers->values(),
            $total,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page'
            ]
        );

        // Get filter options
        $countries = Customer::whereNotNull('country')
                           ->where('country', '!=', '')
                           ->distinct()
                           ->orderBy('country')
                           ->pluck('country');

        $cities = Customer::whereNotNull('city')
                        ->where('city', '!=', '')
                        ->distinct()
                        ->orderBy('city')
                        ->pluck('city');

        // Calculate statistics
        $allCustomersForStats = Customer::with('sales')->get();
        $allCustomersForStats->each(function($customer) {
            $customer->calculated_loan = $customer->sales->sum(fn($sale) => $sale->total - $sale->paid_amount);
        });

        $stats = [
            'total_customers' => $allCustomersForStats->count(),
            'customers_with_loans' => $allCustomersForStats->filter(fn($c) => $c->calculated_loan > 0)->count(),
            'total_outstanding' => $allCustomersForStats->sum('calculated_loan'),
            'average_loan' => $allCustomersForStats->count() > 0 ?
                            $allCustomersForStats->sum('calculated_loan') / $allCustomersForStats->count() : 0
        ];

        return view('customers.index', compact('customers', 'countries', 'cities', 'stats'));
    }

    /**
     * Export filtered customers to CSV
     */
    public function export(Request $request)
    {
        // Use the same query logic as index but without pagination
        $query = Customer::with('sales');

        // Apply all the same filters...
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%")
                  ->orWhere('state', 'LIKE', "%{$search}%")
                  ->orWhere('country', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%")
                  ->orWhere('note', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('has_email')) {
            $query->whereNotNull('email')->where('email', '!=', '');
        }

        if ($request->filled('has_phone')) {
            $query->whereNotNull('phone')->where('phone', '!=', '');
        }

        if ($request->filled('has_address')) {
            $query->whereNotNull('address')
                  ->where('address', '!=', '')
                  ->whereNotNull('city')
                  ->where('city', '!=', '');
        }

        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }

        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        // Get all customers and calculate loans
        $customers = $query->get();

        foreach ($customers as $customer) {
            $customer->calculated_loan = $customer->sales->sum(fn($sale) => $sale->total - $sale->paid_amount);
        }

        // Apply loan-based filters
        if ($request->filled('loan_status')) {
            $customers = $customers->filter(function($customer) use ($request) {
                $loan = $customer->calculated_loan;
                return match($request->loan_status) {
                    'with_loan' => $loan > 0,
                    'no_loan' => $loan <= 0,
                    'paid_up' => $loan == 0,
                    default => true
                };
            });
        }

        if ($request->filled('loan_min')) {
            $customers = $customers->filter(fn($customer) => $customer->calculated_loan >= $request->loan_min);
        }

        if ($request->filled('loan_max')) {
            $customers = $customers->filter(fn($customer) => $customer->calculated_loan <= $request->loan_max);
        }

        $filename = 'customers_export_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'Name',
                'Email',
                'Phone',
                'Address',
                'City',
                'State',
                'Country',
                'Outstanding Loan',
                'Total Sales',
                'Date Added',
                'Notes'
            ]);

            foreach ($customers as $customer) {
                $totalSales = $customer->sales->count();
                $loan = $customer->calculated_loan;

                fputcsv($file, [
                    $customer->name,
                    $customer->email ?? '',
                    $customer->phone ?? '',
                    $customer->address ?? '',
                    $customer->city ?? '',
                    $customer->state ?? '',
                    $customer->country ?? '',
                    number_format($loan, 2),
                    $totalSales,
                    $customer->created_at->format('Y-m-d'),
                    $customer->note ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show form to create a new customer.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'nullable|email|unique:customers,email',
            'phone'    => 'nullable|string|unique:customers,phone',
            'address'  => 'nullable|string',
            'city'     => 'nullable|string|max:100',
            'state'    => 'nullable|string|max:100',
            'country'  => 'nullable|string|max:100',
            'note'     => 'nullable|string',
        ]);

        Customer::create($validated);

        return redirect()->route('customers.index')
                         ->with('success', 'Customer created successfully.');
    }

    /**
     * Show form to edit an existing customer.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone'    => 'nullable|string|unique:customers,phone,' . $customer->id,
            'address'  => 'nullable|string',
            'city'     => 'nullable|string|max:100',
            'state'    => 'nullable|string|max:100',
            'country'  => 'nullable|string|max:100',
            'note'     => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')
                         ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer (soft delete).
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')
                         ->with('success', 'Customer deleted.');
    }

    /**
     * Display a listing of soft-deleted customers.
     */
    public function trashed()
    {
        $customers = Customer::onlyTrashed()->with('sales')->get();

        foreach ($customers as $customer) {
            $customer->calculated_loan = $customer->sales
                ->sum(fn($sale) => $sale->total - $sale->paid_amount);
        }

        return view('customers.trashed', compact('customers'));
    }

    /**
     * Restore a soft-deleted customer.
     */
    public function restore($id)
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $customer->restore();

        return redirect()->route('customers.trashed')
                         ->with('success', 'Customer restored.');
    }

    /**
     * Permanently delete a soft-deleted customer.
     */
    public function forceDelete($id)
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $customer->forceDelete();

        return redirect()->route('customers.trashed')
                         ->with('success', 'Customer permanently deleted.');
    }

    /**
     * Clear all outstanding loan for a customer:
     *    - mark each sale as fully paid (paid_amount = total)
     *    - reset loan field to zero
     */
    public function clearLoan(Customer $customer)
    {
        foreach ($customer->sales as $sale) {
            $sale->update([
                'paid_amount' => $sale->total,
            ]);
        }

        $customer->update(['loan' => 0]);

        return back()->with('success', 'Customer loan has been cleared.');
    }

    /**
     * Get customer dashboard/summary data
     */
    public function dashboard()
    {
        $stats = [
            'total_customers' => Customer::count(),
            'new_this_month' => Customer::whereMonth('created_at', now()->month)
                                     ->whereYear('created_at', now()->year)
                                     ->count(),
            'with_loans' => Customer::whereHas('sales', function($query) {
                $query->whereRaw('total > paid_amount');
            })->count(),
            'total_outstanding' => Customer::with('sales')
                                         ->get()
                                         ->sum(function($customer) {
                                             return $customer->sales->sum(fn($sale) => $sale->total - $sale->paid_amount);
                                         })
        ];

        $topCustomers = Customer::with('sales')
                              ->get()
                              ->map(function($customer) {
                                  $customer->total_purchases = $customer->sales->sum('total');
                                  $customer->outstanding = $customer->sales->sum(fn($sale) => $sale->total - $sale->paid_amount);
                                  return $customer;
                              })
                              ->sortByDesc('total_purchases')
                              ->take(10);

        $recentCustomers = Customer::latest()->take(5)->get();

        return view('customers.dashboard', compact('stats', 'topCustomers', 'recentCustomers'));
    }
}
