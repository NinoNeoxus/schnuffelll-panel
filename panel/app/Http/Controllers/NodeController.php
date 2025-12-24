<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NodeController extends Controller
{
    /**
     * Display a listing of the nodes.
     */
    public function index()
    {
        // Fetch nodes with location relationship
        $nodes = Node::with('location')->get(); 
        
        return view('admin.nodes.index', compact('nodes'));
    }

    /**
     * Show the form for creating a new node.
     */
    public function create()
    {
        $locations = Location::all();
        return view('admin.nodes.create', compact('locations'));
    }

    /**
     * Store a newly created node in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'fqdn' => 'required|string',
            'scheme' => 'required|in:http,https',
            'memory' => 'required|integer',
            'disk' => 'required|integer',
        ]);

        // Generate Token automatically
        $validated['daemon_token'] = Str::random(64);
        $validated['daemon_token_id'] = Str::random(16);
        
        $node = Node::create($validated);

        return redirect()->route('admin.nodes.index')
            ->with('success', 'Node created successfully! Token: ' . $validated['daemon_token']);
    }

    /**
     * Display the specified node.
     */
    public function show(Node $node)
    {
        $node->load(['location', 'servers', 'allocations']);
        return view('admin.nodes.show', compact('node'));
    }

    /**
     * Show the form for editing the specified node.
     */
    public function edit(Node $node)
    {
        $locations = Location::all();
        return view('admin.nodes.edit', compact('node', 'locations'));
    }

    /**
     * Update the specified node in storage.
     */
    public function update(Request $request, Node $node)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'fqdn' => 'required|string',
            'scheme' => 'required|in:http,https',
            'memory' => 'required|integer',
            'disk' => 'required|integer',
            'daemon_listen' => 'nullable|integer',
            'daemon_sftp' => 'nullable|integer',
            'maintenance_mode' => 'boolean',
        ]);

        $node->update($validated);

        return redirect()->route('admin.nodes.index')
            ->with('success', 'Node updated successfully!');
    }

    /**
     * Remove the specified node from storage.
     */
    public function destroy(Node $node)
    {
        // Check if node has servers
        if ($node->servers()->count() > 0) {
            return redirect()->route('admin.nodes.index')
                ->with('error', 'Cannot delete node with active servers. Delete or migrate servers first.');
        }

        $node->delete();

        return redirect()->route('admin.nodes.index')
            ->with('success', 'Node deleted successfully!');
    }

    /**
     * The Auto-Green Diagnostic API Key
     * Called by the Daemon to prove it is alive.
     */
    public function diagnose(Request $request, $token)
    {
        $node = Node::where('daemon_token', $token)->first();

        if (!$node) {
            return response()->json(['error' => 'Invalid Token'], 401);
        }

        // Check if connection is working (simple ping)
        // In reality, we might check headers or SSL validity
        
        return response()->json([
            'status' => 'success',
            'message' => 'Node Connected Successfully',
            'node_id' => $node->id,
            'name' => $node->name,
            'ssl' => $request->secure() ? 'secure' : 'insecure'
        ]);
    }
}
