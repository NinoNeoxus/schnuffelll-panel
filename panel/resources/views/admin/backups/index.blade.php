@extends('layouts.admin')

@section('title', 'Backups')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">💾 Server Backups</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBackupModal">
            <i class="fas fa-plus"></i> Create Backup
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header">Filter</div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <select name="server_id" class="form-select">
                        <option value="">All Servers</option>
                        @foreach($servers as $server)
                            <option value="{{ $server->id }}" {{ $serverId == $server->id ? 'selected' : '' }}>
                                {{ $server->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Server</th>
                        <th>Size</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups as $backup)
                    <tr>
                        <td>{{ $backup->name }}</td>
                        <td>{{ $backup->server->name ?? 'N/A' }}</td>
                        <td>{{ number_format($backup->size / 1024 / 1024, 2) }} MB</td>
                        <td>
                            @if($backup->is_successful)
                                <span class="badge bg-success">Complete</span>
                            @else
                                <span class="badge bg-warning">Pending</span>
                            @endif
                        </td>
                        <td>{{ $backup->created_at->diffForHumans() }}</td>
                        <td>
                            @if($backup->is_successful)
                                <a href="{{ route('admin.backups.download', $backup) }}" class="btn btn-sm btn-outline-primary">
                                    Download
                                </a>
                                <form action="{{ route('admin.backups.restore', $backup) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success"
                                        onclick="return confirm('Are you sure you want to restore this backup? This will overwrite current server data.')">
                                        Restore
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('admin.backups.destroy', $backup) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Delete this backup?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No backups found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $backups->links() }}
        </div>
    </div>
</div>

<!-- Create Backup Modal -->
<div class="modal fade" id="createBackupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.backups.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create New Backup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Server</label>
                        <select name="server_id" class="form-select" required>
                            <option value="">Select Server</option>
                            @foreach($servers as $server)
                                <option value="{{ $server->id }}">{{ $server->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Backup Name (Optional)</label>
                        <input type="text" name="name" class="form-control" placeholder="Auto-generated if empty">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Backup</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
