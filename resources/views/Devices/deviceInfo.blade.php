@extends('layouts.app')

@section('title', 'Device Info')

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="row align-items-center mb-3">
        <div class="col-md-6">
            <h1>Device Info</h1>
        </div>
        <div class="col-md-6 text-end">
            <div class="btn-group">
                <button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item reboot-device" href="#" data-serial-number="{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? 'Unknown' }}">Reboot</a></li>
                    <li><a class="dropdown-item reset-device" href="#" data-serial-number="{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? 'Unknown' }}">Factory Reset</a></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#pushSoftware">Push Upgrade</a></li>
                    <li><a class="dropdown-item diagnostics-button" href="#" data-bs-toggle="modal" data-bs-target="#diagnosticsModal" data-serial-number="{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? 'Unknown' }}">Run Diagnostics</a></li>
                    <li>
                        <form action="{{ route('device.delete', $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? 'Unknown') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item">Delete</button>
                        </form>
                    </li>
                </ul>
            </div>
            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#deviceLogsModal" data-device-id="{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? '' }}">Check Device Logs</button>
            <button id="checkWifiBtn" class="btn btn-secondary" disabled>Check Nearby WiFi</button>
        </div>
    </div>
    <hr>

    <!-- Device Information -->
    <div class="row">
        <!-- Device Details -->
        <div class="col-md-4">
            <table class="table table-striped">
                <tbody>
                    <tr><th>Serial Number:</th><td>{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? 'Unknown' }}</td></tr>
                    <tr><th>Device ID:</th><td>{{ $deviceData['_id'] ?? 'Unknown' }}</td></tr>
                    <tr><th>OUI:</th><td>{{ $deviceData['_deviceId']['children']['_OUI']['value'] ?? 'Unknown' }}</td></tr>
                    <tr><th>Manufacturer:</th><td>{{ $deviceData['_deviceId']['children']['_Manufacturer']['value'] ?? 'Unknown' }}</td></tr>
                    <tr><th>Product Class:</th><td>{{ $deviceData['_deviceId']['children']['_ProductClass']['value'] ?? 'Unknown' }}</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Signal Status -->
        @if(isset($signalStatus['4G']))
        <div class="col-md-4">
            <table class="table table-striped">
                <tbody>
                    @foreach ($rfValues ?? [] as $key => $value)
                        <tr><th>{{ $key }}:</th><td>{{ $value ?? 'Unknown' }}</td></tr>
                    @endforeach
                    <tr>
                        <th>4G Signal Status</th>
                        <td><strong>{{ $signalStatus['4G'] }}</strong> <span class="signal-indicator {{ strtolower($signalStatus['4G']) }}"></span></td>
                    </tr>
                    @isset($signalStatus['5G'])
                        <tr>
                            <th>5G Signal Status</th>
                            <td><strong>{{ $signalStatus['5G'] }}</strong> <span class="signal-indicator {{ strtolower($signalStatus['5G']) }}"></span></td>
                        </tr>
                    @endisset
                </tbody>
            </table>
        </div>
        @endif

        <!-- Device Image -->
        <div class="col-md-4">
            <img src="{{ asset(file_exists(public_path('assets/Devices/' . ($deviceData['_deviceId']['children']['_ProductClass']['value'] ?? '') . '.png')) ? 'assets/Devices/' . $deviceData['_deviceId']['children']['_ProductClass']['value'] . '.png' : 'assets/AVXAV Logos/default.png') }}" class="card-img-top" alt="Device Image">
        </div>
    </div>

    <!-- Heatmap and Connected Devices -->
    <div id="HeatmapRow" class="row Heatmap" style="display: none;">
        <div class="col-md-8">
            <h2>Heatmap</h2>
            @include('partials.heatmap')
        </div>
        <div class="col-md-4">
            <h2>Connected Devices</h2>
            <table class="table table-striped">
                <thead><tr><th>Device Name</th><th>RSSI</th></tr></thead>
                <tbody id="deviceTableBody"></tbody>
            </table>
        </div>
    </div>

    <!-- Device Data Tree -->
    <div class="card row mt-4">
        <div class="col-md-12 p-4">
            <h2>Device Data Tree</h2>
            <div class="d-flex gap-2 mb-3">
                <input type="text" id="search-bar" class="form-control" placeholder="Search by path, name, or value...">
                <button id="clear-search" class="btn btn-primary">Clear</button>
            </div>
            @if ($deviceData)
                @foreach ($deviceData as $key => $value)
                    @include('partials.tree-item', ['key' => $key, 'value' => $value])
                @endforeach
            @else
                <p>No device information found.</p>
            @endif
        </div>
    </div>

    <!-- Modals -->
    <!-- Set Value Modal -->
    <div class="modal fade" id="setValueModal" tabindex="-1" aria-labelledby="setValueModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="setValueModalLabel">Set New Value</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="currentValue" class="form-label">Current Value:</label>
                            <input type="text" class="form-control" id="currentValue" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="newValue" class="form-label">New Value:</label>
                            <input type="text" class="form-control" id="newValue" placeholder="Enter new value">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveValueButton">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Push Software Modal -->
    <div class="modal fade" id="pushSoftware" tabindex="-1" aria-labelledby="pushSoftwareLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pushSoftwareLabel">Software Update</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('device.pushSW') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="device_id" value="{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? '' }}">
                        <div class="mb-3">
                            <label for="swFile" class="form-label">Select Software File</label>
                            <select id="swFile" name="swFile" class="form-select" required>
                                <option value="" disabled selected>Select a software file</option>
                                @foreach ($softwareFiles as $file)
                                    <option value="{{ $file['filename'] }}">{{ $file['filename'] }} ({{ $file['metadata']['version'] }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Push Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Device Logs Modal -->
    <div class="modal fade" id="deviceLogsModal" tabindex="-1" aria-labelledby="deviceLogsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deviceLogsModalLabel">Device Logs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr><th>Username</th><th>Action</th><th>Response</th><th>Created At</th></tr>
                            </thead>
                            <tbody id="deviceLogsTableBody">
                                <tr><td colspan="4" class="text-center">No logs available.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- WiFi Modal -->
    <div class="modal" id="wifiModal" style="display: none;">
        <div class="modal-content" style="width: 600px; margin: 10% auto; background: white; padding: 20px; border-radius: 8px; position: relative;">
            <span class="close" style="position: absolute; right: 15px; top: 10px; cursor: pointer;">×</span>
            <h4>Nearby WiFi Signals</h4>
            <table class="table">
                <thead>
                    <tr><th>SSID</th><th>Channel</th></tr>
                </thead>
                <tbody id="wifiTableBody"></tbody>
            </table>
            <p id="recommendation" class="text-success fw-bold mt-3"></p>
        </div>
    </div>

    <!-- Diagnostics Modal -->
    <div class="modal fade" id="diagnosticsModal" tabindex="-1" aria-labelledby="diagnosticsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="diagnosticsModalLabel">Run Diagnostics</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="diagnostics-form">
                        <div class="mb-3">
                            <label for="diagnostics-host" class="form-label">Target IP</label>
                            <input type="text" class="form-control" id="diagnostics-host" name="host" required placeholder="Enter IP address">
                        </div>
                        <div class="mb-3">
                            <label for="diagnostics-method" class="form-label">Diagnostics Method</label>
                            <select class="form-select" id="diagnostics-method" name="method" required>
                                <option value="Ping">Ping</option>
                                <option value="Traceroute">Traceroute</option>
                            </select>
                        </div>
                    </form>
                    <div id="diagnostics-loading" class="text-center" style="display: none;">
                        <p>Running diagnostics, please wait...</p>
                    </div>
                    <div id="diagnostics-result" style="display: none;">
                        <h6>Results:</h6>
                        <pre id="diagnostics-data" class="bg-light p-3 rounded border"></pre>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="run-diagnostics-btn">Run</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9998;">
        <div class="spinner-border text-primary" style="position: absolute; top: 50%; left: 50%;"></div>
    </div>

    <!-- Popup Notification -->
    <div id="simplePopup" style="display: none; position: fixed; top: 20px; right: 20px; max-width: 300px; background: #fff; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); border-radius: 8px; padding: 20px; z-index: 9999;">
        <div id="popupMessage" style="font-size: 16px; color: #333;"></div>
    </div>
</div>
@endsection
<!-- Load CSS and JavaScript -->
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/eng/device-info.css') }}" onload="console.log('CSS loaded successfully')" onerror="console.error('Failed to load CSS')">
@endsection

@section('scripts')
    <!-- Ensure jQuery is loaded -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" onload="console.log('jQuery loaded successfully')" onerror="console.error('Failed to load jQuery')"></script>
    <script src="{{ asset('js/eng/device-info.js') }}" onload="console.log('JS loaded successfully')" onerror="console.error('Failed to load JS')"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof DeviceInfoModule === 'undefined') {
                console.error('DeviceInfoModule is not defined. Check if device-info.js is loaded correctly.');
                return;
            }
            try {
                DeviceInfoModule.init({
                    serialNumber: "{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? 'Unknown' }}",
                    csrfToken: "{{ csrf_token() }}"
                });
                console.log('DeviceInfoModule initialized successfully');
            } catch (error) {
                console.error('Failed to initialize DeviceInfoModule:', error);
            }
        });
    </script>
@endsection
