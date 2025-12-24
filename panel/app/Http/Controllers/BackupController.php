<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BackupController extends Controller
{
    /**
     * Display a listing of backups.
     */
    public function index(Request $request)
    {
        $serverId = $request->query('server_id');
        
        $query = Backup::with('server');
        
        if ($serverId) {
            $query->where('server_id', $serverId);
        }
        
        $backups = $query->orderBy('created_at', 'desc')->paginate(20);
        $servers = Server::all();
        
        return view('admin.backups.index', compact('backups', 'servers', 'serverId'));
    }

    /**
     * Store a newly created backup.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|exists:servers,id',
            'name' => 'nullable|string|max:255',
        ]);

        $server = Server::with('node')->findOrFail($validated['server_id']);
        
        $backup = Backup::create([
            'server_id' => $server->id,
            'uuid' => Str::uuid(),
            'name' => $validated['name'] ?? 'Backup ' . now()->format('Y-m-d H:i'),
            'disk' => 'local',
            'size' => 0,
            'is_successful' => false,
        ]);

        // Notify Wings to create backup
        try {
            $url = sprintf(
                '%s://%s:%d/api/servers/%s/backup',
                $server->node->scheme,
                $server->node->fqdn,
                $server->node->daemon_listen,
                $server->uuid
            );

            $response = Http::withToken($server->node->daemon_token)
                ->timeout(300)
                ->post($url, [
                    'backup_uuid' => $backup->uuid,
                ]);

            if ($response->successful()) {
                $backup->update([
                    'is_successful' => true,
                    'size' => $response->json('size', 0),
                    'checksum' => $response->json('checksum'),
                    'completed_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            logger()->error('Backup failed: ' . $e->getMessage());
            $backup->update(['is_successful' => false]);
        }

        return redirect()->route('admin.backups.index', ['server_id' => $server->id])
            ->with('success', 'Backup initiated!');
    }

    /**
     * Remove the specified backup.
     */
    public function destroy(Backup $backup)
    {
        // Delete file if exists
        if (file_exists($backup->path)) {
            unlink($backup->path);
        }

        $backup->delete();

        return redirect()->route('admin.backups.index')
            ->with('success', 'Backup deleted!');
    }

    /**
     * Restore a backup.
     */
    public function restore(Backup $backup)
    {
        $server = $backup->server()->with('node')->first();

        try {
            $url = sprintf(
                '%s://%s:%d/api/servers/%s/backup/%s/restore',
                $server->node->scheme,
                $server->node->fqdn,
                $server->node->daemon_listen,
                $server->uuid,
                $backup->uuid
            );

            Http::withToken($server->node->daemon_token)
                ->timeout(600)
                ->post($url);

            return redirect()->route('admin.backups.index', ['server_id' => $server->id])
                ->with('success', 'Restore initiated!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Restore failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Download a backup file.
     */
    public function download(Backup $backup)
    {
        if (!file_exists($backup->path)) {
            return back()->withErrors(['error' => 'Backup file not found.']);
        }

        return response()->download($backup->path, $backup->name . '.tar.gz');
    }
}
