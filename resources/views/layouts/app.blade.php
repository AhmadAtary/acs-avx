<!doctype html>
<html lang="en" data-bs-theme="blue-theme">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Dashboard')</title>
  <!--favicon-->
  <link rel="icon" href="{{ asset('assets/images/favicon-32x32.png') }}" type="image/png">
  <!-- loader-->
	<link href="{{ asset('assets/css/pace.min.css') }}" rel="stylesheet">
	<script src="{{ asset('assets/js/pace.min.js') }}"></script>

  <!--plugins-->
  <link href="{{ asset('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/plugins/metismenu/metisMenu.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/plugins/metismenu/mm-vertical.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/plugins/simplebar/css/simplebar.css') }}">
  <!--bootstrap css-->
  <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined" rel="stylesheet">
  <!--main css-->
  <link href="{{ asset('assets/css/bootstrap-extended.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/dark-theme.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/blue-theme.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/semi-dark.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/bordered-theme.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/responsive.css') }}" rel="stylesheet">

  <link href="{{ asset('assets/css/bootstrap-extended.css') }}" rel="stylesheet">
  <link href="{{ asset('sass/main.css') }}" rel="stylesheet">
  <link href="{{ asset('sass/dark-theme.css') }}" rel="stylesheet">
  <link href="{{ asset('sass/blue-theme.css') }}" rel="stylesheet">
  <link href="{{ asset('sass/responsive.css') }}" rel="stylesheet">
  @stack('styles') <!-- Allow extra styles to be added -->

</head>

<body>

  <!-- Start Header -->
  @include('partials.header')
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

  <!-- Start Footer -->
  <footer class="page-footer">
    <p class="mb-0">Copyright © 2025. All rights reserved.</p>
  </footer>
  <!-- End Footer -->

<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

<!--plugins-->
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/plugins/metismenu/metisMenu.min.js') }}"></script>
<script src="{{ asset('assets/plugins/apexchart/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/plugins/simplebar/js/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/plugins/peity/jquery.peity.min.js') }}"></script>
<script>
  $(".data-attributes span").peity("donut")
</script>
<script src="{{ asset('assets/js/main.js') }}"></script>
<script src="{{ asset('assets/js/dashboard1.js') }}"></script>
<script>
   new PerfectScrollbar(".user-list")
</script>

@stack('scripts') <!-- Allow extra scripts to be added -->

</body>

</html>
