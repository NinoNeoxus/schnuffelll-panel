<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Schnuffelll') }} - Enterprise Game Panel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        slate: {
                            850: '#1e293b', // Custom lighter slate for cards
                            900: '#0f172a', // Main BG
                        },
                        blue: {
                            500: '#3b82f6', // Primary
                            600: '#2563eb', // Primary Hover
                        },
                        emerald: {
                            500: '#10b981', // Success
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0f172a; 
        }
        ::-webkit-scrollbar-thumb {
            background: #334155; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #475569; 
        }
    </style>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full font-sans antialiased text-slate-300 selection:bg-blue-500 selection:text-white">
    <div class="min-h-full flex flex-col">
        <!-- Top Navigation -->
        <nav class="bg-slate-900 border-b border-slate-800" x-data="{ open: false }">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <!-- Logo placeholder -->
                            <a href="{{ route('dashboard') }}">
                                <h1 class="text-2xl font-bold tracking-tight text-white">
                                    <span class="text-blue-500">Schnuffe</span>lll
                                </h1>
                            </a>
                        </div>
                        <div class="hidden md:block">
                            <div class="ml-10 flex items-baseline space-x-4">
                                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                                
                                <a href="{{ route('admin.servers.index') }}" class="{{ request()->routeIs('admin.servers.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium">Servers</a>
                                
                                <a href="{{ route('admin.nodes.index') }}" class="{{ request()->routeIs('admin.nodes.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium">Nodes</a>
                                
                                <a href="{{ route('admin.locations.index') }}" class="{{ request()->routeIs('admin.locations.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium">Locations</a>

                                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium">Users</a>
                                
                                <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium">Settings</a>
                            </div>
                        </div>
                    </div>
                    <!-- User Menu -->
                    <div class="hidden md:block">
                        <div class="ml-4 flex items-center md:ml-6 space-x-4">
                             <div class="text-sm text-slate-400">{{ Auth::user()->name ?? 'Guest' }}</div>
                             <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-slate-400 hover:text-white text-sm font-medium transition-colors">Logout</button>
                             </form>
                             <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold cursor-default select-none">
                                 {{ substr(Auth::user()->name ?? 'G', 0, 1) }}
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="flex-1 py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                @hasSection('title')
                    <header class="mb-8">
                        <h1 class="text-3xl font-bold leading-tight tracking-tight text-white">@yield('title')</h1>
                    </header>
                @endif

                @yield('content')
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="bg-slate-900 border-t border-slate-800 py-6">
            <div class="text-center text-xs text-slate-500">
                &copy; {{ date('Y') }} Schnuffelll Panel. "Pterodactyl, but German Engineering".
            </div>
        </footer>
    </div>
</body>
</html>
