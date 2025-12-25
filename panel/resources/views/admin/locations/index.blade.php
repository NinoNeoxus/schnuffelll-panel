@extends('layouts.app')

@section('title', 'Locations')

@section('content')
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <p class="text-sm text-slate-400">Manage server locations.</p>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('admin.locations.create') }}" class="block rounded-md bg-blue-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                Create Location
            </a>
        </div>
    </div>

    <div class="bg-slate-800 shadow-xl rounded-xl border border-slate-700 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-700">
            <thead class="bg-slate-900/50">
                <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-white sm:pl-6">Short Code</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Description</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Nodes</th>
                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700 bg-slate-800">
                @foreach($locations as $location)
                <tr class="hover:bg-slate-700/50 transition-colors">
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-bold text-white sm:pl-6">{{ $location->short }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-300">{{ $location->long }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-300">
                         <span class="inline-flex items-center rounded-full bg-slate-700 px-2.5 py-0.5 text-xs font-medium text-slate-300">
                             {{ $location->nodes_count }} Nodes
                         </span>
                    </td>
                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                        <a href="#" class="text-red-400 hover:text-red-300">Delete</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
