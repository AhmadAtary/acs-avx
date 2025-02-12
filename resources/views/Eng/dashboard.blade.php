@extends('layouts.app')

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
  <div class="breadcrumb-title pe-3">Engineering Dashboard</div>
</div>

<div class="row g-3">
  <!-- First Column -->
  <div class="col-md-6 col-12 d-flex flex-column gap-3">
  <div class="card w-100 rounded-4 centered-card">
  <div class="card-body">
    <h5 class="mb-1">Welcome, <span id="userName">{{ auth()->user()->name }}</span></h5>
      <p class="mb-0">Role: <span id="userRole">{{ auth()->user()->access->role }}</span></p>
    </div>
      </div>
        <div class="card w-100 h-100 rounded-4">
          <div class="card-body">
            <div class="d-flex flex-column gap-3">
              <div class="d-flex align-items-start justify-content-between">
                <h5 class="mb-0">Devices Connection Type</h5>
              </div>
              <div class="position-relative">
                <div id="chart6" style="min-height: 290px;"></div>
              </div>
              <div class="d-flex flex-column gap-3">
              <div class="d-flex align-items-center justify-content-between">
              <p class="mb-0 d-flex align-items-center gap-2 w-50">
                  <span class="material-icons-outlined fs-6 text-primary">public</span> Connected Devices
              </p>
              <p class="mb-0">{{ $online_devices_count }}</p>
    </div>
    <div class="d-flex align-items-center justify-content-between">
        <p class="mb-0 d-flex align-items-center gap-2 w-50">
            <span class="material-icons-outlined fs-6 text-danger">public_off</span> Disconnected Devices
        </p>
        <p class="mb-0">{{ $offline_devices_count }}</p>
    </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Second Column -->
  <div class="col-md-6 col-12 d-flex flex-column gap-3">
    <div class="card w-100 h-100 rounded-4">
      <div class="card-body d-flex flex-column justify-content-center align-items-center">
        <h5 class="mb-0">All Devices Count</h5>
        <h1 id="deviceCounter" class="mb-0 fw-bold">{{ $devices->count() }}</h1>
      </div>
    </div>
    <div class="card w-100 h-100 rounded-4">
      <div class="card-body d-flex flex-column justify-content-center align-items-center">
        <h5 class="mb-0">Devices Count</h5>
        <div id="chart1" style="min-height: 290px; width: 100%"></div>
      </div>
    </div>
  </div>
</div>




        
<div class="row g-3 mt-3">
  <!-- Models Table -->
  <div class="col-md-6 col-12">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Models</h5>
          @php
            $user = Auth::user();
          @endphp
          @if($user->hasRole('owner'))
          <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModelModal">Add Model</button>
          @endif
        </div>
        <div class="table-responsive" style="max-height: 400px;">
          <table class="table table-bordered mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Commercial Name</th>
                <th>Product Class</th>
              </tr>
            </thead>
            <tbody>
              @if($models->isEmpty())
                <tr>
                  <td colspan="3" class="text-center">No models added yet.</td>
                </tr>
              @else
                @foreach($models as $index => $model)
                  <tr>
                    <th>{{ $index + 1 }}</th>
                    <td>{{ $model->Model }}</td>
                    <td>{{ $model->Product_Class }}</td>
                  </tr>
                @endforeach
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Devices Table -->
  <div class="col-md-6 col-12">
    <div class="card h-100">
      <div class="card-body">
        <h5 class="mb-3">Devices</h5>
        <div class="table-responsive" style="max-height: 400px;">
          <table class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Serial Number</th>
                <th>Manufacturer</th>
                <th>OUI</th>
                <th>Product Class</th>
              </tr>
            </thead>
            <tbody>
              @foreach($devices as $device)
                <tr>
                  <td>{{ $device->_deviceId['_SerialNumber'] }}</td>
                  <td>{{ $device->_deviceId['_Manufacturer'] ?? 'N/A' }}</td>
                  <td>{{ $device->_deviceId['_OUI'] ?? 'N/A' }}</td>
                  <td>{{ $device->_deviceId['_ProductClass'] ?? 'N/A' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add Model Modal -->
<div class="modal fade" id="addModelModal" tabindex="-1" aria-labelledby="addModelModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Model</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('models.store') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label for="modelName" class="form-label">Commercial Name</label>
            <input type="text" class="form-control" id="modelName" name="Model" required>
          </div>
          <div class="mb-3">
            <label for="OUI" class="form-label">OUI</label>
            <input type="text" class="form-control" id="OUI" name="OUI" required>
          </div>
          <div class="mb-3">
            <label for="productClass" class="form-label">Product Class</label>
            <input type="text" class="form-control" id="productClass" name="Product_Class" required>
          </div>
          <button type="submit" class="btn btn-primary">Save</button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

@section('styles')
<style>
  .card.centered-card {
  height: 100%; /* Ensures full height based on parent container */
  min-height: 100px; /* Matches opposite card height */
  display: flex;
  justify-content: center;
  align-items: center;
  text-align: center;
}
</style>


@endsection


@section('scripts')
    <!-- Load ApexCharts -->
    <script src="{{ asset('assets/plugins/apexchart/apexcharts.min.js') }}"></script>
    <!-- <script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script> -->
    <script src="{{ asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var onlineDevices = Number("{{ $online_devices_count }}");
        var offlineDevices = Number("{{ $offline_devices_count }}");
        var totalDevices = Number("{{ $devices_count }}");

        console.log("Online Devices:", onlineDevices);
        console.log("Offline Devices:", offlineDevices);
        console.log("Total Devices:", totalDevices);

        var options = {
        series: [onlineDevices,offlineDevices],
        chart: {
            height: 290,
            type: 'donut',
        },
        labels: ["Online Devices", "Offline Devices"],
        legend: {
            position: 'bottom',
            show: !1
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'dark',
                gradientToColors: ["#3494e6", "#e6344f", "#3494e6"],
                shadeIntensity: 1,
                type: 'vertical',
                opacityFrom: 1,
                opacityTo: 1,
                stops: [0, 100, 100, 100]
            },
        },
        colors: ["#3494e6", "#e6344f", "#3494e6"],
        dataLabels: {
            enabled: !1
        },
        plotOptions: {
            pie: {
                donut: {
                    size: "85%"
                }
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    height: 270
                },
                legend: {
                    position: 'bottom',
                    show: !1
                }
            }
        }]
    };

        var chart = new ApexCharts(document.querySelector("#chart6"), options);
        chart.render();
    });

      var options = {
      series: [{
        name: "New Device",
        data: [4, 10, 25, 12, 25, 18, 40, 22, 7]
    }],
      chart: {
      foreColor: "#9ba7b2",
      height: 350,
      type: 'area',
      zoom: {
        enabled: false
      },
      toolbar: {
          show: !1,
      },
    },
    dataLabels: {
      enabled: false
    },
    stroke: {
      width: 4,
      curve: 'smooth'
    },
    fill: {
      type: 'gradient',
      gradient: {
        shade: 'dark',
        gradientToColors: ['#ff0080'],
        shadeIntensity: 1,
        type: 'vertical',
        opacityFrom: 0.8,
        opacityTo: 0.1,
        stops: [0, 100, 100, 100]
      },
    },
    colors: ["#ffd200"],
    grid: {
      show: true,
      borderColor: 'rgba(0, 0, 0, 0.15)',
      strokeDashArray: 4,
    },
    tooltip: {
      theme: "dark",
    },
    xaxis: {
      categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
    },
    markers: {
      show: !1,
      size: 5,
    },
    };

  var chart = new ApexCharts(document.querySelector("#chart1"), options);
  chart.render();

    let count = 0;
    let target = {{ $devices_count }}; // Laravel variable
    let duration = 1000; // Animation duration in milliseconds
    let stepTime = Math.abs(Math.floor(duration / target));

    function counterAnimation() {
      let interval = setInterval(() => {
        count++;
        document.getElementById("deviceCounter").innerText = count;
        if (count >= target) {
          clearInterval(interval);
        }
      }, stepTime);
    }

    counterAnimation();
    </script>
@endsection
