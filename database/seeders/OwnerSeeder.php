<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Owner;

class OwnerSeeder extends Seeder
{
    public function run()
    {
        // Create default business owner
        Owner::create([
            'name' => 'Business Owner',
            'email' => 'owner@example.com',
            'phone' => '+1234567890',
            'address' => '123 Business Street, City, State 12345',
            'ownership_percentage' => 100.00,
            'notes' => 'Primary business owner and founder',
            'is_active' => true
        ]);

        // You can add more owners/investors here
        // Example of additional investor:
        /*
        Owner::create([
            'name' => 'John Investor',
            'email' => 'john@investor.com',
            'phone' => '+1987654321',
            'address' => '456 Investor Ave, City, State 54321',
            'ownership_percentage' => 25.00,
            'notes' => 'Silent partner investor',
            'is_active' => true
        ]);
        */
    }
}