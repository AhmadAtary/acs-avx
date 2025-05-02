@extends('layouts.app')
@section('title', 'Device Info')

@section('content')
<div class="container">
    <!-- Header Section -->
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
            <button id="checkWifiBtn" class="btn btn-secondary" style="display: none;">Check Nearby WiFi</button>
        </div>
    </div>
    <hr>

    <!-- Device Info Section -->
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

        <!-- Signal Status (Conditional) -->
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
                    <tr><th>SSID</th><th>Signal</th><th>Channel</th><th>BSSID</th><th>Mode</th></tr>
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
                        <div class="spinner-border text-primary" role="status"></div>
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
        <div class="spinner-border text-primary" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);"></div>
    </div>

    <!-- Popup Notification -->
    <div id="simplePopup" style="display: none; position: fixed; top: 20px; right: 20px; max-width: 300px; background: #fff; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); border-radius: 8px; padding: 20px; z-index: 9999;">
        <div id="popupMessage" style="font-size: 16px; color: #333;"></div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .card-img-top {
        width: 100%;
        height: 200px;
        object-fit: contain;
        object-position: top;
    }
    .signal-indicator {
        display: inline-block;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        margin-left: 10px;
    }
    .strong { background-color: green; }
    .medium { background-color: orange; }
    .weak { background-color: red; }
    .unknown { background-color: gray; }
    .node-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .node-name, .node-value {
        font-family: monospace;
        font-size: 14px;
    }
    .node-name { font-weight: bold; flex-grow: 1; }
    .expand-icon {
        cursor: pointer;
        margin-right: 10px;
        font-size: 12px;
    }
    ul {
        list-style: none;
        padding-left: 20px;
    }
    ul.collapsed { display: none; }
    ul.expanded { display: block; }
    .actions button {
        margin-left: 10px;
        padding: 3px 8px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .actions .get { background-color: #007bff; color: white; }
    .actions .set { background-color: #28a745; color: white; }
    .highlight {
        background-color: yellow;
        padding: 2px 4px;
        border-radius: 3px;
    }
    #simplePopup {
        transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
        opacity: 0;
        transform: translateY(-20px);
    }
    #simplePopup.show {
        opacity: 1;
        transform: translateY(0);
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {
    const serialNumber = "{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? 'Unknown' }}";
    const csrfToken = "{{ csrf_token() }}";

    // Utility Functions
    const utils = {
        showLoading: () => document.getElementById('loadingOverlay').style.display = 'block',
        hideLoading: () => document.getElementById('loadingOverlay').style.display = 'none',
        showPopup: (message) => {
            const popup = document.getElementById('simplePopup');
            document.getElementById('popupMessage').textContent = message;
            popup.style.display = 'block';
            popup.classList.add('show');
            setTimeout(() => {
                popup.classList.remove('show');
                setTimeout(() => popup.style.display = 'none', 300);
            }, 3000);
        },
        fetchData: async (url, options = {}) => {
            utils.showLoading();
            try {
                const response = await fetch(url, {
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', ...options.headers },
                    ...options
                });
                if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
                return await response.json();
            } catch (error) {
                console.error(`Fetch error: ${error}`);
                utils.showPopup(`Error: ${error.message}`);
                throw error;
            } finally {
                utils.hideLoading();
            }
        },
        updateFieldValue: (path, value) => {
            const element = document.getElementById(path);
            if (element) {
                element.textContent = value;
                return;
            }
            const observer = new MutationObserver((_, obs) => {
                const el = document.getElementById(path);
                if (el) {
                    el.textContent = value;
                    obs.disconnect();
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }
    };



    // Device Actions
    const deviceActions = {
        getNode: async (button) => {
            const { path, type } = button.dataset;
            try {
                const data = await utils.fetchData('/device-action/get-Node', {
                    method: 'POST',
                    body: JSON.stringify({ serialNumber, path, type })
                });
                if (data.status_code === 200) {
                    utils.updateFieldValue(path, data.value);
                    utils.showPopup('Value fetched successfully.');
                } else if (data.status_code === 202) {
                    utils.showPopup('Fetch value saved as task.');
                } else {
                    utils.showPopup('Fetch failed.');
                }
            } catch {
                utils.showPopup('Error fetching value.');
            }
        },
        setNode: async (button) => {
            const { path, type, value: currentValue } = button.dataset;
            $('#setValueModal').modal('show');
            document.getElementById('currentValue').value = currentValue;
            document.getElementById('newValue').value = '';
            document.getElementById('saveValueButton').setAttribute('data-path', path);
            document.getElementById('saveValueButton').setAttribute('data-type', type);
        },
        saveNodeValue: async () => {
            const newValue = document.getElementById('newValue').value;
            const path = document.getElementById('saveValueButton').dataset.path;
            const type = document.getElementById('saveValueButton').dataset.type;

            if (!newValue) {
                utils.showPopup('Please enter a new value.');
                return;
            }

            try {
                const data = await utils.fetchData('/device-action/set-Node', {
                    method: 'POST',
                    body: JSON.stringify({ serialNumber, path, type, value: newValue })
                });
                if (data.status_code === 200) {
                    utils.updateFieldValue(path, newValue);
                    utils.showPopup('Value set successfully.');
                } else if (data.status_code === 202) {
                    utils.showPopup('Set value saved as task.');
                }
                $('#setValueModal').modal('hide');
            } catch {
                utils.showPopup('Error setting value.');
                $('#setValueModal').modal('hide');
            }
        },
        executeCommand: async (action, serialNumber) => {
            try {
                const data = await utils.fetchData(`/device-action/${action}`, {
                    method: 'POST',
                    body: JSON.stringify({ serialNumber })
                });
                utils.showPopup(data.success ? `Device ${action} request accepted.` : `${action} failed: ${data.message}`);
            } catch {
                utils.showPopup(`${action} error occurred.`);
            }
        }
    };

    // Device Logs
    const deviceLogs = {
        init: () => {
            document.getElementById('deviceLogsModal').addEventListener('show.bs.modal', async (event) => {
                const deviceId = event.relatedTarget.dataset.deviceId;
                if (!deviceId) {
                    document.getElementById('deviceLogsTableBody').innerHTML = '<tr><td colspan="4" class="text-center text-warning">Device ID not found.</td></tr>';
                    return;
                }
                await deviceLogs.fetch(deviceId);
            });
        },
        fetch: async (deviceId, page = 1) => {
            const tableBody = document.getElementById('deviceLogsTableBody');
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center">Loading...</td></tr>';

            try {
                const data = await utils.fetchData(`/device-logs/${deviceId}?page=${page}`);
                if (!data.logs || data.logs.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No logs available.</td></tr>';
                    return;
                }
                tableBody.innerHTML = data.logs.map(log => `
                    <tr>
                        <td>${log.username || 'Unknown'}</td>
                        <td>${log.action || 'N/A'}</td>
                        <td>${log.response || 'N/A'}</td>
                        <td>${log.created_at ? new Date(log.created_at).toLocaleString() : 'N/A'}</td>
                    </tr>
                `).join('');
            } catch {
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Failed to load logs.</td></tr>';
            }
        }
    };

    const heatmap = {
    init: async () => {
        const heatmapRow = document.getElementById('HeatmapRow');
        const heatmapContainer = document.getElementById('heatmap');
        const MAX_DISPLAY_RADIUS = 180;
        const MIN_DISTANCE = 30;

        try {
            const { data: devices } = await utils.fetchData(`/device/hosts/${serialNumber}`);
            if (!devices || devices.length === 0) {
                heatmapRow.style.display = 'none';
                return;
            }

            heatmapRow.style.display = 'flex';
            heatmap.createCircles(heatmapContainer);

            const totalDevices = devices.length;
            devices.forEach((device, index) => {
                const angle = (index / totalDevices) * Math.PI * 2;
                const signal = device.signalStrength || 0;
                const distance = heatmap.getDistance(signal);
                heatmap.createNode(device, angle, distance, heatmapContainer);
                heatmap.addToTable(device);
            });

            heatmap.ensureTooltip();
        } catch {
            heatmapRow.style.display = 'none';
        }
    },
    getDistance: (signal) => {
        if (signal == null || signal === 0) {
            return 30;
        } else if (signal >= -20) {
            return 60;
        } else if (signal >= -40) {
            return 90;
        } else if (signal >= -60) {
            return 120;
        } else if (signal >= -80) {
            return 150;
        } else {
            return 180;
        }
    },
    createCircles: (container) => {
        [30, 60, 90, 120, 150, 180].forEach(radius => {
            const circle = document.createElement('div');
            circle.className = 'radar-circle';
            circle.style.cssText = `width: ${radius * 2}px; height: ${radius * 2}px; left: ${250 - radius}px; top: ${250 - radius}px;`;
            container.appendChild(circle);
        });
    },
    createNode: (device, angle, distance, container) => {
        const node = document.createElement('div');
        node.className = 'device-node';
        const signal = device.signalStrength || 0;
        node.setAttribute('data-signal', signal === 0 ? 'unknown' : signal >= -30 ? 'strong' : signal >= -70 ? 'medium' : 'weak');
        node.style.cssText = `left: ${250 + Math.cos(angle) * distance - 15}px; top: ${250 + Math.sin(angle) * distance - 15}px; z-index: 10;`;
        node.innerHTML = '<i class="fa-solid fa-user"></i>';
        node.addEventListener('mouseenter', (e) => heatmap.showTooltip(e, device));
        node.addEventListener('mouseleave', heatmap.hideTooltip);
        container.appendChild(node);
    },
    addToTable: (device) => {
        const row = document.createElement('tr');
        const signalClass = device.signalStrength ? (device.signalStrength < -70 ? 'weak-signal' : 'good-signal') : '';
        row.innerHTML = `
            <td>${device.hostName || 'Unknown Device'}</td>
            <td class="${signalClass}">${device.signalStrength ? `${device.signalStrength} dBm` : 'N/A'}</td>
        `;
        document.getElementById('deviceTableBody').appendChild(row);
    },
    ensureTooltip: () => {
        let tooltip = document.getElementById('tooltip');
        if (!tooltip) {
            tooltip = document.createElement('div');
            tooltip.id = 'tooltip';
            tooltip.className = 'tooltip';
            document.body.appendChild(tooltip);
        }
        tooltip.style.cssText = `
            position: fixed; padding: 10px; background: rgba(0, 0, 0, 0.85); color: white; border-radius: 4px;
            font-size: 12px; pointer-events: none; opacity: 0; transition: opacity 0.2s; z-index: 9999;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5); max-width: 200px; word-wrap: break-word;
        `;
    },
    showTooltip: (event, device) => {
        const tooltip = document.getElementById('tooltip');
        tooltip.innerHTML = `
            <strong>${device.hostName || 'Unknown'}</strong><br>
            IP: ${device.ipAddress || 'N/A'}<br>
            MAC: ${device.macAddress || 'N/A'}<br>
            ${device.signalStrength ? `RSSI: ${device.signalStrength} dBm` : 'RSSI: N/A'}
        `;
        tooltip.style.left = `${event.clientX + 15}px`;
        tooltip.style.top = `${event.clientY - 15}px`;
        tooltip.style.opacity = '1';
    },
    hideTooltip: () => document.getElementById('tooltip').style.opacity = '0'
};

    // WiFi Signals
    const wifiSignals = {
        init: async () => {
            const checkWifiBtn = document.getElementById('checkWifiBtn');
            try {
                const wifiList = await utils.fetchData(`/wifi/standard-nodes/${serialNumber}`);
                checkWifiBtn.style.display = wifiList.length > 0 ? 'inline-block' : 'none';

                checkWifiBtn.addEventListener('click', () => wifiSignals.fetch());
            } catch {
                checkWifiBtn.style.display = 'none';
            }
        },
        fetch: async () => {
            const modal = document.getElementById('wifiModal');
            const tableBody = document.getElementById('wifiTableBody');
            const recommendation = document.getElementById('recommendation');

            try {
                const wifiList = await utils.fetchData(`/wifi/standard-nodes/${serialNumber}`);
                tableBody.innerHTML = wifiList.map(wifi => `
                    <tr>
                        <td>${wifi.SSID}</td>
                        <td>${wifi.Signal}</td>
                        <td>${wifi.Channel}</td>
                        <td>${wifi.BSSID}</td>
                        <td>${wifi.Mode}</td>
                    </tr>
                `).join('');

                if (wifiList.length > 0 && wifiList.some(w => w.Channel <= 14)) {
                    const interferenceScore = Array(12).fill(0);
                    wifiList.forEach(w => {
                        const ch = Number(w.Channel);
                        const signal = Number(w.Signal);
                        if (ch >= 1 && ch <= 11) {
                            for (let offset = -2; offset <= 2; offset++) {
                                const target = ch + offset;
                                if (target >= 1 && target <= 11) {
                                    interferenceScore[target] += (1 / (Math.abs(offset) + 1)) * Math.abs(signal);
                                }
                            }
                        }
                    });
                    const bestChannel = interferenceScore
                        .map((score, i) => ({ channel: i, score }))
                        .filter(c => c.channel >= 1 && c.channel <= 11)
                        .sort((a, b) => a.score - b.score)[0];
                    recommendation.textContent = `📶 Best 2.4GHz Channel: ${bestChannel.channel} (Score: ${bestChannel.score.toFixed(2)})`;
                } else {
                    recommendation.textContent = wifiList.length > 0 ? 'No 2.4GHz channels detected.' : 'No WiFi networks detected.';
                }

                modal.style.display = 'block';
            } catch {
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Failed to load WiFi signals.</td></tr>';
            }
        },
        setupModal: () => {
            const modal = document.getElementById('wifiModal');
            document.querySelector('.modal .close').addEventListener('click', () => modal.style.display = 'none');
            window.addEventListener('click', e => e.target === modal && (modal.style.display = 'none'));
        }
    };

    // Diagnostics
    const diagnostics = {
        init: () => {
            let selectedSerial = null;
            document.querySelectorAll('.diagnostics-button').forEach(btn => {
                btn.addEventListener('click', () => {
                    selectedSerial = btn.dataset.serialNumber;
                    document.getElementById('diagnostics-result').style.display = 'none';
                    document.getElementById('diagnostics-loading').style.display = 'none';
                    document.getElementById('diagnostics-form').reset();
                });
            });

            document.getElementById('run-diagnostics-btn').addEventListener('click', async () => {
                const host = document.getElementById('diagnostics-host').value;
                const method = document.getElementById('diagnostics-method').value;
                if (!host || !method || !selectedSerial) return;

                document.getElementById('diagnostics-loading').style.display = 'block';
                document.getElementById('diagnostics-result').style.display = 'none';

                try {
                    const data = await utils.fetchData(`/device/${selectedSerial}/diagnostics?host=${encodeURIComponent(host)}&method=${method}`);
                    document.getElementById('diagnostics-loading').style.display = 'none';
                    document.getElementById('diagnostics-result').style.display = 'block';
                    document.getElementById('diagnostics-data').textContent = JSON.stringify(data, null, 2);
                } catch {
                    document.getElementById('diagnostics-loading').style.display = 'none';
                    document.getElementById('diagnostics-result').style.display = 'block';
                    document.getElementById('diagnostics-data').textContent = 'Diagnostics failed.';
                }
            });
        }
    };

    // Initialize Components
    // treeView.init();
    // treeView.search();
    deviceLogs.init();
    heatmap.init();
    wifiSignals.init();
    wifiSignals.setupModal();
    diagnostics.init();

    // Event Listeners
    document.querySelectorAll('.get-button').forEach(btn => btn.addEventListener('click', () => deviceActions.getNode(btn)));
    document.querySelectorAll('.set-button').forEach(btn => btn.addEventListener('click', () => deviceActions.setNode(btn)));
    document.getElementById('saveValueButton').addEventListener('click', deviceActions.saveNodeValue);
    document.querySelectorAll('.reboot-device, .reset-device').forEach(btn => {
        btn.addEventListener('click', () => deviceActions.executeCommand(btn.classList.contains('reboot-device') ? 'reboot' : 'reset', btn.dataset.serialNumber));
    });
});
</script>
@endsection