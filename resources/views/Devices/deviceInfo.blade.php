@extends('layouts.app')
@section('title', 'Device Info')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <h1>Device Info</h1>
        </div>
        <div class="col-md-6 text-end">
            <div class="btn-group">
                <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    Actions
                </button>
                <ul class="dropdown-menu">
                    <li><a class="reboot-device dropdown-item" href="#" data-serial-number="{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? 'Unknown' }}">Reboot</a></li>
                    <li><a class="reset-device dropdown-item" href="#" data-serial-number="{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? 'Unknown' }}">Factory Reset</a></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#pushSoftware">Push Upgrade</a></li>
                    <li>
                        <form action="{{ route('device.delete', $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? 'Unknown') }}" method="POST" >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item">Delete</button>
                        </form>
                    </li>
                </ul>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-secondary" 
                        data-bs-toggle="modal" 
                        data-bs-target="#deviceLogsModal" 
                        data-device-id="{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? '' }}">
                    Check Device Logs
                </button>
            </div>
            <div class="btn-group">
                <button type="button" id="checkWifiBtn" class="btn btn-secondary" >
                    Check Nearby WiFi
                </button>
            </div>

        </div>
        <hr>
    </div>

    <div class="row">

                <div class="col-md-4">
                <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th>Serial Number:</th>
                                <td>{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? 'Unknown Serial Number' }}</td>
                            </tr>
                            <tr>
                                <th>Device ID:</th>
                                <td>{{ $deviceData['_id'] ?? 'Unknown' }}</td>
                            </tr>
                            <tr>
                                <th>OUI:</th>
                                <td>{{ $deviceData['_deviceId']['children']['_OUI']['value'] ?? 'Unknown' }}</td>
                            </tr>
                            <tr>
                                <th>Manufacturer:</th>
                                <td>{{ $deviceData['_deviceId']['children']['_Manufacturer']['value'] ?? 'Unknown' }}</td>
                            </tr>
                            <tr>
                                <th>Product Class:</th>
                                <td>{{ $deviceData['_deviceId']['children']['_ProductClass']['value'] ?? 'Unknown' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                {{-- Only show this column if 4G signal exists --}}
                @if(isset($signalStatus['4G']))
                <div class="col-md-4">
                    <table class="table table-striped">
                        <tbody>
                        @if ($rfValues)
                            @foreach ($rfValues as $key => $value)
                                <tr>
                                    <th>{{ $key }}:</th>
                                    <td>{{ $value ?? 'Unknown' }}</td>
                                </tr>
                            @endforeach
                        @endif

                        @if ($signalStatus)
                            <tr>
                                <th>4G Signal Status</th>
                                <td>
                                    <strong>{{ $signalStatus['4G'] }}</strong>
                                    <span class="signal-indicator {{ strtolower($signalStatus['4G']) }}"></span>
                                </td>
                            </tr>
                            @isset($signalStatus['5G'])
                                <tr>
                                    <th>5G Signal Status</th>
                                    <td>
                                        <strong>{{ $signalStatus['5G'] }}</strong>
                                        <span class="signal-indicator {{ strtolower($signalStatus['5G']) }}"></span>
                                    </td>
                                </tr>
                            @endisset
                        @endif
                        </tbody>
                    </table>
                </div>
                @endif
                <div class="col-md-4">
                <img src="{{ 
                file_exists(public_path('assets/Devices/' . $deviceData['_deviceId']['children']['_ProductClass']['value'] . '.png'))
                    ? asset('assets/Devices/' . $deviceData['_deviceId']['children']['_ProductClass']['value'] . '.png') 
                    : asset('assets/AVXAV Logos/default.png') }}" 
                class="card-img-top">
                </div>
                <div id="HeatmapRow" class="row Heatmap">
                    <div class="col-md-8">
                        <h2>Heatmap</h2>
                        @include('partials.heatmap')
                    </div>
                    <div class="col-md-4">
                        <h2>Connected Devices</h2>
                        <div class="d-flex justify-content-center">
                        <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Device Name</th>
                            <th>RSSI</th>
                        </tr>
                    </thead>
                    <tbody id="deviceTableBody">
                        <!-- Table rows will be populated dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card row mt-4">
    <div class="col-md-12 p-4">
        <h2>Device Data Tree</h2>
        <!-- Search bar -->
        <div style="display: flex; gap: 10px; margin-bottom: 10px;">
        <input 
            type="text" 
            id="search-bar" 
            placeholder="Search by path, name, or value..." 
            style="flex-grow: 1; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" 
        />
        <button 
            id="clear-search" 
            style="padding: 0.5rem 1rem; background-color: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer;"
        >
            Clear
        </button>
        </div>


        <!-- Device Tree -->
        @if ($deviceData)
            @foreach ($deviceData as $key => $value)
                @include('partials.tree-item', ['key' => $key, 'value' => $value])
            @endforeach
        @else
            <p>No device information found.</p>
        @endif
    </div>
</div>



<!-- Modal for Set Value -->
<div class="modal fade" id="setValueModal" tabindex="-1" role="dialog" aria-labelledby="setValueModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="setValueModalLabel">Set New Value</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="currentValue">Current Value:</label>
                        <input type="text" class="form-control" id="currentValue" readonly>
                    </div>
                    <div class="form-group">
                        <label for="newValue">New Value:</label>
                        <input type="text" class="form-control" id="newValue" placeholder="Enter new value">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveValueButton">Save</button>
            </div>
        </div>
    </div>
</div>

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
                    <div class="mb-3">
                        
                        <!-- Properly handle the value and ensure it is enclosed in quotes -->
                        <input type="hidden" id="device_id" name="device_id" class="form-control" 
                               value="{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] }}">
                    </div>
                    <div class="mb-3">
                        <label for="swFile" class="form-label">Select Software File</label>
                        <select id="swFile" name="swFile" class="form-select" required>
                            <option value="" disabled selected>Select a software file</option>
                            @foreach ($softwareFiles as $file)
                                <option value="{{ $file['filename'] }}">
                                    {{ $file['filename'] }} ({{ $file['metadata']['version'] }})
                                </option>
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
            <thead class="table-light">
              <tr>
                <th>Username</th>
                <th>Action</th>
                <th>Response</th>
                <th>Created At</th>
              </tr>
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



<!-- Loading Overlay -->
<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 9998;">
    <div id="loadingSpinner" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>

<!-- Simple Popup -->
<div id="simplePopup" style="display: none; position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 300px; background-color: #fff; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); border-radius: 8px; padding: 20px; font-family: Arial, sans-serif;">
    <div id="popupMessage" style="font-size: 16px; color: #333;"></div>
</div>

<!-- Wi-Fi Modal -->
<div class="modal" id="wifiModal" style="display: none;">
  <div class="modal-content" style="width: 600px; margin: 10% auto; background: white; padding: 20px; border-radius: 8px; position: relative;">
    <span class="close" style="position: absolute; right: 15px; top: 10px; cursor: pointer;">&times;</span>
    <h4>Nearby WiFi Signals</h4>
    <table class="table">
      <thead>
        <tr>
          <th>SSID</th>
          <th>Signal</th>
          <th>Channel</th>
          <th>BSSID</th>
          <th>Mode</th>
        </tr>
      </thead>
      <tbody id="wifiTableBody">
        <!-- Data will be injected here -->
      </tbody>
    </table>
    <p id="recommendation" class="text-success fw-bold mt-3"></p>
  </div>
</div>
@endsection

@section('styles')
<style>
    ul {
        list-style-type: none;
        padding-left: 20px;
    }
    li {
        margin: 5px 0;
        position: relative;
        padding: 5px 0;
    }
    .expand-icon {
        margin-right: 10px;
        font-size: 12px;
        cursor: pointer;
    }
    .node-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .node-name {
        font-family: monospace;
        font-weight: bold;
        flex-grow: 1;
    }
    .node-value {
        font-family: monospace;
        margin-left: 10px;
    }
    .actions {
        display: flex;
        align-items: center;
    }
    .actions button {
        margin-left: 10px;
        padding: 3px 8px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .actions .get {
        background-color: #007bff;
        color: white;
    }
    .actions .set {
        background-color: #28a745;
        color: white;
    }
    ul.collapsed {
        display: none;
    }
    ul.expanded {
        display: block;
    }
    .fas {
        font-size: 12px;
    }
    /* Child Node Font Size */
    ul ul .node-name {
        font-size: 14px;
    }
    ul ul .node-value {
        font-size: 14px;
    }
    /* Simple Popup Styles */
    #simplePopup {
        transition: opacity 0.3s ease-in-out;
        opacity: 0;
        transform: translateY(-20px);
    }
    #simplePopup.show {
        opacity: 1;
        transform: translateY(0);
    }
    
    .card-img-top {
        width: 100%; /* Ensures the image fits the card width */
        height: 200px; /* Set a fixed height for consistency across cards */
        object-fit: contain; /* Maintains the aspect ratio and prevents distortion */
        /* background-color: #f8f9fa; Optional: Adds a light background to make smaller images look centered */
        object-position: top;
    }

    .highlight {
        background-color: yellow;
        padding: 2px 4px;
        border-radius: 3px;
    }
    /* ul.collapsed {
        display: none;
    }
    ul {
        list-style-type: none;
        padding-left: 20px;
    } */
    .expand-icon {
        cursor: pointer;
        margin-right: 5px;
    }

    .signal-indicator {
        display: inline-block;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        margin-left: 10px;
    }

    .strong {
        background-color: green;
    }

    .medium {
        background-color: orange;
    }

    .weak {
        background-color: red;
    }

    .unknown {
        background-color: gray;
    }
</style>
@endsection


@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // =============================================
        //  CORE UTILITY FUNCTIONS
        // =============================================
        
        /**
         * Show/hide loading overlay
         */
        function showLoadingOverlay() {
            document.getElementById('loadingOverlay').style.display = 'block';
        }
        function hideLoadingOverlay() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }

        /**
         * Simple popup notification system
         */
        function showSimplePopup(message) {
            const popup = document.getElementById('simplePopup');
            const popupMessage = document.getElementById('popupMessage');
            popupMessage.textContent = message;
            popup.style.display = 'block';
            popup.style.opacity = '1';
            popup.style.transform = 'translateY(0)';
            setTimeout(hideSimplePopup, 3000);
        }
        function hideSimplePopup() {
            const popup = document.getElementById('simplePopup');
            popup.style.opacity = '0';
            popup.style.transform = 'translateY(-20px)';
            setTimeout(() => popup.style.display = 'none', 300);
        }

        // =============================================
        //  TREE VIEW MANAGEMENT
        // =============================================

        /** Initialize expand/collapse functionality */
        function initializeTreeView() {
            document.querySelectorAll(".expand-icon").forEach(toggle => {
                toggle.addEventListener("click", function() {
                    const parentLi = this.closest("li");
                    const childUl = parentLi.querySelector("ul");
                    if (childUl) {
                        childUl.classList.toggle("collapsed");
                        childUl.classList.toggle("expanded");
                        this.textContent = childUl.classList.contains("expanded") ? "▼" : "▶";
                    }
                });
            });
        }

        /** Update field value in UI with MutationObserver fallback */
        function updateFieldValue(path, value) {
            const fieldElement = document.getElementById(path);
            if (fieldElement) {
                fieldElement.textContent = value;
                return;
            }

            const observer = new MutationObserver((mutations, observer) => {
                const element = document.getElementById(path);
                if (element) {
                    element.textContent = value;
                    observer.disconnect();
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }

        // =============================================
        //  DEVICE DATA ACTIONS
        // =============================================

        /** Handle GET requests for node values */
        function handleGetButton(button) {
            const path = button.dataset.path;
            const type = button.dataset.type;
            const serialNumber = "{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? 'Unknown' }}";

            showLoadingOverlay();
            fetch('/device-action/get-Node', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({ serialNumber, path, type })
            })
            .then(response => {
                hideLoadingOverlay();
                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.status_code === 200) {
                    updateFieldValue(path, data.value);
                    showSimplePopup('Value fetched successfully.');
                } else if (data.status_code === 202) {
                    showSimplePopup('Fetch value saved as task.');
                } else {
                    showSimplePopup('Fetch failed.');
                }
            })
            .catch(error => {
                hideLoadingOverlay();
                console.error('Fetch error:', error);
                showSimplePopup('Error fetching value.');
            });
        }

        /** Handle SET requests for node values */
        function handleSetValue(button) {
            const path = button.dataset.path;
            const type = button.dataset.type;
            const currentValue = button.dataset.value;
            const serialNumber = "{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? 'Unknown' }}";

            // Show modal with current and new value inputs
            $('#setValueModal').modal('show');
            document.getElementById('setValueModalLabel').textContent = 'Set New Value';
            document.getElementById('currentValue').value = currentValue;
            document.getElementById('newValue').value = ''; // Clear new value input
            document.getElementById('saveValueButton').setAttribute('data-path', path);
            document.getElementById('saveValueButton').setAttribute('data-type', type);
            document.getElementById('saveValueButton').setAttribute('data-serial-number', serialNumber);
        }

        // Save the new value from the modal
        document.getElementById('saveValueButton').addEventListener('click', function () {
            const newValue = document.getElementById('newValue').value;
            const path = this.getAttribute('data-path');
            const type = this.getAttribute('data-type');
            const serialNumber = this.getAttribute('data-serial-number');

            if (!newValue) {
                alert('Please enter a new value.');
                return;
            }

            showLoadingOverlay();

            fetch('/device-action/set-Node', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ serialNumber, path, type, value: newValue })
            })
            .then(response => {
                hideLoadingOverlay();
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.status_code === 200) {
                    showSimplePopup(`Value set successfully`);
                    updateFieldValue(path, newValue);
                } else if (data.status_code === 202) {
                    showSimplePopup(`Set value saved as a task`);
                }
            })
            .catch(error => {
                hideLoadingOverlay();
                console.error('Error setting value:', error);
                showSimplePopup('An error occurred while setting the value.');
            })
            .finally(() => {
                $('#setValueModal').modal('hide');
            });
        });

        // =============================================
        //  DEVICE COMMANDS (REBOOT/RESET)
        // =============================================

        /** Generic device command handler */
        function handleDeviceCommand(action, serialNumber) {
            showLoadingOverlay();
            fetch(`/device-action/${action}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({ serialNumber })
            })
            .then(response => {
                hideLoadingOverlay();
                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showSimplePopup(`Device ${action} request accepted.`);
                } else {
                    showSimplePopup(`${action} failed: ${data.message}`);
                }
            })
            .catch(error => {
                hideLoadingOverlay();
                console.error(`${action} error:`, error);
                showSimplePopup(`${action} error occurred.`);
            });
        }

        // Attach command handlers
        document.querySelectorAll(".reboot-device, .reset-device").forEach(button => {
            button.addEventListener("click", function() {
                const action = this.classList.contains('reboot-device') ? 'reboot' : 'reset';
                handleDeviceCommand(action, this.dataset.serialNumber);
            });
        });

        // =============================================
        //  SEARCH FUNCTIONALITY
        // =============================================

        /** Initialize search functionality */
        function initializeSearch() {
            const searchBar = document.getElementById("search-bar");
            const clearButton = document.getElementById("clear-search");
            const treeItems = document.querySelectorAll(".node-content");

            if (!searchBar || !clearButton) {
                console.warn("Search bar or clear button not found.");
                return;
            }

            // Search input event
            searchBar.addEventListener("input", function () {
                const query = searchBar.value.trim().toLowerCase();

                resetTree();
                if (!query) return;

                let found = false;

                treeItems.forEach(item => {
                    const valueElement = item.querySelector(".node-value");
                    const nameElement = item.querySelector(".node-name");

                    const nodePath = valueElement?.id?.toLowerCase() || "";
                    const nodeName = nameElement?.textContent?.toLowerCase() || "";
                    const nodeValue = valueElement?.textContent?.toLowerCase() || "";

                    if (nodePath.includes(query) || nodeName.includes(query) || nodeValue.includes(query)) {
                        found = true;

                        // Highlight matched element
                        if (nodePath.includes(query)) {
                            valueElement?.classList.add("highlight");
                        } else if (nodeName.includes(query)) {
                            nameElement?.classList.add("highlight");
                        } else if (nodeValue.includes(query)) {
                            valueElement?.classList.add("highlight");
                        }

                        // Expand all parent uls
                        let parent = item.closest("ul");
                        while (parent) {
                            parent.classList.remove("collapsed");
                            parent = parent.parentElement.closest("ul");
                        }
                    }
                });

                if (!found) {
                    console.log("No matching nodes found.");
                }
            });

            // Clear button event
            clearButton.addEventListener("click", function () {
                searchBar.value = "";
                resetTree();
            });

            // Reset highlights and collapse all
            function resetTree() {
                document.querySelectorAll(".highlight").forEach(el => el.classList.remove("highlight"));
                document.querySelectorAll("ul").forEach(ul => ul.classList.add("collapsed"));
            }
        }


        /** Highlight matching elements */
        function highlightMatches(item, query, path, name, value) {
            const valueElement = item.querySelector(".node-value");
            const nameElement = item.querySelector(".node-name");
            
            if (path.includes(query)) valueElement?.classList.add("highlight");
            else if (name.includes(query)) nameElement?.classList.add("highlight");
            else if (value.includes(query)) valueElement?.classList.add("highlight");
        }

        /** Expand parent nodes of matches */
        function expandParentNodes(item) {
            let parent = item.closest("ul");
            while (parent) {
                parent.classList.remove("collapsed");
                parent = parent.parentElement.closest("ul");
            }
        }

        /** Reset search state */
        function resetTree() {
            document.querySelectorAll(".highlight").forEach(el => el.classList.remove("highlight"));
            document.querySelectorAll("ul").forEach(ul => ul.classList.add("collapsed"));
        }

        // =============================================
        //  INITIALIZATION
        // =============================================
        
        // Attach core event listeners
        document.querySelectorAll(".get-button").forEach(btn => 
            btn.addEventListener('click', () => handleGetButton(btn)));
        document.querySelectorAll(".set-button").forEach(btn => 
            btn.addEventListener('click', () => handleSetValue(btn)));

        // Initialize components
        initializeTreeView();
        initializeSearch();
    });
</script>

<!-- Device Logs Script -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const logsModal = document.getElementById('deviceLogsModal');

        // Extract device ID from data attribute instead of blade
        logsModal?.addEventListener('show.bs.modal', (event) => {
            const button = event.relatedTarget;
            const deviceId = button?.getAttribute('data-device-id');

            console.log('Opening logs modal for device:', deviceId);

            if (deviceId) {
                fetchDeviceLogs(deviceId);
            } else {
                console.warn('No device ID provided for logs modal.');
                document.getElementById('deviceLogsTableBody').innerHTML =
                    '<tr><td colspan="4" class="text-center text-warning">Device ID not found.</td></tr>';
            }
        });

        function fetchDeviceLogs(deviceId, page = 1) {
            console.log(`Fetching logs for Device ID: ${deviceId}, Page: ${page}`);

            const tableBody = document.getElementById('deviceLogsTableBody');
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center">Loading...</td></tr>';

            fetch(`/device-logs/${deviceId}?page=${page}`)
                .then(response => {
                    console.log('Response Status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('API Response Data:', data);
                    if (!data.logs || data.logs.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No logs available for this device.</td></tr>';
                        return;
                    }

                    tableBody.innerHTML = '';
                    data.logs.forEach(log => {
                        const createdAt = log.created_at ? new Date(log.created_at).toLocaleString() : 'N/A';
                        const row = `<tr>
                            <td>${log.username || 'Unknown'}</td>
                            <td>${log.action || 'N/A'}</td>
                            <td>${log.response || 'N/A'}</td>
                            <td>${createdAt}</td>
                        </tr>`;
                        tableBody.innerHTML += row;
                    });
                })
                .catch(error => {
                    console.error('Error fetching logs:', error);
                    tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Failed to load logs.</td></tr>';
                });
        }
    });
</script>


<!-- Heatmap Script -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const heatmapContainer = document.getElementById("heatmap");
        const heatmapRow = document.getElementById("HeatmapRow");
        const serialNumber = "{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] }}";

        async function initializeHeatmap() {
            try {
                const response = await fetch(`/device/hosts/${serialNumber}`);
                const { data: devices } = await response.json();
                
                console.log("Devices data:", devices);
                if (!heatmapContainer || !heatmapRow) {
                    console.error("Heatmap container or row not found.");
                    return;
                }
                if (!devices || devices.length === 0) {
                    console.warn("No devices found for heatmap.");
                    heatmapRow.style.display = "none";
                    return;
                }

                createRadarCircles();
                devices.forEach((device, index) => {
                    createDeviceNode(device, index, devices.length);
                    addDeviceToTable(device);
                });
            } catch (error) {
                console.error("Heatmap error:", error);
                if (heatmapRow) {
                    heatmapRow.style.display = "none";
                }
            }
        }

        function createRadarCircles() {
            [30, 60, 90, 120, 150, 200].forEach(radius => {
                const circle = document.createElement("div");
                circle.className = "radar-circle";
                circle.style.cssText = `
                    width: ${radius * 2}px;
                    height: ${radius * 2}px;
                    left: ${250 - radius}px;
                    top: ${250 - radius}px;
                `;
                heatmapContainer.appendChild(circle);
            });
        }

        function createDeviceNode(device, index, totalDevices) {
            const angle = (index / totalDevices) * Math.PI * 2;
            const distance = device.signalStrength ? 200 - (device.signalStrength * 2) : 40;
            const node = document.createElement("div");
            
            node.className = "device-node";
            node.style.cssText = `
                left: ${250 + Math.cos(angle) * distance - 15}px;
                top: ${250 + Math.sin(angle) * distance - 15}px;
            `;
            
            const icon = document.createElement("i");
            icon.className = "fa-solid fa-user";
            icon.style.color = device.signalStrength ? 'white' : 'lightblue';
            node.appendChild(icon);

            // Tooltip functionality
            node.addEventListener("mouseenter", () => showDeviceTooltip(device, node));
            node.addEventListener("mouseleave", () => 
                document.getElementById("tooltip").style.opacity = 0);

            heatmapContainer.appendChild(node);
        }

        function showDeviceTooltip(device, node) {
            const tooltip = document.getElementById("tooltip");
            const rect = node.getBoundingClientRect();
            
            tooltip.innerHTML = `
                <strong>${device.hostName || "Unknown"}</strong><br>
                IP: ${device.ipAddress || "N/A"}<br>
                MAC: ${device.macAddress || "N/A"}<br>
                ${device.signalStrength ? `RSSI: -${device.signalStrength} dBm` : 'N/A'}
            `;
            tooltip.style.cssText = `
                opacity: 1;
                left: ${rect.right + 10}px;
                top: ${rect.top}px;
            `;
        }

        function addDeviceToTable(device) {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${device.hostName}</td>
                <td>${device.signalStrength ? `${device.signalStrength} dBm` : 'N/A'}</td>
            `;
            document.getElementById("deviceTableBody").appendChild(row);
        }

        initializeHeatmap();
    });
</script>

<!-- Wifi Signal Script -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("wifiModal");
    const closeBtn = document.querySelector(".modal .close");
    const tableBody = document.getElementById("wifiTableBody");
    const recommendation = document.getElementById("recommendation");
    const serialNumber = "{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] }}";

    document.getElementById("checkWifiBtn").addEventListener("click", async function () {
        console.log("Checking WiFi...");
        try {
            const response = await fetch(`/wifi/standard-nodes/${serialNumber}`);
            const wifiList = await response.json();

            // Clear table
            tableBody.innerHTML = '';

            // Fill table
            wifiList.forEach(wifi => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${wifi.SSID}</td>
                    <td>${wifi.Signal}</td>
                    <td>${wifi.Channel}</td>
                    <td>${wifi.BSSID}</td>
                    <td>${wifi.Mode}</td>
                `;
                tableBody.appendChild(row);
            });

            // Smart 2.4GHz best channel suggestion
            const is24GHz = wifiList.some(wifi => wifi.Channel <= 14); // crude check
            if (wifiList.length > 0 && is24GHz) {
                const interferenceScore = {};

                // Initialize scores for channels 1–11
                for (let ch = 1; ch <= 11; ch++) {
                    interferenceScore[ch] = 0;
                }

                // Function to calculate influence of one signal on a channel
                function addInterference(center, signal) {
                    // Affect center ±2 (overlapping)
                    for (let offset = -2; offset <= 2; offset++) {
                        const ch = center + offset;
                        if (ch >= 1 && ch <= 11) {
                            // Add weighted signal (stronger = worse)
                            const weight = 1 / (Math.abs(offset) + 1);
                            interferenceScore[ch] += weight * Math.abs(Number(signal));
                        }
                    }
                }

                wifiList.forEach(wifi => {
                    const ch = Number(wifi.Channel);
                    const signal = Number(wifi.Signal);
                    if (ch >= 1 && ch <= 11) {
                        addInterference(ch, signal);
                    }
                });

                // Pick channel with lowest interference
                const bestChannel = Object.entries(interferenceScore).sort((a, b) => a[1] - b[1])[0];
                recommendation.textContent = `📶 Best 2.4GHz Channel: ${bestChannel[0]} (Lowest Interference Score: ${bestChannel[1].toFixed(2)})`;
            } else if (wifiList.length > 0) {
                recommendation.textContent = "WiFi channels detected are not in the 2.4GHz range.";
            } else {
                recommendation.textContent = "No nearby WiFi networks detected.";
            }

            // Show modal
            modal.style.display = "block";
        } catch (error) {
            console.error("Error fetching WiFi signals:", error);
        }
    });

    // Close modal
    closeBtn?.addEventListener("click", () => {
        modal.style.display = "none";
    });

    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
});
</script>
<script>

    const checkWifiBtn = document.getElementById("checkWifiBtn");
const serialNumber = "{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? '' }}";

// You can optionally hide the button on load
checkWifiBtn.style.display = "none";

// Check if WiFi data exists before enabling button
async function checkWifiAvailability() {
    try {
        const response = await fetch(`/wifi/standard-nodes/${serialNumber}`);
        const wifiList = await response.json();

        if (wifiList.length > 0) {
            checkWifiBtn.style.display = "inline-block"; // or "block" if full width
        } else {
            checkWifiBtn.style.display = "none";
        }
    } catch (error) {
        console.error("Failed to fetch WiFi signals:", error);
        checkWifiBtn.style.display = "none";
    }
}

// Call the function on page load
checkWifiAvailability();

</script>
@endsection