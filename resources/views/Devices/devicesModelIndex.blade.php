@extends('layouts.app')

@section('title', "AVXAV ACS | Devices - Model: {$model}")

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
    <div class="breadcrumb-title pe-3">Devices - Model: {{ $model }}</div>
</div>

<div class="row">
    <!-- Chart Card -->
    <div class="col-12">
        <div class="card" style="max-height: 350px; padding: 15px;">
            <canvas id="softwareVersionChart" style="width: 100%; height: 100%;"></canvas>
        </div>
    </div>

    <!-- Device Table Section -->
    <div class="col-12 mt-4">
        <div class="card">
            <div class="card-body">
                <!-- Search Bar -->
                <div class="mb-3 d-flex gap-2">
                    <select id="searchType" class="form-select w-auto">
                        <option value="_deviceId._SerialNumber">Serial Number</option>
                        <option value="_deviceId._Manufacturer">Manufacturer</option>
                        <option value="_deviceId._OUI">OUI</option>
                        <option value="_deviceId._ProductClass">Product Class</option>
                    </select>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                    <button id="clearButton" class="btn btn-secondary">Clear</button>
                </div>

                <!-- Devices Table -->
                <div class="table-responsive">
                    <table id="devicesTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Serial Number</th>
                                <th>Manufacturer</th>
                                <th>OUI</th>
                                <th>Product Class</th>
                                <th>Software Version</th>
                                <th>Up Time</th>
                                <th>Last Inform</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($devices as $device)
                        <tr>
                          <td>
                            <a href="{{ route('device.info', ['serialNumber' => $device->_deviceId['_SerialNumber']]) }}" class="btn btn-link text-decoration-none">
                              {{ $device->_deviceId['_SerialNumber'] ?? 'N/A' }}
                            </a>
                          </td>
                          <td>{{ $device->_deviceId['_Manufacturer'] ?? 'N/A' }}</td>
                          <td>{{ $device->_deviceId['_OUI'] ?? 'N/A' }}</td>
                          <td>{{ $device->_deviceId['_ProductClass'] ?? 'N/A' }}</td>
                          <td>{{ $device->InternetGatewayDevice['DeviceInfo']['SoftwareVersion']['_value'] ?? 'N/A' }}</td>
                          <td>
                            @php
                                $uptimeSeconds = $device->InternetGatewayDevice['DeviceInfo']['UpTime']['_value'] ?? null;
                                if ($uptimeSeconds) {
                                    $hours = floor($uptimeSeconds / 3600);
                                    $minutes = floor(($uptimeSeconds % 3600) / 60);
                                    echo "{$hours}h {$minutes}m";
                                } else {
                                    echo '0h 1m';
                                }
                            @endphp
                          </td>
                          <td>{{ $device->_lastInform ?? 'N/A' }}</td>
                        </tr>
                      @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div id="paginationContainer" class="d-flex mt-3 justify-content-end gap-2">
                    {{ $devices->links('vendor.pagination.bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    let chartInstance = null;

    const ctx = document.getElementById('softwareVersionChart').getContext('2d');
    
    // Create gradient for bars
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, '#4facfe');
    gradient.addColorStop(1, '#00f2fe');

    chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($formattedSoftwareData['labels'] ?? []),
            datasets: [{
                data: @json($formattedSoftwareData['counts'] ?? []),
                backgroundColor: gradient,
                borderColor: '#ffffff',
                borderWidth: 1,
                borderRadius: 5,
  barThickness: 20
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#2d3748',
                    titleFont: { size: 14, family: 'Arial' },
                    bodyFont: { size: 12, family: 'Arial' },
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || 'Unknown';
                            const value = context.raw || 0;
                            return `Version ${label}: ${value} devices`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: { size: 12, family: 'Arial' }
                    },
                    grid: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Number of Devices',
                        font: { size: 14, family: 'Arial' }
                    }
                },
                y: {
                    ticks: {
                        font: { size: 12, family: 'Arial' }
                    },
                    grid: {
                        display: false
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });

    // Search logic
    const searchInput = document.getElementById("searchInput");
    const searchType = document.getElementById("searchType");
    const clearButton = document.getElementById("clearButton");
    const tableBody = document.querySelector("#devicesTable tbody");
    const paginationContainer = document.getElementById("paginationContainer");

    async function fetchDevices(query = "", type = "_deviceId._SerialNumber") {
        try {
            const response = await fetch(`/devices/search/model?model={{ $model }}&type=${encodeURIComponent(type)}&query=${encodeURIComponent(query)}`);
            const data = await response.json();

            if (data.devices && data.devices.data) {
                tableBody.innerHTML = "";
                data.devices.data.forEach(device => {
                    const row = tableBody.insertRow();
                    row.innerHTML = `
                        <td><a href="/device-info/${device._deviceId._SerialNumber}" class="btn btn-link text-decoration-none">${device._deviceId._SerialNumber || "N/A"}</a></td>
                        <td>${device._deviceId._Manufacturer || "N/A"}</td>
                        <td>${device._deviceId._OUI || "N/A"}</td>
                        <td>${device._deviceId._ProductClass || "N/A"}</td>
                        <td>${device.InternetGatewayDevice?.DeviceInfo?.SoftwareVersion?._value || "N/A"}</td>
                        <td>
                            ${(() => {
                                const uptimeSeconds = device.InternetGatewayDevice?.DeviceInfo?.UpTime?._value || null;
                                if (uptimeSeconds) {
                                    const hours = Math.floor(uptimeSeconds / 3600);
                                    const minutes = Math.floor((uptimeSeconds % 3600) / 60);
                                    return `${hours}h ${minutes}m`;
                                } else {
                                    return "0h 1m";
                                }
                            })()}
                        </td>
                        <td>${device._lastInform || "N/A"}</td>
                    `;
                });

                paginationContainer.innerHTML = "";
                if (data.devices.links) {
                    data.devices.links.forEach(link => {
                        const paginationLink = document.createElement("a");
                        paginationLink.href = link.url || "#";
                        paginationLink.className = `btn btn-${link.active ? "primary" : "outline-secondary"} mx-1 pagination-btn`;
                        paginationLink.innerHTML = link.label;
                        paginationLink.addEventListener("click", function (e) {
                            e.preventDefault();
                            if (link.url) {
                                fetchDevices(searchInput.value, searchType.value);
                            }
                        });
                        paginationContainer.appendChild(paginationLink);
                    });
                }
            } else {
                tableBody.innerHTML = '<tr><td colspan="7" class="text-center">No devices found</td></tr>';
            }
        } catch (error) {
            console.error("Error fetching devices:", error);
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center">Error loading devices</td></tr>';
        }
    }

    function handleSearch() {
        fetchDevices(searchInput.value.trim(), searchType.value);
    }

    function clearSearch() {
        searchInput.value = "";
        searchType.value = "_deviceId._SerialNumber";
        fetchDevices();
        searchInput.focus();
    }

    searchInput.addEventListener("keyup", handleSearch);
    clearButton.addEventListener("click", clearSearch);
    searchType.addEventListener("change", handleSearch);

    fetchDevices(); // Initial load
});
</script>

<style>
.table-hover tbody tr:hover {
    background-color: #f1f5f9;
    transition: background-color 0.3s;
}
.pagination-btn:hover {
    background-color: #007bff;
    color: #fff;
    transition: all 0.3s;
}
.card {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}
</style>
@endsection