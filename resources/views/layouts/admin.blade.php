<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Solar Sales Admin</title>
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
    </style>
</head>
<body class="bg-light">
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="d-flex align-items-center">
            <button class="menu-toggle" id="menuToggle">
                <i class="bi bi-list"></i>
            </button>
            <span class="ms-3 fw-bold">Solar Sales</span>
        </div>
        <div class="user-dropdown dropdown">
            <a class="text-white text-decoration-none dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle me-2"></i>{{ auth()->check() ? auth()->user()->name : 'Guest' }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
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
                <a href="{{ route('items.index') }}" class="{{ request()->is('/') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>Dashboard
                </a>
            </li>

            <!-- Items Section -->
            <li class="menu-header">
                <span>Inventory Management</span>
            </li>
            <li>
                <a href="{{ route('items.index') }}" class="{{ request()->is('items') && !request()->is('items/create') && !request()->is('items/pricing') ? 'active' : '' }}">
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
                <a href="{{ route('purchases.index') }}" class="{{ request()->is('purchases') && !request()->is('purchases/create') ? 'active' : '' }}">
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
                <a href="#" class="">
                    <i class="bi bi-people"></i>Customers
                </a>
            </li>
            <li>
                <a href="#" class="">
                    <i class="bi bi-graph-up"></i>Sales
                </a>
            </li>

            <!-- Administration -->
            @if(auth()->check())
            <li class="menu-header">
                <span>Administration</span>
            </li>
            <li>
                <a href="#" class="">
                    <i class="bi bi-person-gear"></i>User Management
                </a>
            </li>
            @endif

            <!-- Reports & Export -->
            <li class="menu-header">
                <span>Reports</span>
            </li>
            <li>
                <a href="#" class="">
                    <i class="bi bi-download"></i>Export Items
                </a>
            </li>
            <li>
                <a href="#" class="">
                    <i class="bi bi-download"></i>Export Customers
                </a>
            </li>
            <li>
                <a href="#" class="">
                    <i class="bi bi-download"></i>Export Sales
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
