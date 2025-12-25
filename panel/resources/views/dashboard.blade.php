@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Servers -->
        <div class="overflow-hidden rounded-xl bg-slate-800 p-6 shadow-xl border border-slate-700">
            <div class="flex items-center">
                <div class="flex-shrink-0 rounded-lg bg-blue-500/10 p-3">
                    <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 17.25v-.375a17.25 17.25 0 01-3.468 5.688 6 12 12 0 11-5.688-3.468 17.25 17.25 0 015.688 3.468zm0 0V21m0-18v3.375" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-sm font-medium text-slate-400">Total Servers</dt>
                        <dd class="mt-1 text-2xl font-bold tracking-tight text-white">{{ $stats['servers'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Online Servers -->
        <div class="overflow-hidden rounded-xl bg-slate-800 p-6 shadow-xl border border-slate-700">
            <div class="flex items-center">
                <div class="flex-shrink-0 rounded-lg bg-emerald-500/10 p-3">
                    <svg class="h-6 w-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-sm font-medium text-slate-400">Servers Online</dt>
                        <dd class="mt-1 text-2xl font-bold tracking-tight text-white">{{ $stats['servers_online'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <!-- Total Nodes -->
        <div class="overflow-hidden rounded-xl bg-slate-800 p-6 shadow-xl border border-slate-700">
             <div class="flex items-center">
                <div class="flex-shrink-0 rounded-lg bg-purple-500/10 p-3">
                    <svg class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-sm font-medium text-slate-400">Total Nodes</dt>
                        <dd class="mt-1 text-2xl font-bold tracking-tight text-white">{{ $stats['nodes'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="overflow-hidden rounded-xl bg-slate-800 p-6 shadow-xl border border-slate-700">
            <div class="flex items-center">
               <div class="flex-shrink-0 rounded-lg bg-indigo-500/10 p-3">
                   <svg class="h-6 w-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                       <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                   </svg>
               </div>
               <div class="ml-5 w-0 flex-1">
                   <dl>
                       <dt class="truncate text-sm font-medium text-slate-400">Total Users</dt>
                       <dd class="mt-1 text-2xl font-bold tracking-tight text-white">{{ $stats['users'] }}</dd>
                   </dl>
               </div>
           </div>
       </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <!-- Recent Servers -->
        <div class="bg-slate-800 rounded-xl shadow-xl border border-slate-700 overflow-hidden">
            <div class="p-6 border-b border-slate-700">
                <h3 class="text-lg font-medium text-white">Recent Servers</h3>
            </div>
            <div class="p-6">
                @if($recentServers->isEmpty())
                    <p class="text-slate-400 text-sm">No servers deployed yet.</p>
                @else
                    <div class="flow-root">
                        <ul role="list" class="-my-5 divide-y divide-slate-700">
                            @foreach($recentServers as $server)
                            <li class="py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="h-8 w-8 rounded-full bg-slate-700 flex items-center justify-center">
                                            <span class="text-xs font-bold text-white">{{ substr($server->name, 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-white">{{ $server->name }}</p>
                                        <p class="truncate text-xs text-slate-400">{{ $server->egg->name }} &bull; {{ $server->node->name }}</p>
                                    </div>
                                    <div>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium 
                                            {{ $server->status == 'running' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' }}">
                                            {{ ucfirst($server->status) }}
                                        </span>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        <!-- Node Health -->
         <div class="bg-slate-800 rounded-xl shadow-xl border border-slate-700 overflow-hidden">
            <div class="p-6 border-b border-slate-700">
                <h3 class="text-lg font-medium text-white">Node Status</h3>
            </div>
            <div class="p-6">
                @if($nodes->isEmpty())
                    <p class="text-slate-400 text-sm">No nodes configured.</p>
                @else
                    <div class="space-y-4">
                        @foreach($nodes as $node)
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-slate-200">{{ $node->name }}</span>
                                <span class="text-xs text-slate-400">{{ $node->servers_count }} servers</span>
                            </div>
                            <div class="w-full bg-slate-700 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: 45%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
         </div>
    </div>
</div>
@endsection
