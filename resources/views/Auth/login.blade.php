@extends('layouts.guest')

@section('title', 'AVXAV ACS | Login')

@section('content')

<style>
    .section-authentication-cover {
        min-height: 100vh;
        display: flex;
        align-items: stretch;
    }

    .auth-cover-left {
        position: relative;
        overflow: hidden;
        background-color: #000;
        box-shadow: -5px 0 10px rgba(0, 0, 0, 0.2);
    }

    .auth-img-cover-login {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: absolute;
        top: 0;
        left: 0;
    }

    .auth-cover-right {
        background: #fff;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: -5px 0 10px rgba(0, 0, 0, 0.2);
    }

    .card-body {
        max-width: 400px;
        width: 100%;
    }

    .separator {
        display: flex;
        align-items: center;
        margin: 1.5rem 0;
    }

    .separator .line {
        flex: 1;
        height: 1px;
        background: #dee2e6;
    }

    @media (max-width: 1199px) {
        .auth-cover-left {
            display: none !important;
        }

        .auth-cover-right {
            width: 100%;
            border: none;
        }
    }

    /* Additional fix for border on the right */
    .auth-cover-right {
        border-left: 4px rgba(0, 0, 0, 0.8); /* Adjust color to match the primary color */
    }
</style>

<div class="section-authentication-cover">
    <div class="container-fluid p-0">
        <div class="row g-0">
            <div class="col-12 col-xl-8 col-xxl-9 auth-cover-left d-none d-xl-flex">
                <video src="{{ asset('assets/Videos/AVX-Video.mp4') }}" class="auth-img-cover-login" autoplay loop muted playsinline>
                    Your browser does not support the video tag.
                </video>
            </div>

            <div class="col-12 col-xl-4 col-xxl-4 auth-cover-right d-flex align-items-center justify-content-center">
                <div class="card rounded-0 border-0 shadow-none bg-transparent">
                    <div class="card-body p-4 p-sm-5">
                        <img src="{{ asset('assets/AVXAV Logos/logo_black.png') }}" class="mb-4" width="145" alt="AVXAV Logo">
                        <h4 class="fw-bold">Get Started Now</h4>
                        <p class="mb-0">Enter your credentials to login to your account</p>

                        <div class="separator">
                            <div class="line"></div>
                            <div class="line"></div>
                        </div>

                        <div class="form-body mt-4">
                            @if ($errors->any())
                                <div class="alert alert-danger border-0 bg-grd-danger alert-dismissible fade show">
                                    <div class="d-flex align-items-center">
                                        <div class="font-35 text-white">
                                            <span class="material-icons-outlined fs-2">report_gmailerrorred</span>
                                        </div>
                                        <div class="ms-3">
                                            <div class="text-white">
                                                @foreach ($errors->all() as $error)
                                                    <div>{{ $error }}</div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <form class="row g-3" method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="col-12">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" id="email" placeholder="jhon@example.com" required>
                                </div>
                                <div class="col-12">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group" id="show_hide_password">
                                        <input type="password" class="form-control" name="password" id="password" placeholder="Enter Password" required>
                                        <a href="javascript:;" class="input-group-text bg-transparent"><i class="bi bi-eye-slash-fill"></i></a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                        <label class="form-check-label" for="remember">Remember Me</label>
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <a href="">Forgot Password?</a>
                                </div>
                                <div class="col-12">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-grd-primary" style="color: white;">Login</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
