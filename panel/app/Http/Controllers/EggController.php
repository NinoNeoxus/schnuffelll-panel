<?php

namespace App\Http\Controllers;

use App\Models\Egg;
use App\Models\Nest;
use App\Services\EggImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EggController extends Controller
{

    /**
     * Display a listing of eggs.
     */
    public function index()
    {
        $eggs = Egg::with('nest', 'variables')->paginate(20);
        return view('admin.eggs.index', compact('eggs'));
    }

    /**
     * Show the form for creating a new egg.
     */
    public function create()
    {
        $nests = Nest::all();
        return view('admin.eggs.create', compact('nests'));
    }

    /**
     * Store a newly created egg.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nest_id' => 'required|exists:nests,id',
            'name' => 'required|string|max:255',
            'author' => 'required|email',
            'description' => 'nullable|string',
            'startup' => 'required|string',
            'docker_images' => 'required|array',
        ]);

        $validated['uuid'] = Str::uuid();
        $validated['docker_images'] = json_encode($validated['docker_images']);

        $egg = Egg::create($validated);

        return redirect()->route('admin.eggs.index')
            ->with('success', 'Egg created successfully!');
    }

    /**
     * Display the specified egg.
     */
    public function show(Egg $egg)
    {
        $egg->load('nest', 'variables', 'servers');
        return view('admin.eggs.show', compact('egg'));
    }

    /**
     * Show the form for editing the specified egg.
     */
    public function edit(Egg $egg)
    {
        $nests = Nest::all();
        return view('admin.eggs.edit', compact('egg', 'nests'));
    }

    /**
     * Update the specified egg.
     */
    public function update(Request $request, Egg $egg)
    {
        $validated = $request->validate([
            'nest_id' => 'required|exists:nests,id',
            'name' => 'required|string|max:255',
            'author' => 'required|email',
            'description' => 'nullable|string',
            'startup' => 'required|string',
        ]);

        $egg->update($validated);

        return redirect()->route('admin.eggs.index')
            ->with('success', 'Egg updated successfully!');
    }

    /**
     * Remove the specified egg.
     */
    public function destroy(Egg $egg)
    {
        if ($egg->servers()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete egg with active servers.']);
        }

        $egg->delete();

        return redirect()->route('admin.eggs.index')
            ->with('success', 'Egg deleted successfully!');
    }

    /**
     * Import eggs from JSON files.
     */
    public function import(Request $request, EggImportService $importService)
    {
        $directory = base_path('eggs');
        $imported = $importService->importAll($directory);

        return redirect()->route('admin.eggs.index')
            ->with('success', 'Imported ' . count($imported) . ' eggs: ' . implode(', ', $imported));
    }

    /**
     * Export an egg to JSON.
     */
    public function export(Egg $egg)
    {
        $data = [
            'meta' => ['version' => 'SCHNUFFELLL_EGG_V1'],
            'name' => $egg->name,
            'author' => $egg->author,
            'description' => $egg->description,
            'docker_images' => $egg->docker_images,
            'startup' => $egg->startup,
            'config' => [
                'files' => $egg->config_files,
                'startup' => $egg->config_startup,
                'logs' => $egg->config_logs,
                'stop' => $egg->config_stop,
            ],
            'scripts' => [
                'installation' => [
                    'script' => $egg->script_install,
                    'container' => $egg->script_container,
                    'entrypoint' => $egg->script_entry,
                ],
            ],
            'variables' => $egg->variables->map(fn($v) => [
                'name' => $v->name,
                'description' => $v->description,
                'env_variable' => $v->env_variable,
                'default_value' => $v->default_value,
                'user_viewable' => $v->user_viewable,
                'user_editable' => $v->user_editable,
                'rules' => $v->rules,
            ])->toArray(),
        ];

        return response()->json($data, 200, [], JSON_PRETTY_PRINT)
            ->header('Content-Disposition', 'attachment; filename="egg-' . Str::slug($egg->name) . '.json"');
    }
}
