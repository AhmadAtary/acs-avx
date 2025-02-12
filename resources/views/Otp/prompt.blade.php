@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1>Enter OTP</h1>
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('otp.verify') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="otp_code" class="form-label">OTP Code</label>
            <input type="text" id="otp_code" name="otp_code" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Verify OTP</button>
    </form>
</div>
@endsection
