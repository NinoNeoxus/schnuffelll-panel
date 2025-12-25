@extends('layouts.app')

@section('title', 'Create Location')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-slate-800 shadow-xl rounded-xl border border-slate-700 overflow-hidden p-6">
        <form action="{{ route('admin.locations.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label for="short" class="block text-sm font-medium text-slate-300">Short Code</label>
                <div class="mt-1">
                    <input type="text" name="short" id="short" placeholder="us.nyc" required class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                </div>
                <p class="mt-1 text-xs text-slate-400">A short identifier, e.g. "de.fra" or "us.nyc"</p>
            </div>

            <div>
                <label for="long" class="block text-sm font-medium text-slate-300">Description</label>
                <div class="mt-1">
                    <input type="text" name="long" id="long" placeholder="Frankfurt, Germany" class="block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <a href="{{ route('admin.locations.index') }}" class="mr-3 px-4 py-2 text-sm font-medium text-slate-300 hover:text-white">Cancel</a>
                <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                    Create Location
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
