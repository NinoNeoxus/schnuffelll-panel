@extends('layouts.admin')

@section('title', 'Eggs')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">🥚 Game Eggs</h1>
        <div>
            <form action="{{ route('admin.eggs.import') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-file-import"></i> Import from JSON
                </button>
            </form>
            <a href="{{ route('admin.eggs.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Egg
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Nest</th>
                        <th>Author</th>
                        <th>Servers</th>
                        <th>Variables</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eggs as $egg)
                    <tr>
                        <td>
                            <strong>{{ $egg->name }}</strong>
                            <br><small class="text-muted">{{ Str::limit($egg->description, 50) }}</small>
                        </td>
                        <td>{{ $egg->nest->name ?? 'N/A' }}</td>
                        <td>{{ $egg->author }}</td>
                        <td><span class="badge bg-info">{{ $egg->servers_count ?? $egg->servers->count() }}</span></td>
                        <td><span class="badge bg-secondary">{{ $egg->variables->count() }}</span></td>
                        <td>
                            <a href="{{ route('admin.eggs.show', $egg) }}" class="btn btn-sm btn-outline-primary">View</a>
                            <a href="{{ route('admin.eggs.edit', $egg) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <a href="{{ route('admin.eggs.export', $egg) }}" class="btn btn-sm btn-outline-success">Export</a>
                            <form action="{{ route('admin.eggs.destroy', $egg) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                    onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            No eggs found. Import some or create a new one!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $eggs->links() }}
        </div>
    </div>
</div>
@endsection
