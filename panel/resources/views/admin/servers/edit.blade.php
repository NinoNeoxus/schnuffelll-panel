@extends('layouts.app')

@section('title', 'Edit Server: ' . $server->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-slate-800 shadow-xl rounded-xl border border-slate-700 overflow-hidden p-6">
        <form action="{{ route('admin.servers.update', $server) }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')
            
            <!-- Core Details -->
            <div>
                <h3 class="text-lg font-medium leading-6 text-white">Core Details</h3>
                <p class="mt-1 text-sm text-slate-400">Server identification and ownership.</p>
                
                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="name" class="block text-sm font-medium text-slate-300">Server Name</label>
                        <div class="mt-1">
                            <input type="text" name="name" id="name" value="{{ old('name', $server->name) }}" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="owner_id" class="block text-sm font-medium text-slate-300">Server Owner</label>
                        <div class="mt-1">
                            <select id="owner_id" name="owner_id" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                                @foreach(\App\Models\User::all() as $user)
                                    <option value="{{ $user->id }}" {{ $server->owner_id == $user->id ? 'selected' : '' }}>{{ $user->email }} ({{ $user->name }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="sm:col-span-6">
                        <label class="block text-sm font-medium text-slate-300">UUID</label>
                        <p class="mt-1 text-sm text-slate-400 font-mono bg-slate-900 rounded-md px-3 py-2">{{ $server->uuid }}</p>
                    </div>
                </div>
            </div>

            <!-- Resource Limits -->
            <div class="border-t border-slate-700 pt-8">
                <h3 class="text-lg font-medium leading-6 text-white">Resource Limits</h3>
                <p class="mt-1 text-sm text-slate-400">Hard limits for the container.</p>
                
                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-4">
                     <div class="sm:col-span-1">
                        <label for="memory" class="block text-sm font-medium text-slate-300">Memory (MB)</label>
                        <div class="mt-1">
                            <input type="number" name="memory" id="memory" value="{{ old('memory', $server->memory) }}" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-1">
                        <label for="swap" class="block text-sm font-medium text-slate-300">Swap (MB)</label>
                        <div class="mt-1">
                            <input type="number" name="swap" id="swap" value="{{ old('swap', $server->swap) }}" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-1">
                        <label for="disk" class="block text-sm font-medium text-slate-300">Disk Space (MB)</label>
                        <div class="mt-1">
                            <input type="number" name="disk" id="disk" value="{{ old('disk', $server->disk) }}" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-1">
                        <label for="cpu" class="block text-sm font-medium text-slate-300">CPU Limit (%)</label>
                        <div class="mt-1">
                            <input type="number" name="cpu" id="cpu" value="{{ old('cpu', $server->cpu) }}" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Section -->
            <div class="border-t border-slate-700 pt-8">
                <h3 class="text-lg font-medium leading-6 text-white">Server Info</h3>
                <p class="mt-1 text-sm text-slate-400">Read-only server information.</p>
                
                <div class="mt-6 grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-400">Node</label>
                        <p class="text-white">{{ $server->node->name ?? 'Unknown' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400">Egg</label>
                        <p class="text-white">{{ $server->egg->name ?? 'Unknown' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400">Status</label>
                        <p class="text-white">{{ ucfirst($server->status) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400">Created</label>
                        <p class="text-white">{{ $server->created_at->format('M d, Y H:i') }}</p>
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
                <a href="{{ route('admin.servers.show', $server) }}" class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white">Cancel</a>
                <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
