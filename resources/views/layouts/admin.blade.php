<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title> {{ env('BUSINESS_NAME') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            overflow-x: hidden;
        }

        #sidebar {
            position: fixed;
            top: 0;
            left: -250px;
            width: 250px;
            height: 100vh;
            background: #343a40;
            transition: left 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
        }

        #sidebar.show {
            left: 0;
        }

        #sidebar .sidebar-header {
            padding: 20px;
            background: #495057;
            color: white;
            border-bottom: 1px solid #6c757d;
        }

        #sidebar .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            padding-bottom: 20px;
        }

        #sidebar .sidebar-menu li {
            border-bottom: 1px solid #495057;
        }

        #sidebar .sidebar-menu li a {
            display: block;
            padding: 15px 20px;
            color: #adb5bd;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        #sidebar .sidebar-menu li a:hover,
        #sidebar .sidebar-menu li a.active {
            background: #495057;
            color: white;
        }

        #sidebar .sidebar-menu li a i {
            margin-right: 10px;
            width: 20px;
        }

        #sidebar .sidebar-menu .menu-header {
            padding: 15px 20px 8px 20px;
            color: #6c757d;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: none;
        }

        #sidebar .sidebar-menu .menu-header span {
            display: block;
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        .sidebar-overlay.show {
            display: block;
        }

        .top-bar {
            background: #343a40;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .menu-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }

        .user-dropdown {
            position: relative;
        }

        .user-dropdown .dropdown-menu {
            right: 0;
            left: auto;
        }

        .main-content {
            margin-top: 0;
            padding: 20px;
            min-height: calc(100vh - 70px);
        }

        /* Mobile improvements */
        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }

            #sidebar {
                width: 280px;
                left: -280px;
            }

            .sidebar-menu li a {
                padding: 12px 15px;
                font-size: 14px;
            }

            .sidebar-menu li a small {
                font-size: 10px;
                display: block;
                margin-top: 2px;
            }
        }

        /* Better active state styling */
        #sidebar .sidebar-menu li a.active {
            background: linear-gradient(90deg, #495057 0%, #6c757d 100%);
            border-left: 3px solid #007bff;
            color: #fff;
            font-weight: 500;
        }

        /* Improve text visibility */
        .text-warning {
            color: #ffc107 !important;
        }

        /* Loading state for links */
        .sidebar-menu li a:active {
            background: #6c757d;
            transform: translateX(2px);
        }
    </style>
</head>
<body class="bg-light">
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="d-flex align-items-center">
            <button class="menu-toggle" id="menuToggle">
                <i class="bi bi-list"></i>
            </button>
            <span class="ms-3 fw-bold">{{ env('BUSINESS_NAME') }}</span>
        </div>
        <div class="user-dropdown dropdown">
            <a class="text-white text-decoration-none dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle me-2"></i>{{ auth()->check() ? auth()->user()->name : 'Guest' }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </ul>
        </div>
    </div>

    <!-- Sidebar -->
    <div id="sidebar">
        <div class="sidebar-header">
            <h5 class="mb-0">Navigation</h5>
        </div>
        <ul class="sidebar-menu">
            <!-- Dashboard -->
            <li>
                <a href="{{ route('dashboard') }}" class="{{ request()->is('/') || request()->is('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>Dashboard
                </a>
            </li>

            <!-- Items Section -->
            <li class="menu-header">
                <span>Inventory Management</span>
            </li>
            <li>
                <a href="{{ route('items.index') }}" class="{{ request()->is('items') && !request()->is('items/create') && !request()->is('items/status/*') ? 'active' : '' }}">
                    <i class="bi bi-box"></i>All Items
                </a>
            </li>
            <li>
                <a href="{{ route('items.create') }}" class="{{ request()->is('items/create') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle"></i>Add New Item
                </a>
            </li>

            <li>
                <a href="{{ route('items.lowStock') }}" class="{{ request()->is('items/status/low-stock') ? 'active' : '' }}">
                    <i class="bi bi-exclamation-triangle"></i>Low Stock Items
                </a>
            </li>
            <li>
                <a href="{{ route('items.outOfStock') }}" class="{{ request()->is('items/status/out-of-stock') ? 'active' : '' }}">
                    <i class="bi bi-x-circle"></i>Out of Stock
                </a>
            </li>
            <li>
                <a href="{{ route('inventory-adjustments.index') }}" class="{{ request()->is('inventory-adjustments*') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-data"></i>Inventory Adjustments
                </a>
            </li>

            <!-- Purchase Section -->
            <li class="menu-header">
                <span>Purchase Management</span>
            </li>
            <li>
                <a href="{{ route('suppliers.index') }}" class="{{ request()->is('suppliers*') ? 'active' : '' }}">
                    <i class="bi bi-building"></i>Suppliers
                </a>
            </li>
            <li>
                <a href="{{ route('purchases.index') }}" class="{{ request()->is('purchases') && !request()->is('purchases/create') && !request()->is('purchases/*/edit') ? 'active' : '' }}">
                    <i class="bi bi-cart-plus"></i>All Purchases
                </a>
            </li>
            <li>
                <a href="{{ route('purchases.create') }}" class="{{ request()->is('purchases/create') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle"></i>New Purchase Order
                </a>
            </li>

            <!-- Sales Section -->
            <li class="menu-header">
                <span>Sales Management</span>
            </li>
            <li>
                <a href="{{ Route::has('customers.index') ? route('customers.index') : '#' }}" class="{{ request()->is('customers*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>Customers
                    @if(!Route::has('customers.index'))<small class="text-warning ms-1">(Coming Soon)</small>@endif
                </a>
            </li>
            <li>
                <a href="{{ Route::has('sales.index') ? route('sales.index') : '#' }}" class="{{ request()->is('sales*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up"></i>Sales
                    @if(!Route::has('sales.index'))<small class="text-warning ms-1">(Coming Soon)</small>@endif
                </a>
            </li>

            <!-- Financial Management -->
            <li class="menu-header">
                <span>Financial Management</span>
            </li>
            <li>
                <a href="{{ route('income.index') }}" class="{{ request()->is('income*') && !request()->is('income/create') ? 'active' : '' }}">
                    <i class="bi bi-cash-stack"></i>All Income
                </a>
            </li>
            <li>
                <a href="{{ route('income.create') }}" class="{{ request()->is('income/create') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle"></i>Add Income
                </a>
            </li>
            <li>
                <a href="{{ route('expenses.index') }}" class="{{ request()->is('expenses*') && !request()->is('expenses/create') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i>All Expenses
                </a>
            </li>
            <li>
                <a href="{{ route('expenses.create') }}" class="{{ request()->is('expenses/create') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle"></i>Add Expense
                </a>
            </li>
            <li>
                <a href="{{ route('transactions.index') }}" class="{{ request()->is('transactions*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-left-right"></i>All Transactions
                </a>
            </li>

            <!-- Profit/Loss Tracking -->
            <li class="menu-header">
                <span>Profit/Loss Tracking</span>
            </li>
            <li>
                <a href="{{ route('item-sales.index') }}" class="{{ request()->is('item-sales') && !request()->is('item-sales/create') ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow"></i>Item Sales & Profit
                </a>
            </li>
            <li>
                <a href="{{ route('item-sales.create') }}" class="{{ request()->is('item-sales/create') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle"></i>Record Sale
                </a>
            </li>

            <!-- Administration -->
            @if(auth()->check())
            <li class="menu-header">
                <span>Administration</span>
            </li>
            <li>
                <a href="{{ Route::has('users.index') ? route('users.index') : '#' }}" class="{{ request()->is('users*') ? 'active' : '' }}">
                    <i class="bi bi-person-gear"></i>User Management
                    @if(!Route::has('users.index'))<small class="text-warning ms-1">(Coming Soon)</small>@endif
                </a>
            </li>
            @endif

            <!-- Reports & Export -->
            <li class="menu-header">
                <span>Reports & Analytics</span>
            </li>
            <li>
                <a href="{{ route('dashboard') }}#financial-statements" class="{{ request()->is('reports/financial*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i>Financial Report
                </a>
            </li>
            <li>
                <a href="{{ route('dashboard') }}#balance-sheet" class="{{ request()->is('reports/balance-sheet*') ? 'active' : '' }}">
                    <i class="bi bi-pie-chart"></i>Balance Sheet
                </a>
            </li>
            <li>
                <a href="{{ route('dashboard') }}#income-statement" class="{{ request()->is('reports/income*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up"></i>Income Statement
                </a>
            </li>

            <!-- Quick Actions -->
            <li class="menu-header">
                <span>Quick Actions</span>
            </li>
            <li>
                <a href="#" onclick="window.print()" class="">
                    <i class="bi bi-printer"></i>Print Current Page
                </a>
            </li>
            <li>
                <a href="{{ route('dashboard') }}" class="">
                    <i class="bi bi-arrow-clockwise"></i>Refresh Dashboard
                </a>
            </li>
        </ul>
    </div>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <div class="main-content">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        }

        function closeSidebar() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        }

        menuToggle.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', closeSidebar);

        // Close sidebar when clicking on a menu item
        const sidebarLinks = document.querySelectorAll('#sidebar .sidebar-menu a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', closeSidebar);
        });

        // Close sidebar on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSidebar();
            }
        });
    });
</script>
</body>
</html>
