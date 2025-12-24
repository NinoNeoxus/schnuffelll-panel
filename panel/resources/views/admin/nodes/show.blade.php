@extends('layouts.app')

@section('title', $node->name)

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center space-x-3">
                <div class="h-12 w-12 flex-shrink-0 bg-slate-700 rounded-lg flex items-center justify-center">
                    <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.737 5.1a3.375 3.375 0 012.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 01.9 2.7m0 0a3 3 0 01-3 3m0 3h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008zm-3 6h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">{{ $node->name }}</h2>
                    <p class="text-sm text-slate-400 font-mono">{{ $node->fqdn }}</p>
                </div>
            </div>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('admin.nodes.edit', $node) }}" class="rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-600">
                Edit Node
            </a>
            <form action="{{ route('admin.nodes.destroy', $node) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this node? All servers must be migrated first.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Connection Info -->
        <div class="bg-slate-800 rounded-xl shadow-lg border border-slate-700 p-6">
            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Connection</h3>
            <div class="space-y-4">
                <div>
                    <p class="text-xs text-slate-500">FQDN</p>
                    <p class="text-white font-mono">{{ $node->fqdn }}</p>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Scheme</span>
                    <span class="text-white uppercase">{{ $node->scheme }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Daemon Port</span>
                    <span class="text-white font-mono">{{ $node->daemon_listen ?? 8080 }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">SFTP Port</span>
                    <span class="text-white font-mono">{{ $node->daemon_sftp ?? 2022 }}</span>
                </div>
            </div>
        </div>

        <!-- Resources -->
        <div class="bg-slate-800 rounded-xl shadow-lg border border-slate-700 p-6">
            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Resources</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-slate-400">Memory</span>
                        <span class="text-white">0 / {{ $node->memory }} MB</span>
                    </div>
                    <div class="w-full bg-slate-700 rounded-full h-2">
                        <div class="bg-purple-500 h-2 rounded-full" style="width: 0%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-slate-400">Disk</span>
                        <span class="text-white">0 / {{ $node->disk }} MB</span>
                    </div>
                    <div class="w-full bg-slate-700 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: 0%"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Active Servers</span>
                    <span class="text-white">{{ $node->servers->count() }}</span>
                </div>
            </div>
        </div>

        <!-- Status -->
        <div class="bg-slate-800 rounded-xl shadow-lg border border-slate-700 p-6">
            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Status</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Public</span>
                    @if($node->public)
                        <span class="inline-flex items-center rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-medium text-emerald-400">Yes</span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-amber-500/10 px-2.5 py-0.5 text-xs font-medium text-amber-400">No</span>
                    @endif
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Maintenance</span>
                    @if($node->maintenance_mode)
                        <span class="inline-flex items-center rounded-full bg-red-500/10 px-2.5 py-0.5 text-xs font-medium text-red-400">Enabled</span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-slate-500/10 px-2.5 py-0.5 text-xs font-medium text-slate-400">Disabled</span>
                    @endif
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Location</span>
                    <span class="text-white">{{ $node->location->short ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Token -->
    <div class="bg-slate-800 rounded-xl shadow-lg border border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="text-lg font-medium text-white">Configuration</h3>
            <div class="flex space-x-2">
                <a href="{{ route('admin.nodes.configuration.yaml', $node) }}" target="_blank" 
                   class="rounded-md bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                    Download config.yml
                </a>
                <form action="{{ route('admin.nodes.reset-token', $node) }}" method="POST" 
                      onsubmit="return confirm('Are you sure? This will invalidate the current token.');">
                    @csrf
                    <button type="submit" class="rounded-md bg-slate-700 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-600">
                        Reset Token
                    </button>
                </form>
            </div>
        </div>
        <div class="p-6 space-y-6">
            <!-- Auto-deploy Command -->
            <div>
                <h4 class="text-sm font-semibold text-slate-400 mb-2">Auto-Deploy Command</h4>
                <p class="text-xs text-slate-500 mb-2">Run this command on your node to automatically configure Wings:</p>
                <div class="bg-slate-900 rounded-md p-4 font-mono text-sm text-green-400 overflow-x-auto">
                    <code>cd /etc/pterodactyl && sudo wings configure --panel-url {{ url('/') }} --token {{ $node->daemon_token }} --node {{ $node->id }}</code>
                </div>
            </div>

            <!-- Or Manual Config -->
            <div>
                <h4 class="text-sm font-semibold text-slate-400 mb-2">Manual Configuration (config.yml)</h4>
                <p class="text-xs text-slate-500 mb-2">Paste this into <code class="text-blue-400">/etc/pterodactyl/config.yml</code>:</p>
                <div class="bg-slate-900 rounded-md p-4 font-mono text-xs text-slate-300 overflow-x-auto max-h-96">
                    <pre>debug: false
uuid: {{ $node->uuid ?? 'GENERATE_UUID' }}
token_id: {{ $node->daemon_token_id }}
token: {{ $node->daemon_token }}

api:
  host: 0.0.0.0
  port: {{ $node->daemon_listen ?? 8080 }}
  ssl:
    enabled: {{ ($node->scheme === 'https' && !$node->behind_proxy) ? 'true' : 'false' }}
    cert: /etc/letsencrypt/live/{{ strtolower($node->fqdn) }}/fullchain.pem
    key: /etc/letsencrypt/live/{{ strtolower($node->fqdn) }}/privkey.pem
  upload_limit: 100

system:
  data: /var/lib/pterodactyl/volumes
  sftp:
    bind_port: {{ $node->daemon_sftp ?? 2022 }}

allowed_mounts: []

remote: {{ url('/') }}</pre>
                </div>
            </div>

            <!-- Connection Details Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-slate-700">
                <div>
                    <p class="text-xs text-slate-500">Node UUID</p>
                    <p class="text-white font-mono text-sm truncate">{{ $node->uuid ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Token ID</p>
                    <p class="text-white font-mono text-sm">{{ $node->daemon_token_id }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Daemon Port</p>
                    <p class="text-white font-mono text-sm">{{ $node->daemon_listen ?? 8080 }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">SFTP Port</p>
                    <p class="text-white font-mono text-sm">{{ $node->daemon_sftp ?? 2022 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Allocations -->
    @if($node->allocations && $node->allocations->count() > 0)
    <div class="bg-slate-800 rounded-xl shadow-lg border border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="text-lg font-medium text-white">Allocations</h3>
            <span class="text-sm text-slate-400">{{ $node->allocations->count() }} total</span>
        </div>
        <table class="min-w-full divide-y divide-slate-700">
            <thead class="bg-slate-900/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">IP Address</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Port</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Assigned To</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @foreach($node->allocations->take(20) as $allocation)
                <tr>
                    <td class="px-6 py-4 text-sm text-white font-mono">{{ $allocation->ip }}</td>
                    <td class="px-6 py-4 text-sm text-white font-mono">{{ $allocation->port }}</td>
                    <td class="px-6 py-4 text-sm">
                        @if($allocation->server_id)
                            <span class="text-blue-400">Server #{{ $allocation->server_id }}</span>
                        @else
                            <span class="text-slate-500">Unassigned</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($node->allocations->count() > 20)
        <div class="px-6 py-3 border-t border-slate-700 text-center text-sm text-slate-400">
            Showing 20 of {{ $node->allocations->count() }} allocations
        </div>
        @endif
    </div>
    @endif

    <!-- Servers on this Node -->
    @if($node->servers && $node->servers->count() > 0)
    <div class="bg-slate-800 rounded-xl shadow-lg border border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700">
            <h3 class="text-lg font-medium text-white">Servers ({{ $node->servers->count() }})</h3>
        </div>
        <table class="min-w-full divide-y divide-slate-700">
            <thead class="bg-slate-900/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Memory</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @foreach($node->servers as $server)
                <tr>
                    <td class="px-6 py-4 text-sm text-white">{{ $server->name }}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium 
                            {{ $server->status == 'running' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-500/10 text-slate-400' }}">
                            {{ ucfirst($server->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-300">{{ $server->memory }} MB</td>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('admin.servers.show', $server) }}" class="text-blue-400 hover:text-blue-300">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
