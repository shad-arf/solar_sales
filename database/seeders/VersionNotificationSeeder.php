<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VersionNotification;

class VersionNotificationSeeder extends Seeder
{
    public function run()
    {
        // Version 1.1.0 - Category Management System
        VersionNotification::create([
            'version' => '1.1.0',
            'title' => 'Category Management System',
            'description' => 'Introducing dynamic category management for income and expenses with advanced features.',
            'features' => [
                'Dynamic category creation and management',
                'Separate income and expense categories',
                'Category status management (active/inactive)',
                'Category usage tracking and statistics',
                'Improved income and expense forms with smart category selection',
                'Enhanced pagination UI with Bootstrap 5 styling',
                'Category search and filtering capabilities'
            ],
            'release_date' => now(),
            'priority' => 'high',
            'is_active' => true
        ]);

        // Version 1.0.0 - Initial Release (for demo)
        VersionNotification::create([
            'version' => '1.0.0',
            'title' => 'Welcome to Solar Sales Management System',
            'description' => 'Your comprehensive solution for managing solar business operations.',
            'features' => [
                'Supplier and customer management',
                'Inventory tracking with low stock alerts',
                'Purchase order management',
                'Financial tracking (income and expenses)',
                'Sales management and invoicing',
                'Dashboard with key metrics',
                'User management system'
            ],
            'release_date' => now()->subDays(30),
            'priority' => 'medium',
            'is_active' => true
        ]);
    }
}