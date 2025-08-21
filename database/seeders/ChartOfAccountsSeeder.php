<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;

class ChartOfAccountsSeeder extends Seeder
{
    public function run()
    {
        $accounts = [
            // Assets
            ['name' => 'Cash', 'type' => 'asset', 'code' => '1000', 'description' => 'Cash on hand and in bank'],
            ['name' => 'Accounts Receivable', 'type' => 'asset', 'code' => '1100', 'description' => 'Money owed by customers'],
            ['name' => 'Inventory', 'type' => 'asset', 'code' => '1200', 'description' => 'Solar panels and equipment inventory'],
            ['name' => 'Equipment', 'type' => 'asset', 'code' => '1300', 'description' => 'Tools and equipment'],
            ['name' => 'Vehicles', 'type' => 'asset', 'code' => '1400', 'description' => 'Company vehicles'],

            // Liabilities
            ['name' => 'Accounts Payable', 'type' => 'liability', 'code' => '2000', 'description' => 'Money owed to suppliers'],
            ['name' => 'Loans Payable', 'type' => 'liability', 'code' => '2100', 'description' => 'Bank loans and credit'],
            ['name' => 'Taxes Payable', 'type' => 'liability', 'code' => '2200', 'description' => 'Taxes owed'],

            // Equity
            ['name' => 'Owner Capital', 'type' => 'equity', 'code' => '3000', 'description' => 'Owner initial investment'],
            ['name' => 'Retained Earnings', 'type' => 'equity', 'code' => '3100', 'description' => 'Accumulated profits'],

            // Revenue
            ['name' => 'Solar Sales Revenue', 'type' => 'revenue', 'code' => '4000', 'description' => 'Revenue from solar panel sales'],
            ['name' => 'Installation Revenue', 'type' => 'revenue', 'code' => '4100', 'description' => 'Revenue from installation services'],
            ['name' => 'Maintenance Revenue', 'type' => 'revenue', 'code' => '4200', 'description' => 'Revenue from maintenance services'],

            // Expenses
            ['name' => 'Cost of Goods Sold', 'type' => 'expense', 'code' => '5000', 'description' => 'Direct cost of solar panels sold'],
            ['name' => 'Salaries Expense', 'type' => 'expense', 'code' => '6000', 'description' => 'Employee salaries'],
            ['name' => 'Rent Expense', 'type' => 'expense', 'code' => '6100', 'description' => 'Office and warehouse rent'],
            ['name' => 'Utilities Expense', 'type' => 'expense', 'code' => '6200', 'description' => 'Electricity, water, internet'],
            ['name' => 'Fuel Expense', 'type' => 'expense', 'code' => '6300', 'description' => 'Vehicle fuel costs'],
            ['name' => 'Marketing Expense', 'type' => 'expense', 'code' => '6400', 'description' => 'Advertising and marketing'],
            ['name' => 'Office Supplies', 'type' => 'expense', 'code' => '6500', 'description' => 'Office supplies and materials'],
        ];

        foreach ($accounts as $account) {
            Account::firstOrCreate(['code' => $account['code']], $account);
        }
    }
}