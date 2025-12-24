@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white font-weight-bold">
                    System Settings
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            {{$errors->first()}}
                        </div>
                    @endif

                    <div class="media align-items-center">
                        <div class="media-body">
                            <h5 class="mt-0">System Update</h5>
                            <p class="text-muted mb-0">
                                Update the panel to the latest version. This process may take a few minutes and will put the panel in maintenance mode briefly.
                            </p>
                            <small class="text-secondary">Only available for Root Admins.</small>
                        </div>
                        <div class="ml-3">
                            <form action="{{ route('admin.settings.update') }}" method="POST" onsubmit="return confirm('Are you sure you want to update the panel?');">
                                @csrf
                                <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cloud-arrow-down-fill" viewBox="0 0 16 16">
                                      <path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2zm2.354 6.854-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 1 1 .708-.708L7.5 9.293V5.5a.5.5 0 0 1 1 0v3.793l1.146-1.147a.5.5 0 0 1 .708.708z"/>
                                    </svg>
                                    Update Now
                                </button>
                            </form>
                        </div>
                    </div>

                    <hr>
                    
                    <div class="mt-4">
                        <h6 class="text-uppercase text-muted font-weight-bold" style="font-size: 0.8rem;">System Info</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                Panel Version
                                <span class="badge badge-secondary badge-pill">1.0.0</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                PHP Version
                                <span class="text-muted">{{ phpversion() }}</span>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
