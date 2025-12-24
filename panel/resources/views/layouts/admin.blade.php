<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Schnuffelll Panel</title>
    
    <!-- Bootstrap 5.3 Dark Theme -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --schnuff-primary: #6366f1;
            --schnuff-secondary: #8b5cf6;
            --schnuff-accent: #22d3ee;
            --schnuff-bg: #0f172a;
            --schnuff-surface: #1e293b;
            --schnuff-surface-light: #334155;
            --schnuff-text: #f1f5f9;
            --schnuff-text-muted: #94a3b8;
        }
        
        * {
            font-family: 'Inter', -apple-system, sans-serif;
        }
        
        body {
            background: var(--schnuff-bg);
            color: var(--schnuff-text);
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            background: linear-gradient(180deg, var(--schnuff-surface) 0%, rgba(30,41,59,0.8) 100%);
            border-right: 1px solid rgba(255,255,255,0.05);
            padding: 1.5rem 0;
            backdrop-filter: blur(10px);
            z-index: 1000;
        }
        
        .sidebar-brand {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            margin-bottom: 1rem;
        }
        
        .sidebar-brand h4 {
            background: linear-gradient(135deg, var(--schnuff-primary), var(--schnuff-accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
            margin: 0;
        }
        
        .sidebar-nav {
            padding: 0 0.75rem;
        }
        
        .nav-item {
            margin-bottom: 4px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            color: var(--schnuff-text-muted);
            transition: all 0.2s ease;
        }
        
        .nav-link:hover {
            background: rgba(99,102,241,0.1);
            color: var(--schnuff-text);
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, var(--schnuff-primary), var(--schnuff-secondary));
            color: white;
            box-shadow: 0 4px 12px rgba(99,102,241,0.3);
        }
        
        .nav-link i {
            width: 24px;
            margin-right: 12px;
            font-size: 1.1rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 2rem;
            min-height: 100vh;
        }
        
        /* Cards */
        .card {
            background: var(--schnuff-surface);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-weight: 600;
        }
        
        /* Stat Cards */
        .stat-card {
            background: linear-gradient(135deg, var(--schnuff-surface), var(--schnuff-surface-light));
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 1.5rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
        }
        
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }
        
        .stat-card .stat-label {
            color: var(--schnuff-text-muted);
            font-size: 0.875rem;
        }
        
        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--schnuff-primary), var(--schnuff-secondary));
            border: none;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99,102,241,0.4);
        }
        
        /* Tables */
        .table {
            color: var(--schnuff-text);
        }
        
        .table thead th {
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        
        .table tbody td {
            border-bottom: 1px solid rgba(255,255,255,0.03);
            vertical-align: middle;
        }
        
        .table-hover tbody tr:hover {
            background: rgba(99,102,241,0.05);
        }
        
        /* Badges */
        .badge {
            font-weight: 500;
            padding: 0.5em 0.75em;
            border-radius: 6px;
        }
        
        /* Form Controls */
        .form-control, .form-select {
            background: var(--schnuff-surface-light);
            border: 1px solid rgba(255,255,255,0.1);
            color: var(--schnuff-text);
        }
        
        .form-control:focus, .form-select:focus {
            background: var(--schnuff-surface-light);
            border-color: var(--schnuff-primary);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.2);
            color: var(--schnuff-text);
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--schnuff-bg);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--schnuff-surface-light);
            border-radius: 4px;
        }
        
        /* Responsive */
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h4>🚀 Schnuffelll</h4>
            <small class="text-muted">Game Server Panel</small>
        </div>
        
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.servers.index') }}" class="nav-link {{ request()->routeIs('admin.servers.*') ? 'active' : '' }}">
                        <i class="fas fa-server"></i> Servers
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.nodes.index') }}" class="nav-link {{ request()->routeIs('admin.nodes.*') ? 'active' : '' }}">
                        <i class="fas fa-network-wired"></i> Nodes
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.eggs.index') }}" class="nav-link {{ request()->routeIs('admin.eggs.*') ? 'active' : '' }}">
                        <i class="fas fa-egg"></i> Eggs
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.backups.index') }}" class="nav-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}">
                        <i class="fas fa-archive"></i> Backups
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.locations.index') }}" class="nav-link {{ request()->routeIs('admin.locations.*') ? 'active' : '' }}">
                        <i class="fas fa-globe"></i> Locations
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="mt-auto px-3">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100 mt-4">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <p class="mb-0">{{ $error }}</p>
                @endforeach
            </div>
        @endif
        
        @yield('content')
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
