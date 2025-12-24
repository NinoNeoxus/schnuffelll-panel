@extends('layouts.app')

@section('title', 'Edit Node: ' . $node->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-slate-800 shadow-xl rounded-xl border border-slate-700 overflow-hidden p-6">
        <form action="{{ route('admin.nodes.update', $node) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                <div class="sm:col-span-3">
                    <label for="name" class="block text-sm font-medium text-slate-300">Node Name</label>
                    <div class="mt-1">
                        <input type="text" name="name" id="name" value="{{ old('name', $node->name) }}" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label for="location_id" class="block text-sm font-medium text-slate-300">Location</label>
                    <div class="mt-1">
                        <select id="location_id" name="location_id" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" {{ $node->location_id == $location->id ? 'selected' : '' }}>{{ $location->short }} ({{ $location->long }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="sm:col-span-4">
                    <label for="fqdn" class="block text-sm font-medium text-slate-300">FQDN (Fully Qualified Domain Name)</label>
                    <div class="mt-1">
                        <input type="text" name="fqdn" id="fqdn" value="{{ old('fqdn', $node->fqdn) }}" placeholder="node1.example.com" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                    </div>
                    <p class="mt-1 text-xs text-slate-400">The domain name used to connect to the daemon (Wings).</p>
                </div>

                <div class="sm:col-span-2">
                    <label for="scheme" class="block text-sm font-medium text-slate-300">Scheme</label>
                    <div class="mt-1">
                        <select id="scheme" name="scheme" class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                            <option value="https" {{ $node->scheme == 'https' ? 'selected' : '' }}>HTTPS (SSL)</option>
                            <option value="http" {{ $node->scheme == 'http' ? 'selected' : '' }}>HTTP</option>
                        </select>
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label for="memory" class="block text-sm font-medium text-slate-300">Total Memory (MB)</label>
                    <div class="mt-1">
                        <input type="number" name="memory" id="memory" value="{{ old('memory', $node->memory) }}" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label for="disk" class="block text-sm font-medium text-slate-300">Total Disk Space (MB)</label>
                    <div class="mt-1">
                        <input type="number" name="disk" id="disk" value="{{ old('disk', $node->disk) }}" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                    </div>
                </div>
                
                 <div class="sm:col-span-3">
                    <label for="daemon_listen" class="block text-sm font-medium text-slate-300">Daemon Port</label>
                    <div class="mt-1">
                        <input type="number" name="daemon_listen" id="daemon_listen" value="{{ old('daemon_listen', $node->daemon_listen ?? 8080) }}" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                    </div>
                </div>

                 <div class="sm:col-span-3">
                    <label for="daemon_sftp" class="block text-sm font-medium text-slate-300">SFTP Port</label>
                    <div class="mt-1">
                        <input type="number" name="daemon_sftp" id="daemon_sftp" value="{{ old('daemon_sftp', $node->daemon_sftp ?? 2022) }}" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                    </div>
                </div>
                
                <div class="sm:col-span-6">
                    <div class="relative flex items-start">
                        <div class="flex h-6 items-center">
                            <input id="public" name="public" type="checkbox" value="1" {{ $node->public ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-700 bg-slate-900 text-blue-600 focus:ring-blue-600 focus:ring-offset-slate-900">
                        </div>
                        <div class="ml-3 text-sm leading-6">
                            <label for="public" class="font-medium text-white">Public Node</label>
                            <p class="text-slate-400">Allow automatic server allocation to this node.</p>
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-6">
                    <div class="relative flex items-start">
                        <div class="flex h-6 items-center">
                            <input id="maintenance_mode" name="maintenance_mode" type="checkbox" value="1" {{ $node->maintenance_mode ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-700 bg-slate-900 text-amber-600 focus:ring-amber-600 focus:ring-offset-slate-900">
                        </div>
                        <div class="ml-3 text-sm leading-6">
                            <label for="maintenance_mode" class="font-medium text-white">Maintenance Mode</label>
                            <p class="text-slate-400">Prevents new server deployments to this node.</p>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
            <div class="bg-red-500/10 border border-red-500/50 rounded-md p-4">
                <ul class="list-disc list-inside text-sm text-red-400">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="flex justify-between pt-4 border-t border-slate-700 mt-6">
                <a href="{{ route('admin.nodes.show', $node) }}" class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white">Cancel</a>
                <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
