@extends('layouts.guest')

@section('title', 'AVXAV ACS | Login')

@section('content')

<div class="section-authentication-cover">
    <div class="">
        <div class="row g-0">

            <div class="col-12 col-xl-7 col-xxl-8 auth-cover-left align-items-center justify-content-center d-none d-xl-flex border-end bg-transparent">
                <div class="card rounded-0 mb-0 border-0 shadow-none bg-transparent bg-none">
                    <div class="card-body">
                        <img src="{{ asset('assets/AVXAV Logos/logo.png') }}" class="img-fluid auth-img-cover-login" width="650" alt="">
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5 col-xxl-4 auth-cover-right align-items-center justify-content-center border-top border-4 border-primary border-gradient-1">
                <div class="card rounded-0 m-3 mb-0 border-0 shadow-none bg-none">
                    <div class="card-body p-sm-5">
                        <img src="{{ asset('assets/AVXAV Logos/logo 3 white.png') }}" class="mb-4" width="145" alt="">
                        <h4 class="fw-bold">Get Started Now</h4>
                        <p class="mb-0">Enter your credentials to login to your account</p>

                        <div class="separator section-padding">
                            <div class="line"></div>
                            <div class="line"></div>
                        </div>

                        <div class="form-body mt-4">
                            @if ($errors->any())
                                <div class="alert alert-danger border-0 bg-grd-danger alert-dismissible fade show">
                                    <div class="d-flex align-items-center">
                                        <div class="font-35 text-white"><span class="material-icons-outlined fs-2">report_gmailerrorred</span>
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-0 text-white">Danger Alerts</h6>
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
                                        <button type="submit" class="btn btn-grd-primary">Login</button>
                                    </div>
                                </div>
                                <!-- <div class="col-12">
                                    <div class="text-start">
                                        <p class="mb-0">Don't have an account yet? <a href="">Sign up here</a></p>
                                    </div>
                                </div> -->
                            </form>
                        </div>

                    </div>
                </div>
            </div>

        </div>
        <!--end row-->
    </div>
</div>

@endsection
