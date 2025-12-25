@extends('layouts.app')

@section('title', 'Nodes')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <p class="text-sm text-slate-400">Manage all remote nodes.</p>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('admin.nodes.create') }}" class="block rounded-md bg-blue-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                New Node
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($nodes as $node)
        <div class="bg-slate-800 rounded-xl shadow-lg border border-slate-700 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white">{{ $node->name }}</h3>
                    <div class="flex items-center space-x-2">
                        @if($node->public)
                             <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S12 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S12 3 12 3m0 18b1.243 1.243 0 01-.043-1.043M12 3a1.242 1.242 0 01.043 1.043" />
                             </svg>
                        @else
                             <svg class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                             </svg>
                        @endif
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div>
                         <p class="text-xs font-semibold text-slate-500 uppercase">FQDN</p>
                         <p class="text-sm text-slate-300 font-mono">{{ $node->fqdn }}</p>
                    </div>
                    
                    <div>
                         <p class="text-xs font-semibold text-slate-500 uppercase">Location</p>
                         <p class="text-sm text-slate-300">{{ $node->location->short ?? 'None' }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                             <p class="text-xs font-semibold text-slate-500 uppercase">Memory</p>
                             <div class="mt-1 w-full bg-slate-700 rounded-full h-1.5">
                                 <div class="bg-purple-500 h-1.5 rounded-full" style="width: 0%"></div>
                             </div>
                             <p class="text-xs text-slate-400 mt-1">0 / {{ $node->memory }} MB</p>
                        </div>
                        <div>
                             <p class="text-xs font-semibold text-slate-500 uppercase">Disk</p>
                             <div class="mt-1 w-full bg-slate-700 rounded-full h-1.5">
                                 <div class="bg-blue-500 h-1.5 rounded-full" style="width: 0%"></div>
                             </div>
                             <p class="text-xs text-slate-400 mt-1">0 / {{ $node->disk }} MB</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-slate-900/50 px-6 py-3 border-t border-slate-700 flex justify-between items-center">
                <span class="text-xs text-slate-500">Port {{ $node->daemon_listen }}</span>
                <a href="#" class="text-sm font-medium text-blue-400 hover:text-blue-300">Manage &rarr;</a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
