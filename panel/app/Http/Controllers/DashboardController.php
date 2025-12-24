<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\Node;
use App\Models\User;
use App\Models\Allocation;

class DashboardController extends Controller
{
    /**
     * Show the main dashboard with real stats.
     */
    public function index()
    {
        $stats = [
            'servers' => Server::count(),
            'servers_online' => Server::where('status', 'running')->count(),
            'nodes' => Node::count(),
            'users' => User::count(),
            'allocations' => Allocation::count(),
            'allocations_used' => Allocation::whereNotNull('server_id')->count(),
        ];

        // Get recent servers
        $recentServers = Server::with(['node', 'egg'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get nodes with health status
        $nodes = Node::withCount('servers')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'recentServers', 'nodes'));
    }
}
