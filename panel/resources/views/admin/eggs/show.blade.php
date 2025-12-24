@extends('layouts.admin')

@section('title', $egg->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">🥚 {{ $egg->name }}</h1>
        <div>
            <a href="{{ route('admin.eggs.export', $egg) }}" class="btn btn-outline-success">Export JSON</a>
            <a href="{{ route('admin.eggs.edit', $egg) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">Configuration</div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">UUID</dt>
                        <dd class="col-sm-9"><code>{{ $egg->uuid ?? 'N/A' }}</code></dd>
                        
                        <dt class="col-sm-3">Nest</dt>
                        <dd class="col-sm-9">{{ $egg->nest->name ?? 'N/A' }}</dd>
                        
                        <dt class="col-sm-3">Author</dt>
                        <dd class="col-sm-9">{{ $egg->author }}</dd>
                        
                        <dt class="col-sm-3">Description</dt>
                        <dd class="col-sm-9">{{ $egg->description ?? 'No description' }}</dd>
                        
                        <dt class="col-sm-3">Startup Command</dt>
                        <dd class="col-sm-9"><code>{{ $egg->startup }}</code></dd>
                        
                        <dt class="col-sm-3">Stop Command</dt>
                        <dd class="col-sm-9"><code>{{ $egg->config_stop ?? 'stop' }}</code></dd>
                    </dl>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">Docker Images</div>
                <div class="card-body">
                    @if($egg->docker_images && count($egg->docker_images) > 0)
                        <ul class="list-group">
                            @foreach($egg->docker_images as $name => $image)
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>{{ $name }}</strong>
                                <code>{{ $image }}</code>
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No Docker images configured.</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">Variables ({{ $egg->variables->count() }})</div>
                <div class="card-body">
                    @if($egg->variables->count() > 0)
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Env Variable</th>
                                <th>Default</th>
                                <th>Permissions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($egg->variables as $var)
                            <tr>
                                <td>{{ $var->name }}</td>
                                <td><code>{{ $var->env_variable }}</code></td>
                                <td>{{ $var->default_value }}</td>
                                <td>
                                    @if($var->user_viewable) <span class="badge bg-info">View</span> @endif
                                    @if($var->user_editable) <span class="badge bg-success">Edit</span> @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                        <p class="text-muted">No variables defined.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">Statistics</div>
                <div class="card-body text-center">
                    <h2 class="display-4">{{ $egg->servers->count() }}</h2>
                    <p class="text-muted">Active Servers</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Install Script</div>
                <div class="card-body">
                    <p><strong>Container:</strong> <code>{{ $egg->script_container ?? 'N/A' }}</code></p>
                    <p><strong>Entrypoint:</strong> <code>{{ $egg->script_entry ?? 'N/A' }}</code></p>
                    @if($egg->script_install)
                    <pre class="bg-dark text-light p-2 rounded" style="max-height: 300px; overflow: auto;">{{ $egg->script_install }}</pre>
                    @else
                    <p class="text-muted">No install script defined.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
