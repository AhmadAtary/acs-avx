@extends('layouts.guest')

@section('title', 'AVXAV ACS | OTP')

@section('content')

<div class="section-authentication-cover">
    <div class="">
        <div class="row g-0">

            <div class="col-12 col-xl-7 col-xxl-8 auth-cover-left align-items-center justify-content-center d-none d-xl-flex border-end bg-transparent">
            <div style="color: white; background-color: white;">
                <div class="">
                    <video src="{{ asset('assets/Videos/AVX-Video.mp4') }}" class="auth-img-cover-login" autoplay loop muted playsinline>
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
            </div>

            <div class="col-12 col-xl-5 col-xxl-4 auth-cover-right align-items-center justify-content-center border-top border-4 border-primary border-gradient-1">
                <div class="card rounded-0 m-3 mb-0 border-0 shadow-none bg-none">
                    <div class="card-body p-sm-5">
                        <img src="{{ asset('assets/AVXAV Logos/logo_black.png') }}" class="mb-4" width="145" alt="">
                        <h4 class="fw-bold">Get Started Now</h4>
                        <p class="mb-0">Enter your OTP to login to your account</p>

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

                            <form class="row g-3" method="POST" action="/otp/verify">
                                @csrf
                                <div class="col-12">
                                    <label for="otp_code" class="form-label">OTP Code</label>
                                    <input type="text" id="otp_code" name="otp_code" class="form-control" required placeholder="Enter your OTP code">
                                </div>
                                <div class="col-12" style="margin-top:20px">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-grd-primary" style="color: white;">Submit</button>
                                    </div>
                                </div>
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
