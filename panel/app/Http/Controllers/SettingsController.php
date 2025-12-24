<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Process;

class SettingsController extends Controller
{
    public function index()
    {
        if (!Auth::user()->root_admin) {
            abort(403, 'Unauthorized.');
        }

        return view('admin.settings.index');
    }

    public function update(Request $request)
    {
        if (!Auth::user()->root_admin) {
            abort(403, 'Unauthorized.');
        }

        // Trigger the update script
        // In production, this should ideally be a queued job or run in background
        // For simplicity/MVP, we'll try to execute it directly.
        
        $scriptPath = base_path('update.sh');
        
        if (!file_exists($scriptPath)) {
            return back()->withErrors(['msg' => 'Update script (update.sh) not found in root directory.']);
        }

        try {
            // Run the script
            // Note: This might timeout if the update takes too long.
            // A better approach for real production is to touch a file or dispatch a job.
            exec("bash $scriptPath > /dev/null 2>&1 &");
            
            return back()->with('success', 'Update process has been started in the background! Please wait a few minutes.');
        } catch (\Exception $e) {
            return back()->withErrors(['msg' => 'Failed to start update: ' . $e->getMessage()]);
        }
    }
}
