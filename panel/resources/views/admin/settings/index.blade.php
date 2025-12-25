@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-slate-800 shadow-xl rounded-xl border border-slate-700 overflow-hidden">
        <div class="p-6 border-b border-slate-700">
             <h3 class="text-lg font-medium leading-6 text-white">System Settings</h3>
             <p class="mt-1 text-sm text-slate-400">Manage panel configuration and updates.</p>
        </div>
        
        <div class="p-6 space-y-8">
            @if (session('success'))
                <div class="rounded-md bg-emerald-500/10 p-4 border border-emerald-500/20">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-emerald-400">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($errors->any())
                 <div class="rounded-md bg-red-500/10 p-4 border border-red-500/20">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-400">{{ $errors->first() }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-between">
                <div>
                     <h4 class="text-base font-semibold text-white">System Update</h4>
                     <p class="text-sm text-slate-400 mt-1">Update the panel to the latest version.</p>
                     <p class="text-xs text-amber-500 mt-1">Process requires maintenance mode.</p>
                </div>
                <div>
                     <form action="{{ route('admin.settings.update') }}" method="POST" onsubmit="return confirm('Are you sure you want to update the panel?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                             <svg class="-ml-0.5 mr-1.5 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                  <path d="M10.75 2.75a.75.75 0 00-1.5 0v8.614L6.295 8.235a.75.75 0 10-1.09 1.03l4.25 4.5a.75.75 0 001.09 0l4.25-4.5a.75.75 0 00-1.09-1.03l-2.965 3.129V2.75z" />
                                  <path d="M5.5 17a.75.75 0 000 1.5h9a.75.75 0 000-1.5h-9z" />
                             </svg>
                            Update Now
                        </button>
                    </form>
                </div>
            </div>

            <div class="border-t border-slate-700 pt-6">
                <h4 class="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">System Information</h4>
                <dl class="divide-y divide-slate-700">
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-slate-300">Panel Version</dt>
                        <dd class="text-sm text-slate-400">1.0.0 (Schnuffelll)</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-slate-300">PHP Version</dt>
                        <dd class="text-sm text-slate-400 font-mono">{{ phpversion() }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                         <dt class="text-sm font-medium text-slate-300">Environment</dt>
                         <dd class="text-sm text-slate-400">{{ app()->environment() }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                         <dt class="text-sm font-medium text-slate-300">Debug Mode</dt>
                         <dd class="text-sm {{ config('app.debug') ? 'text-red-400' : 'text-emerald-400' }}">
                             {{ config('app.debug') ? 'Enabled' : 'Disabled' }}
                         </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
