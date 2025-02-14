@extends('layouts.app')

@section('content')
<div class="container mt-5">
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
  <div class="breadcrumb-title pe-3">Profile</div>
</div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="row">
<div class="col-md-6">
        <div class="card w-100 h-100 rounded-4">
            <div class="card-body">
            <form action="{{ route('profile.update') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password (leave blank if not changing)</label>
            <input type="password" id="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
        </div>

        <div class="mb-3">
            <label for="is_otp_verified" class="form-label">Enable OTP Verification</label>
            <select id="is_otp_verified" name="is_otp_verified" class="form-select">
                <option value="1" {{ $user->is_otp_verified ? 'selected' : '' }}>Enabled</option>
                <option value="0" {{ !$user->is_otp_verified ? 'selected' : '' }}>Disabled</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
            </div>
        </div>
    </div>
</div>
    
</div>
@endsection
