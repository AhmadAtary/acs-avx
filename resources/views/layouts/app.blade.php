<!doctype html>
<html lang="en" data-bs-theme="light">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'AVXAV ACS | Dashboard')</title>

  <!--favicon-->
  <link rel="icon" href="{{ asset('assets/AVXAV Logos/Mini icon.ico') }}" type="image/png">

  <!-- loader-->
  <link href="{{ asset('assets/css/pace.min.css') }}" rel="stylesheet">
  <script src="{{ asset('assets/js/pace.min.js') }}"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- plugins -->
  <link href="{{ asset('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/metismenu/metisMenu.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/simplebar/css/simplebar.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/extra-icons.css')}}">
  <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

  <!-- main css -->
  <link href="{{ asset('assets/css/bootstrap-extended.css') }}" rel="stylesheet">
  <link href="{{ asset('sass/main.css') }}" rel="stylesheet">
  <link href="{{ asset('sass/dark-theme.css') }}" rel="stylesheet">
  <link href="{{ asset('sass/blue-theme.css') }}" rel="stylesheet">
  <link href="{{ asset('sass/semi-dark.css') }}" rel="stylesheet">
  <link href="{{ asset('sass/bordered-theme.css') }}" rel="stylesheet">
  <link href="{{ asset('sass/responsive.css') }}" rel="stylesheet">
</head>

@yield('styles')

<style>
  html, body {
    height: 100%;
    margin: 0;
  }

  body {
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease-in-out;
  }

  .main-wrapper {
    flex: 1;
  }

  .page-footer {
    background-color: #f8f9fa;
    text-align: center;
    padding: 10px 0;
    position: relative;
  }

  /* Sidebar starts collapsed */
  body.toggled .sidebar-wrapper {
    width: 260px;
    transition: width 0.3s ease-in-out;
  }

  /* Sidebar expands on hover */
  body.toggled.sidebar-hovered .sidebar-wrapper {
    width: 250px;
  }
</style>

<body class="toggled"> <!-- Sidebar starts collapsed -->

  <!-- Start Header -->
  <header class="top-header">
    @include('partials.header')
  </header>
  <!-- End Header -->

  <!-- Start Sidebar -->
  @include('partials.sidebar')
  <!-- End Sidebar -->

  <!-- Start Main Wrapper -->
  <main class="main-wrapper">
    <div class="main-content">
      @yield('content')
    </div>
  </main>
  <!-- End Main Wrapper -->

  <!-- Start Overlay -->
  <div class="overlay btn-toggle"></div>
  <!-- End Overlay -->

  <!-- Start Footer -->
  <footer class="page-footer">
    <p class="mb-0">Copyright © 2025. All rights reserved.</p>
  </footer>
  <!-- End Footer -->

  <!-- Scripts -->
  <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
  <script src="{{ asset('assets/plugins/metismenu/metisMenu.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/apexchart/apexcharts.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/simplebar/js/simplebar.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/peity/jquery.peity.min.js') }}"></script>

  <script>
    $(".data-attributes span").peity("donut");

    $(document).ready(function () {
        // Sidebar starts in collapsed mode
        $("body").addClass("toggled");

        // Sidebar toggle button click event
        $(".btn-toggle").click(function () {
            $("body").toggleClass("toggled");
        });

        // Sidebar hover effect
        $(".sidebar-wrapper").hover(
            function () {
                if ($("body").hasClass("toggled")) {
                    $("body").addClass("sidebar-hovered");
                }
            },
            function () {
                $("body").removeClass("sidebar-hovered");
            }
        );
    });
  </script>

  @yield('scripts')
</body>
</html>
