<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers with their calculated loan.
     */
    public function index()
    {
        $customers = Customer::with('sales')->get();

        // Calculate loan for each
        foreach ($customers as $customer) {
            $customer->calculated_loan = $customer->sales->sum(fn($sale) => $sale->total - $sale->paid_amount);
        }

        return view('customers.index', compact('customers'));
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
}
