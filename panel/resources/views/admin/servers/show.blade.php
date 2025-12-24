@extends('layouts.app')

@section('title', $server->name)

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center space-x-3">
                <div class="h-12 w-12 flex-shrink-0 bg-slate-700 rounded-lg flex items-center justify-center text-white font-bold text-lg">
                    {{ substr($server->name, 0, 2) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">{{ $server->name }}</h2>
                    <p class="text-sm text-slate-400 font-mono">{{ $server->uuid }}</p>
                </div>
            </div>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('admin.servers.edit', $server) }}" class="rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-600">
                Edit Server
            </a>
            <form action="{{ route('admin.servers.destroy', $server) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this server?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Status Card -->
        <div class="bg-slate-800 rounded-xl shadow-lg border border-slate-700 p-6">
            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Status</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Current State</span>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium 
                        {{ $server->status == 'running' ? 'bg-emerald-500/10 text-emerald-400' : 
                           ($server->status == 'installing' ? 'bg-blue-500/10 text-blue-400' : 'bg-slate-500/10 text-slate-400') }}">
                        {{ ucfirst($server->status) }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Node</span>
                    <span class="text-white">{{ $server->node->name ?? 'Unknown' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Primary IP</span>
                    <span class="text-white font-mono text-sm">{{ $server->allocation->ip ?? 'N/A' }}:{{ $server->allocation->port ?? '0' }}</span>
                </div>
            </div>
        </div>

        <!-- Resource Card -->
        <div class="bg-slate-800 rounded-xl shadow-lg border border-slate-700 p-6">
            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Resources</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-slate-400">Memory</span>
                        <span class="text-white">{{ $server->memory }} MB</span>
                    </div>
                    <div class="w-full bg-slate-700 rounded-full h-2">
                        <div class="bg-purple-500 h-2 rounded-full" style="width: 0%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-slate-400">Disk</span>
                        <span class="text-white">{{ $server->disk }} MB</span>
                    </div>
                    <div class="w-full bg-slate-700 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: 0%"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">CPU Limit</span>
                    <span class="text-white">{{ $server->cpu }}%</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Swap</span>
                    <span class="text-white">{{ $server->swap }} MB</span>
                </div>
            </div>
        </div>

        <!-- Owner Card -->
        <div class="bg-slate-800 rounded-xl shadow-lg border border-slate-700 p-6">
            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Owner</h3>
            <div class="space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                        {{ substr($server->owner->name ?? 'U', 0, 1) }}
                    </div>
                    <div>
                        <p class="text-white font-medium">{{ $server->owner->name ?? 'Unknown' }}</p>
                        <p class="text-sm text-slate-400">{{ $server->owner->email ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Section -->
    <div class="bg-slate-800 rounded-xl shadow-lg border border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700">
            <h3 class="text-lg font-medium text-white">Startup Configuration</h3>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">Docker Image</label>
                <p class="text-white font-mono text-sm bg-slate-900 rounded-md px-3 py-2">{{ $server->image ?? 'Not configured' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">Startup Command</label>
                <p class="text-white font-mono text-sm bg-slate-900 rounded-md px-3 py-2 break-all">{{ $server->startup ?? 'Not configured' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">Egg</label>
                <p class="text-white">{{ $server->egg->name ?? 'Unknown' }}</p>
            </div>
        </div>
    </div>

    <!-- Allocations Table -->
    @if($server->allocations && $server->allocations->count() > 0)
    <div class="bg-slate-800 rounded-xl shadow-lg border border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700">
            <h3 class="text-lg font-medium text-white">Allocations</h3>
        </div>
        <table class="min-w-full divide-y divide-slate-700">
            <thead class="bg-slate-900/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">IP Address</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Port</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Primary</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @foreach($server->allocations as $allocation)
                <tr>
                    <td class="px-6 py-4 text-sm text-white font-mono">{{ $allocation->ip }}</td>
                    <td class="px-6 py-4 text-sm text-white font-mono">{{ $allocation->port }}</td>
                    <td class="px-6 py-4 text-sm">
                        @if($allocation->id == $server->allocation_id)
                            <span class="inline-flex items-center rounded-full bg-emerald-500/10 px-2 py-1 text-xs font-medium text-emerald-400">Primary</span>
                        @else
                            <span class="text-slate-500">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
