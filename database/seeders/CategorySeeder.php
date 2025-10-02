<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        // Income Categories
        $incomeCategories = [
            ['name' => 'Solar Panel Sales', 'description' => 'Revenue from selling solar panels', 'type' => 'income'],
            ['name' => 'Installation Services', 'description' => 'Revenue from installation services', 'type' => 'income'],
            ['name' => 'Maintenance Services', 'description' => 'Revenue from maintenance and repair services', 'type' => 'income'],
            ['name' => 'Consultation Fees', 'description' => 'Revenue from consultation and advisory services', 'type' => 'income'],
            ['name' => 'Other Income', 'description' => 'Miscellaneous income sources', 'type' => 'income'],
        ];

        // Expense Categories
        $expenseCategories = [
            ['name' => 'Inventory Purchase', 'description' => 'Cost of purchasing inventory items', 'type' => 'expense'],
            ['name' => 'Rent', 'description' => 'Office and warehouse rent expenses', 'type' => 'expense'],
            ['name' => 'Utilities', 'description' => 'Electricity, water, internet, and phone bills', 'type' => 'expense'],
            ['name' => 'Fuel & Vehicle', 'description' => 'Fuel, vehicle maintenance, and transportation costs', 'type' => 'expense'],
            ['name' => 'Marketing & Advertising', 'description' => 'Marketing campaigns and advertising expenses', 'type' => 'expense'],
            ['name' => 'Office Supplies', 'description' => 'Stationery, office equipment, and supplies', 'type' => 'expense'],
            ['name' => 'Equipment', 'description' => 'Tools, machinery, and equipment purchases', 'type' => 'expense'],
            ['name' => 'Salaries & Wages', 'description' => 'Employee salaries and wage payments', 'type' => 'expense'],
            ['name' => 'Insurance', 'description' => 'Business insurance premiums', 'type' => 'expense'],
            ['name' => 'Other Expenses', 'description' => 'Miscellaneous business expenses', 'type' => 'expense'],
        ];

        foreach ($incomeCategories as $category) {
            Category::create($category);
        }

        foreach ($expenseCategories as $category) {
            Category::create($category);
        }
    }
}