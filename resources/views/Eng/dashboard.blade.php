@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
        <div class="breadcrumb-title pe-3">Engineering Dashboard</div>
    </div>

    <div class="row g-3">
        <!-- First Column -->
        <div class="col-lg-6 col-12">
            <div class="d-flex flex-column gap-3 h-100">
                <div class="card rounded-4 h-auto">
                    <div class="card-body text-center">
                        <h5 class="mb-2">Welcome, <span id="userName">{{ auth()->user()->name ?? 'User' }}</span></h5>
                        <p class="mb-0">Role: <span id="userRole">
                            @if(auth()->user()->access && auth()->user()->access->role === 'owner')
                                Super-admin
                            @else
                                {{ auth()->user()->access->role ?? 'N/A' }}
                            @endif
                        </span></p>
                    </div>
                </div>
                <div class="card rounded-4 flex-grow-1">
                    <div class="card-body d-flex flex-column">
                        <h5 class="mb-3">Device Connection Status</h5>
                        <div class="position-relative flex-grow-1">
                            <div id="chart6" style="min-height: 300px;"></div>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <p class="mb-0 d-flex align-items-center gap-2">
                                    <span class="material-icons-outlined fs-6 text-primary">public</span> Connected
                                </p>
                                <p class="mb-0">{{ $online_devices_count ?? 0 }}</p>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <p class="mb-0 d-flex align-items-center gap-2">
                                    <span class="material-icons-outlined fs-6 text-danger">public_off</span> Disconnected
                                </p>
                                <p class="mb-0">{{ $offline_devices_count ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second Column -->
        <div class="col-lg-6 col-12">
            <div class="d-flex flex-column gap-3 h-100">
                <div class="card rounded-4 h-auto">
                    <div class="card-body text-center">
                        <h5 class="mb-2">Total Devices</h5>
                        <h1 id="deviceCounter" class="mb-0 fw-bold">{{ $devices->count() ?? 0 }}</h1>
                    </div>
                </div>
                <div class="card rounded-4 flex-grow-1">
                    <div class="card-body d-flex flex-column">
                        <h5 class="mb-3">Device Trend</h5>
                        <div id="chart1" style="min-height: 300px; flex-grow: 1;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-3 mt-3">
    <!-- Devices by Model Card -->
    <div class="col-12">
        <div class="card rounded-4 chart-card">
            <div class="card-body">
                <h5 class="mb-3">Devices by Model</h5>
                <div id="modelChart" class="chart-container"></div>
            </div>
        </div>
    </div>
</div>

    <!-- Tables Section -->
    <div class="row g-3 mt-3">
        <div class="col-lg-6 col-12">
            <div class="card rounded-4 h-100">
                <div class="card-body">
                    <h5 class="mb-3">Models</h5>
                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Commercial Name</th>
                                    <th>Product Class</th>
                                    <th>OUI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($models->isEmpty())
                                    <tr>
                                        <td colspan="4" class="text-center">No models added yet.</td>
                                    </tr>
                                @else
                                    @foreach($models as $index => $model)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $model->model_name ?? 'N/A' }}</td>
                                            <td>{{ $model->product_class ?? 'N/A' }}</td>
                                            <td>{{ $model->oui ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-12">
            <div class="card rounded-4 h-100">
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
                                        <td>{{ $device->_deviceId['_SerialNumber'] ?? 'N/A' }}</td>
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
</div>
@endsection

@section('styles')
<style>
.card {
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.table-responsive {
    scrollbar-width: thin;
}

.table-responsive::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}


@media (max-width: 768px) {
    .card {
        margin-bottom: 1rem;
    }
    
    .page-breadcrumb {
        display: flex !important;
    }
}
</style>
@endsection

@section('scripts')
<script src="{{ asset('assets/plugins/apexchart/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Connection Status Chart
    const connectionChartOptions = {
        series: [Number("{{ $online_devices_count ?? 0 }}"), Number("{{ $offline_devices_count ?? 0 }}")],
        chart: {
            height: 300,
            type: 'donut',
            animations: { enabled: true }
        },
        labels: ["Online Devices", "Offline Devices"],
        legend: { position: 'bottom', show: false },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'dark',
                gradientToColors: ["#3494e6", "#e6344f"],
                shadeIntensity: 1,
                type: 'vertical',
                opacityFrom: 1,
                opacityTo: 1,
                stops: [0, 100]
            }
        },
        colors: ["#3494e6", "#e6344f"],
        dataLabels: { enabled: false },
        plotOptions: {
            pie: {
                donut: { size: "85%" }
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: { height: 270 },
                legend: { show: false }
            }
        }]
    };

    const connectionChart = new ApexCharts(document.querySelector("#chart6"), connectionChartOptions);
    connectionChart.render();

    // Device Trend Chart
    const startOfWeekTimestamp = {{ strtotime($startOfWeek ?? now()->startOfWeek()) * 1000 }};
    const currentTimestamp = new Date().getTime();
    const deviceTrendOptions = {
        series: [{
            name: "New Devices",
            data: @json($newDevices ?? [])
        }],
        chart: {
            foreColor: "#9ba7b2",
            height: 300,
            type: 'area',
            zoom: { enabled: true },
            toolbar: {
                show: true,
                tools: { pan: true, zoomin: true, zoomout: true, reset: true }
            },
            animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        dataLabels: { enabled: false },
        stroke: { width: 3, curve: 'smooth' },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'dark',
                gradientToColors: ['#ff0080'],
                shadeIntensity: 1,
                type: 'vertical',
                opacityFrom: 0.8,
                opacityTo: 0.1,
                stops: [0, 100]
            }
        },
        colors: ["#ffd200"],
        grid: {
            show: true,
            borderColor: 'rgba(0, 0, 0, 0.15)',
            strokeDashArray: 4
        },
        tooltip: { theme: "dark" },
        xaxis: {
            categories: @json($dates ?? []),
            labels: {
                format: 'yyyy-MM-dd',
                rotate: -45,
                style: { fontSize: '12px' }
            },
            tickAmount: 7,
            type: 'datetime',
            min: startOfWeekTimestamp - (7 * 24 * 60 * 60 * 1000),
            max: currentTimestamp,
            range: 7 * 24 * 60 * 60 * 1000
        },
        yaxis: {
            min: 0,
            tickAmount: Math.min(5, Math.max(1, Math.ceil(Math.max(...@json($newDevices ?? [0]))))),
            forceNiceScale: true,
            labels: {
                formatter: function(value) {
                    return Number.isInteger(value) ? value : '';
                },
                style: { fontSize: '12px' }
            }
        },
        markers: {
            size: 5,
            colors: ["#ffd200"],
            strokeColors: "#ffd200",
            strokeWidth: 3
        },
        responsive: [{
            breakpoint: 768,
            options: {
                chart: { height: 250 },
                xaxis: { labels: { rotate: -30 } }
            }
        }]
    };

    const trendChart = new ApexCharts(document.querySelector("#chart1"), deviceTrendOptions);
    trendChart.render();

    const modelData = @json($formattedModelData ?? ['labels' => [], 'counts' => []]);
    new ApexCharts(document.querySelector("#modelChart"), {
      chart: { height: 400, type: 'bar', animations: { enabled: true } },
      series: [{
        name: 'Count',
        data: modelData.counts
      }],
      xaxis: {
        categories: modelData.labels,
        // title: { text: 'Models' },
        labels: { style: { fontSize: '12px' } }
      },
      yaxis: {
        // title: { text: 'Count' },
        labels: { style: { fontSize: '12px' } }
      },
      plotOptions: {
        bar: {
          horizontal: true,
          barHeight: '70%',
          dataLabels: { position: 'top' }
        }
      },
      dataLabels: {
        enabled: true,
        style: { fontSize: '14px', fontWeight: 'bold' }
      },
      responsive: [{ breakpoint: 576, options: { chart: { height: 350 } } }]
    }).render();



    // Update device counter
    document.getElementById("deviceCounter").innerText = {{ $devices_count ?? 0 }};
});
</script>

@endsection