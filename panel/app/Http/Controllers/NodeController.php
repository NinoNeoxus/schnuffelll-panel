<?php

namespace App\Http\Controllers;

use App\Models\Node;
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
        // In a real app we'd paginate
        $nodes = Node::with('location')->get(); 
        
        return view('admin.nodes.index', compact('nodes'));
    }

    /**
     * Show the form for creating a new node.
     */
    public function create()
    {
        // We'd need locations list here
        // $locations = Location::all();
        // For now returning view placeholder
        return view('admin.nodes.create');
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
            'ssl' => $request->secure() ? 'secure' : 'insecure' // Check if request came via HTTPS
        ]);
    }
}
