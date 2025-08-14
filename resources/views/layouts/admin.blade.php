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
                <i class="bi bi-person-circle me-2"></i>{{ auth()->user()->name }}
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
            <li>
                <a href="{{ route('items.index') }}" class="{{ request()->is('items*') ? 'active' : '' }}">
                    <i class="bi bi-box"></i>Items
                </a>
            </li>
            <li>
                <a href="{{ route('customers.index') }}" class="{{ request()->is('customers*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>Customers
                </a>
            </li>
            <li>
                <a href="{{ route('sales.index') }}" class="{{ request()->is('sales*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up"></i>Sales
                </a>
            </li>
            @if(auth()->user())
            <li>
                <a href="{{ route('users.index') }}" class="{{ request()->is('users*') ? 'active' : '' }}">
                    <i class="bi bi-person-gear"></i>Users
                </a>
            </li>
            @endif
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
