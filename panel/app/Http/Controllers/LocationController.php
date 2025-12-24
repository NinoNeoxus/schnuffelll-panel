<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of locations.
     */
    public function index()
    {
        $locations = Location::withCount('nodes')->orderBy('short', 'asc')->paginate(15);
        return view('admin.locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new location.
     */
    public function create()
    {
        return view('admin.locations.create');
    }

    /**
     * Store a newly created location.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'short' => 'required|string|max:60|unique:locations,short',
            'long' => 'nullable|string|max:191',
        ]);

        Location::create($validated);

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location created successfully!');
    }

    /**
     * Show location details.
     */
    public function show(Location $location)
    {
        $location->load('nodes');
        return view('admin.locations.show', compact('location'));
    }

    /**
     * Update location.
     */
    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'short' => 'required|string|max:60|unique:locations,short,' . $location->id,
            'long' => 'nullable|string|max:191',
        ]);

        $location->update($validated);

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location updated successfully!');
    }

    /**
     * Delete location.
     */
    public function destroy(Location $location)
    {
        if ($location->nodes()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete location with nodes attached!']);
        }

        $location->delete();

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location deleted successfully!');
    }
}
