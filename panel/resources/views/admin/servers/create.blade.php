@extends('layouts.app')

@section('title', 'Create Server')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-slate-800 shadow-xl rounded-xl border border-slate-700 overflow-hidden p-6">
        <form action="{{ route('admin.servers.store') }}" method="POST" class="space-y-8">
            @csrf
            
            <!-- Core Details -->
            <div>
                <h3 class="text-lg font-medium leading-6 text-white">Core Details</h3>
                <p class="mt-1 text-sm text-slate-400">Basic configuration for the new server.</p>
                
                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="name" class="block text-sm font-medium text-slate-300">Server Name</label>
                        <div class="mt-1">
                            <input type="text" name="name" id="name" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="owner_id" class="block text-sm font-medium text-slate-300">Server Owner</label>
                        <div class="mt-1">
                            <select id="owner_id" name="owner_id" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                                <option value="" disabled selected>Select User</option>
                                @foreach(\App\Models\User::all() as $user)
                                    <option value="{{ $user->id }}">{{ $user->email }} ({{ $user->name }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-slate-700 pt-8">
                <h3 class="text-lg font-medium leading-6 text-white">Allocation Management</h3>
                <p class="mt-1 text-sm text-slate-400">Select where this server will be deployed.</p>

                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                     <div class="sm:col-span-3">
                        <label for="node_id" class="block text-sm font-medium text-slate-300">Target Node</label>
                        <div class="mt-1">
                            <select id="node_id" name="node_id" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                                <option value="" disabled selected>Select Node</option>
                                @foreach(\App\Models\Node::all() as $node)
                                    <option value="{{ $node->id }}">{{ $node->name }} ({{ $node->location->short ?? 'No Loc' }})</option>
                                @endforeach
                            </select>
                        </div>
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
                            <input type="number" name="memory" id="memory" value="1024" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-1">
                        <label for="swap" class="block text-sm font-medium text-slate-300">Swap (MB)</label>
                        <div class="mt-1">
                            <input type="number" name="swap" id="swap" value="0" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-1">
                        <label for="disk" class="block text-sm font-medium text-slate-300">Disk Space (MB)</label>
                        <div class="mt-1">
                            <input type="number" name="disk" id="disk" value="5120" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-1">
                        <label for="cpu" class="block text-sm font-medium text-slate-300">CPU Limit (%)</label>
                        <div class="mt-1">
                            <input type="number" name="cpu" id="cpu" value="100" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Configuration -->
            <div class="border-t border-slate-700 pt-8">
                <h3 class="text-lg font-medium leading-6 text-white">Startup Configuration</h3>
                <p class="mt-1 text-sm text-slate-400">Select the game or service to run.</p>
                
                 <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                     <div class="sm:col-span-3">
                        <label for="egg_id" class="block text-sm font-medium text-slate-300">Nest Egg</label>
                        <div class="mt-1">
                            <select id="egg_id" name="egg_id" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                                <option value="" disabled selected>Select Egg</option>
                                @foreach(\App\Models\Egg::all() as $egg)
                                    <option value="{{ $egg->id }}">{{ $egg->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-700 mt-6">
                <a href="{{ route('admin.servers.index') }}" class="mr-3 px-4 py-2 text-sm font-medium text-slate-300 hover:text-white">Cancel</a>
                <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                    Deploy Server
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
