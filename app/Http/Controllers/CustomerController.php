<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
  public function index()
{
    $customers = Customer::with(['sales'])->get();

    foreach ($customers as $customer) {
        $customer->calculated_loan = $customer->sales->sum(fn($sale) => $sale->total - $sale->paid);
    }

    return view('customers.index', compact('customers'));
}


    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Customer::create($validated);
        return redirect()->route('customers.index')->with('success', 'Customer created.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $customer->update($validated);
        return redirect()->route('customers.index')->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted.');
    }

        public function clearLoan(Customer $customer)
        {
            // Update each sale: paid = total
            foreach ($customer->sales as $sale) {
                $sale->update(['paid' => $sale->total]);
            }

            // Clear loan from customer record
            $customer->update(['loan' => 0]);

            return back()->with('success', 'Customer loan has been cleared and all sales marked as fully paid.');
        }

}
