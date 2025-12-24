<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\Node;
use App\Models\Egg;
use App\Models\Allocation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ServerController extends Controller
{
    public function index()
    {
        $servers = Server::with(['node', 'egg'])->paginate(15);
        return view('admin.servers.index', compact('servers'));
    }

    public function create()
    {
        $nodes = Node::all();
        $eggs = Egg::all();
        return view('admin.servers.create', compact('nodes', 'eggs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'owner_id' => 'required|exists:users,id',
            'node_id' => 'required|exists:nodes,id',
            'egg_id' => 'required|exists:eggs,id',
            'memory' => 'required|integer',
            'swap' => 'required|integer',
            'disk' => 'required|integer',
            'cpu' => 'required|integer',
        ]);

        DB::beginTransaction();
        
        try {
            // 1. Find a free allocation (Port) on the selected Node
            $allocation = Allocation::where('node_id', $validated['node_id'])
                ->whereNull('server_id')
                ->first();

            if (!$allocation) {
                return back()->withErrors(['node_id' => 'No allocations (ports) available on this node.']);
            }

            // 2. Prepare Data
            $uuid = Str::uuid()->toString();
            $uuidShort = Str::substr($uuid, 0, 8);
            
            // Get Egg defaults if needed
            $egg = Egg::findOrFail($validated['egg_id']);
            
            // 3. Create Server Record
            $server = Server::create([
                'uuid' => $uuid,
                'uuidShort' => $uuidShort,
                'name' => $validated['name'],
                'owner_id' => $validated['owner_id'],
                'node_id' => $validated['node_id'],
                'egg_id' => $validated['egg_id'],
                'allocation_id' => $allocation->id,
                'memory' => $validated['memory'],
                'swap' => $validated['swap'],
                'disk' => $validated['disk'],
                'cpu' => $validated['cpu'],
                'status' => 'installing',
                'startup' => $egg->startup_command,
                'image' => $egg->docker_image,
            ]);

            // 4. Assign Allocation to Server
            $allocation->server_id = $server->id;
            $allocation->save();

            DB::commit();

            // 5. Notify Daemon (Wings)
            // In a real app, this should be a queued job
            $this->notifyWings($server);

            return redirect()->route('admin.servers.index')
                ->with('success', 'Server created successfully! Installer running...');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create server: ' . $e->getMessage()]);
        }
    }

    protected function notifyWings(Server $server)
    {
        $node = $server->node;
        // Basic implementation of calling the daemon
        // Scheme: https://fqdn:8080/api/servers
        $url = sprintf(
            '%s://%s:%d/api/servers',
            $node->scheme,
            $node->fqdn,
            $node->daemon_listen
        );

        try {
            Http::withToken($node->daemon_token)
                ->post($url, [
                    'uuid' => $server->uuid,
                    'settings' => [
                        'build' => [
                            'env' => [], // Environment variables
                            'memory' => $server->memory,
                            'swap' => $server->swap,
                            'io' => $server->io,
                            'cpu' => $server->cpu,
                            'disk' => $server->disk,
                            'image' => $server->image
                        ],
                        'service' => [
                            'startup' => $server->startup,
                        ]
                    ]
                ]);
        } catch (\Exception $e) {
            // Log error but don't fail the request user-side
            logger()->error('Failed to notify Wings: ' . $e->getMessage());
        }
    }
}
