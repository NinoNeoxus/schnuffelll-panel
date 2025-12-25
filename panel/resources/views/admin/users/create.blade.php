@extends('layouts.app')

@section('title', 'Create User')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-slate-800 shadow-xl rounded-xl border border-slate-700 overflow-hidden p-6">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label for="name" class="block text-sm font-medium text-slate-300">Full Name</label>
                <input type="text" name="name" id="name" required class="mt-1 block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-300">Email Address</label>
                <input type="email" name="email" id="email" required class="mt-1 block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
                <input type="password" name="password" id="password" required class="mt-1 block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-300">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required class="mt-1 block w-full rounded-md border-0 bg-slate-900 py-2 text-white shadow-sm ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6">
            </div>

            <div class="relative flex items-start">
                <div class="flex h-6 items-center">
                    <input id="root_admin" name="root_admin" type="checkbox" value="1" class="h-4 w-4 rounded border-slate-700 bg-slate-900 text-blue-600 focus:ring-blue-600 focus:ring-offset-slate-900">
                </div>
                <div class="ml-3 text-sm leading-6">
                    <label for="root_admin" class="font-medium text-white">Root Administrator</label>
                    <p class="text-slate-400">Grant this user full administrative access to the panel.</p>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <a href="{{ route('admin.users.index') }}" class="mr-3 px-4 py-2 text-sm font-medium text-slate-300 hover:text-white">Cancel</a>
                <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
