# Claude Code Project Guide

## Project Overview
This is a Laravel-based Solar Sales Management System that handles supplier management, item inventory, and purchase order management.

## Key Components

### Models
- **Supplier**: Manages supplier information with active/inactive status
- **Item**: Inventory items with categories and pricing
- **Purchase**: Purchase orders linking suppliers and items
- **PurchaseItem**: Pivot table for purchase-item relationships

### Database Structure
- **suppliers**: Contains `status` field (active/inactive) and `is_active` boolean
- **items**: Product inventory with categories
- **purchases**: Purchase orders with status tracking
- **purchase_items**: Line items for purchases

### Controllers
- **SupplierController**: CRUD operations, status toggle functionality
- **ItemController**: Item management and inventory
- **PurchaseController**: Purchase order management

### Key Routes
- `suppliers.toggleStatus` - POST route for toggling supplier active/inactive status
- Standard resource routes for suppliers, items, purchases

### Views Structure
- **suppliers/**: index, create, edit, show views with status management
- **items/**: inventory management views
- **purchases/**: purchase order management views
- All views use Bootstrap 5 with custom styling

### Important Fields
- **Supplier status**: Uses `status` column (not `is_active`) with values 'active'/'inactive'
- **Item categories**: Categorized inventory system
- **Purchase workflow**: Multi-step purchase order process

### Testing
- Run tests with: `php artisan test`
- Lint with: `composer run lint` (if available)
- Type check with: `php artisan check` (if available)

### Common Issues Fixed
- Supplier model fillable array updated to include `status` field
- Status toggle functionality uses correct database column
- Views properly display active/inactive status badges

### Development Notes
- Uses Laravel Blade templating
- Bootstrap 5 for UI components
- Font Awesome/Bootstrap Icons for iconography
- Responsive design implementation
- AJAX functionality for dynamic interactions

## File Locations
- Models: `app/Models/`
- Controllers: `app/Http/Controllers/`
- Views: `resources/views/`
- Migrations: `database/migrations/`
- Routes: `routes/web.php`