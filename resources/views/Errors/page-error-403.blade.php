<!doctype html>
<html lang="en" data-bs-theme="blue-theme">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AVXAV | Error 403</title>
    <!--favicon-->
    <link rel="icon" href="{{ asset('assets/AVXAV Logos/Mini icon.ico') }}" type="image/png">
    <!-- loader-->
    <link href="{{asset('assets/css/pace.min.css')}}" rel="stylesheet">
    <script src="{{asset('assets/js/pace.min.js')}}"></script>

    <!--Styles-->
    <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/css/bootstrap-extended.css')}}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined" rel="stylesheet">
    <link href="{{asset('sass/main.css')}}" rel="stylesheet">
    <link href="{{asset('sass/blue-theme.css')}}" rel="stylesheet">
  </head>

<body class="bg-error">

<!-- Start wrapper-->
<div class="pt-5">
    <div class="container pt-5">
        <div class="row pt-5">
            <div class="col-lg-12">
                <div class="text-center error-pages">
                    <h1 class="error-title text-info mb-3">403</h1>
                    <h2 class="error-sub-title text-white">Forbidden Error</h2>

                    <p class="error-message text-white text-uppercase">
                        You don't have permission to access this resource on this server
                    </p>

                    <div class="mt-4 d-flex align-items-center justify-content-center gap-3">
                        <a href="{{ route('dashboard') }}" class="btn btn-grd-danger rounded-5 px-4">
                            <i class="bi bi-house-fill me-2"></i>Go To Home
                        </a>
                        <a href="javascript:history.back();" class="btn btn-outline-light rounded-5 px-4">
                            <i class="bi bi-arrow-left me-2"></i>Previous Page
                        </a>
                    </div>

                    <div class="mt-4">
                        <p class="text-light">Copyright © 2025 | All rights reserved.</p>
                    </div>
                    <hr class="border-light border-2">
                </div>
            </div>
        </div><!--end row-->
    </div>
</div><!--wrapper-->

</body>
</html>
