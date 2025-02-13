@extends('layouts.app')
@section('title', 'Device Info')

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="row mb-3">
        <div class="col-md-6">
            <h1>Device Info</h1>
        </div>
        <div class="col-md-6 text-end">
            <div class="btn-group">
                <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    Actions
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="reboot-device dropdown-item" href="#" data-serial-number="{{ $device['_deviceId']['_SerialNumber']['value'] ?? $device['_deviceId']['_SerialNumber'] ?? 'Unknown' }}">Reboot</a>
                    </li>
                    <li>
                        <a class="reset-device dropdown-item" href="#" data-serial-number="{{ $device['_deviceId']['_SerialNumber']['value'] ?? $device['_deviceId']['_SerialNumber'] ?? 'Unknown' }}">Factory Reset</a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#pushSoftware">Push Upgrade</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Device Info Section -->
    <div class="row">
        <div class="col-md-6">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>Serial Number</th>
                        <td>{{ $device['_deviceId']['_SerialNumber']['value'] ?? $device['_deviceId']['_SerialNumber'] ?? 'Unknown Serial Number' }}</td>
                    </tr>
                    <tr>
                        <th>Device ID</th>
                        <td>{{ $device['_id'] ?? 'Unknown' }}</td>
                    </tr>
                    <tr>
                        <th>OUI</th>
                        <td>{{ $device['_deviceId']['_OUI']['value'] ?? $device['_deviceId']['_OUI'] ?? 'Unknown' }}</td>
                    </tr>
                    <tr>
                        <th>Manufacturer</th>
                        <td>{{ $device['_deviceId']['_Manufacturer']['value'] ?? $device['_deviceId']['_Manufacturer'] ?? 'Unknown' }}</td>
                    </tr>
                    <tr>
                        <th>Product Class</th>
                        <td>{{ $device['_deviceId']['_ProductClass']['value'] ?? $device['_deviceId']['_ProductClass'] ?? 'Unknown' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <img src="{{ asset('assets/Devices/' . ($device['_deviceId']['_ProductClass']['value'] ?? $device['_deviceId']['_ProductClass'] ?? 'default') . '.png') }}" 
                 class="card-img-top" alt="Device Image">
        </div>
    </div>

    <!-- Tabs Section -->
    <div class="row mt-4">
        <div class="col-12">
            <ul class="nav nav-tabs" id="nodeTabs" role="tablist">
                @foreach ($uniqueNodeTypes as $index => $type)
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $index == 0 ? 'active' : '' }}" id="{{ Str::slug($type) }}-tab"
                           data-bs-toggle="tab" href="#{{ Str::slug($type) }}" role="tab"
                           aria-controls="{{ Str::slug($type) }}" aria-selected="{{ $index == 0 ? 'true' : 'false' }}">
                            {{ $type }}
                        </a>
                    </li>
                @endforeach
                
            </ul>

            <div class="tab-content mt-3" id="nodeTabsContent">
                @foreach ($uniqueNodeTypes as $index => $type)
                    <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="{{ Str::slug($type) }}" role="tabpanel" aria-labelledby="{{ Str::slug($type) }}-tab">
                        <div class="card">
                            <div class="card-body">
                                <form method="POST" action="">
                                    @csrf
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Node</th>
                                                <th>Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($nodes as $node)
                                                @if ($node->Type == $type)
                                                    <tr>
                                                        <td>{{ $node->Name }}</td>
                                                        <td>
                                                            @if (isset($nodeValues[$node->Name]['value']))
                                                                @if ($nodeValues[$node->Name]['nodeMode'])
                                                                    <input type="text" name="nodes[{{ $node->Name }}][value]" 
                                                                           value="{{ $nodeValues[$node->Name]['value'] }}" class="form-control">
                                                                @else
                                                                    <input type="hidden" name="nodes[{{ $node->Name }}][value]" 
                                                                           value="{{ $nodeValues[$node->Name]['value'] }}">
                                                                    {{ $nodeValues[$node->Name]['value'] }}
                                                                @endif
                                                            @else
                                                                No value found.
                                                            @endif
                                                        </td>
                                                        <input type="hidden" name="url_Id" value="{{ $url_Id }}">
                                                        <input type="hidden" name="device_id" value="{{ $device->_id }}">
                                                        <input type="hidden" name="nodes[{{ $node->Name }}][key]" value="{{ $nodeValues[$node->Name]['path'] }}">
                                                        <input type="hidden" name="nodes[{{ $node->Name }}][mode]" value="{{ $nodeValues[$node->Name]['nodeMode'] }}">
                                                        <input type="hidden" name="nodes[{{ $node->Name }}][nodeType]" value="{{ $nodeValues[$node->Name]['nodeValueType'] }}">
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <button type="submit" name="action" value="GET" class="btn btn-primary">Get</button>
                                    @if ($type != 'RF')
                                        <button type="submit" name="action" value="SET" class="btn btn-success">Set</button>
                                    @endif
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="tab-pane fade" id="notes" role="tabpanel" aria-labelledby="notes-tab">
                    <div class="card">
                        <div class="card-body">
                            <h5>Customer Notes</h5>
                            <!-- Add your notes UI here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Section -->
<div class="modal fade" id="setValueModal" tabindex="-1" role="dialog" aria-labelledby="setValueModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="setValueModalLabel">Set Node Value</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="setValueForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="currentNodePath" class="form-label">Node Path</label>
                        <input type="text" id="currentNodePath" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="newNodeValue" class="form-label">New Value</label>
                        <input type="text" id="newNodeValue" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
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
</style>
@endsection

@section('scripts')
<script>
        document.addEventListener("DOMContentLoaded", function () {
            // Function to show the loading overlay
            function showLoadingOverlay() {
                document.getElementById('loadingOverlay').style.display = 'block';
            }

            // Function to hide the loading overlay
            function hideLoadingOverlay() {
                document.getElementById('loadingOverlay').style.display = 'none';
            }

            // Function to show the popup with a message
            function showSimplePopup(message) {
                const popup = document.getElementById('simplePopup');
                const popupMessage = document.getElementById('popupMessage');

                popupMessage.textContent = message;
                popup.style.display = 'block';
                popup.style.opacity = '1';
                popup.style.transform = 'translateY(0)';

                // Hide the popup after 3 seconds
                setTimeout(hideSimplePopup, 3000);
            }

            // Function to hide the popup
            function hideSimplePopup() {
                const popup = document.getElementById('simplePopup');
                popup.style.opacity = '0';
                popup.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    popup.style.display = 'none';
                }, 300); // Matches CSS transition duration
            }

            // Expand/Collapse functionality for tree items
            const toggles = document.querySelectorAll(".expand-icon");
            toggles.forEach((toggle) => {
                toggle.addEventListener("click", function () {
                    const parentLi = this.closest("li");
                    const childUl = parentLi.querySelector("ul");
                    if (childUl) {
                        childUl.classList.toggle("collapsed");
                        childUl.classList.toggle("expanded");
                        this.textContent = childUl.classList.contains("expanded") ? "▼" : "▶";
                    }
                });
            });

            // Function to handle fetching node data (GET action)
            function handleGetButton(button) {
            const path = button.dataset.path; // The path to the field being fetched
            const type = button.dataset.type; // The type of the field
            const serialNumber = "{{ $device['_deviceId']['_SerialNumber']['value'] ?? $device['_deviceId']['_SerialNumber'] }}";

            showLoadingOverlay();

            fetch('/device-action/get-Node', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ serialNumber, path, type }) // Send the required data
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
                    const value = data.value; // The fetched value from the server
                    updateFieldValue(path, value); // Update the UI with the new value
                    showSimplePopup('Value fetched successfully.');
                } else if (data.status_code === 202) {
                    showSimplePopup('Fetch value saved as a task.');
                } else {
                    showSimplePopup('Failed to fetch the value.');
                }
            })
            .catch(error => {
                hideLoadingOverlay();
                console.error('Error fetching value:', error);
                showSimplePopup('An error occurred while fetching the value.');
            });
        }

        /**
         * Update the field value in the UI based on the path.
         *
         * @param {string} path - The dot-separated path of the field to update.
         * @param {string|number} value - The new value to display.
         */
        function updateFieldValue(path, value) {

            // Check if the element already exists
            const fieldElement = document.getElementById(path);


            // it's return null
            
            if (fieldElement) {
                fieldElement.textContent = value;
                console.log(`Field "${path}" updated successfully.`);
                return;
            }

            // If the element doesn't exist, set up a MutationObserver to watch for its creation
            const observer = new MutationObserver((mutationsList, observer) => {
                const fieldElement = document.getElementById(path);
                if (fieldElement) {
                    fieldElement.textContent = value;
                    console.log(`Field "${path}" updated successfully.`);
                    observer.disconnect(); // Stop observing once the element is found
                }
            });

            // Start observing the entire document for child node additions
            observer.observe(document.body, { childList: true, subtree: true });
        }


        // Function to handle setting a new value
        function handleSetValue(button) {
            const path = button.dataset.path;
            const type = button.dataset.type;
            const currentValue = button.dataset.value;
            const serialNumber = "{{ $device['_deviceId']['_SerialNumber']['value'] ?? $device['_deviceId']['_SerialNumber'] }}";

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

        // Handle reboot action
        document.querySelectorAll(".reboot-device").forEach((button) => {
            button.addEventListener("click", function () {
                const serialNumber = this.getAttribute('data-serial-number');

                showLoadingOverlay();

                fetch('/device-action/reboot', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ serialNumber })
                })
                .then(response => {
                    hideLoadingOverlay();
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showSimplePopup('Device reboot request accepted.');
                    } else {
                        showSimplePopup(`Failed to reboot device: ${data.message}`);
                    }
                })
                .catch(error => {
                    hideLoadingOverlay();
                    console.error('Error rebooting device:', error);
                    showSimplePopup('An error occurred while rebooting the device.');
                });
            });
        });

        // Handle reset action
        document.querySelectorAll(".reset-device").forEach((button) => {
            button.addEventListener("click", function () {
                const serialNumber = this.getAttribute('data-serial-number');

                showLoadingOverlay();

                fetch('/device-action/reset', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ serialNumber })
                })
                .then(response => {
                    hideLoadingOverlay();
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showSimplePopup('Device reset request accepted.');
                    } else {
                        showSimplePopup(`Failed to reset device: ${data.message}`);
                    }
                })
                .catch(error => {
                    hideLoadingOverlay();
                    console.error('Error resetting device:', error);
                    showSimplePopup('An error occurred while resetting the device.');
                });
            });
        });

        // Attach event listeners for "Get" and "Set" buttons
        document.querySelectorAll(".get-button").forEach(button => button.addEventListener('click', () => handleGetButton(button)));
        document.querySelectorAll(".set-button").forEach(button => button.addEventListener('click', () => handleSetValue(button)));
        
        const heatmapContainer = document.getElementById("heatmap");
        const tooltip = document.getElementById("tooltip");
        const tableBody = document.getElementById("deviceTableBody");
        const serialNumber = "{{ $device['_deviceId']['_SerialNumber']['value'] ?? $device['_deviceId']['_SerialNumber'] }}";
        const heatmapRow = document.querySelector(".HeatmapRow"); // The row containing the heatmap and table

        // Fetch connected device data from the Laravel API
        async function fetchConnectedDevices(serialNumber) {
            try {
                const response = await fetch(`/device/hosts/${serialNumber}`);
                if (!response.ok) {
                    console.error("Failed to fetch connected device data");
                    return { success: false, message: "Failed to fetch data" };
                }
                const data = await response.json();
                return data;
            } catch (error) {
                console.error("Error fetching connected devices:", error);
                return { success: false, message: "Error occurred while fetching data" };
            }
        }

        // Map RSSI to Distance (Smaller distances)
        function mapRssiToDistance(rssi) {
            const minRssi = 30;
            const maxRssi = 100;
            const minDistance = 20;
            const maxDistance = 200;

            if (rssi === 0) {
                return 40;
            }

            return maxDistance - ((rssi - minRssi) / (maxRssi - minRssi)) * (maxDistance - minDistance);
        }

        // Initialize Heatmap and Table
        async function initializeHeatmapAndTable() {
        const result = await fetchConnectedDevices(serialNumber);

        // Hide the HeatmapRow if there's an error or no devices are found
        if (!result.success || result.message.includes("No host nodes configuration found")) {
            console.error(result.message || "No devices found.");
            heatmapRow.style.display = "none"; // Hide the entire row
            return;
        }

        const devices = result.data || [];

        if (!devices.length) {
            console.error("No devices found for the provided serial number.");
            heatmapRow.style.display = "none"; // Hide the entire row
            return;
        }

        // Show the HeatmapRow if data is successfully fetched
        heatmapRow.style.display = "flex"; // Ensure the row is displayed as a flex container

        // Set heatmap container to fixed dimensions
        heatmapContainer.style.width = "500px";
        heatmapContainer.style.height = "500px";

        const containerWidth = heatmapContainer.offsetWidth;
        const containerHeight = heatmapContainer.offsetHeight;

        // Create Circular Range Indicators
        const radarRanges = [30, 60, 90, 120, 150, 200];
        radarRanges.forEach((radius) => {
            const circle = document.createElement("div");
            circle.className = "radar-circle";
            circle.style.width = `${radius * 2}px`;
            circle.style.height = `${radius * 2}px`;
            circle.style.left = `${containerWidth / 2 - radius}px`;
            circle.style.top = `${containerHeight / 2 - radius}px`;
            heatmapContainer.appendChild(circle);
        });

        // Place Devices and Populate Table
        devices.forEach((device, index) => {
            const rssi = device.signalStrength || 0;

            const angle = (index / devices.length) * 2 * Math.PI; // Distribute evenly
            const distance = mapRssiToDistance(rssi);
            const x = containerWidth / 2 + Math.cos(angle) * distance;
            const y = containerHeight / 2 + Math.sin(angle) * distance;

            const deviceNode = document.createElement("div");
            deviceNode.className = "device-node";
            deviceNode.style.left = `${x - 15}px`;
            deviceNode.style.top = `${y - 15}px`;

            const icon = document.createElement("i");
            icon.className = "fa-solid fa-user";
            icon.style.color = rssi === 0 ? "green" : "white";
            deviceNode.appendChild(icon);

            deviceNode.addEventListener("mouseenter", () => {
                tooltip.style.opacity = 1;
                tooltip.style.left = `${x + 20}px`;
                tooltip.style.top = `${y}px`;
                tooltip.innerHTML = `
                    <strong>${device.hostName || "Unknown"}</strong><br>
                    IP: ${device.ipAddress || "N/A"}<br>
                    MAC: ${device.macAddress || "N/A"}<br>
                    RSSI: - ${rssi} dBm
                `;
            });

            deviceNode.addEventListener("mouseleave", () => {
                tooltip.style.opacity = 0;
            });

            heatmapContainer.appendChild(deviceNode);

            // Add Row to Table
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${device.hostName || "Unknown"}</td>
                <td>${rssi} dBm</td>
            `;
            tableBody.appendChild(row);
        });
        }

        // Initialize Heatmap and Table
        initializeHeatmapAndTable();

        const searchBar = document.getElementById("search-bar");
    const clearButton = document.getElementById("clear-search");
    const treeItems = document.querySelectorAll(".node-content");

    // Event listener for search
    searchBar.addEventListener("input", function () {
        const query = searchBar.value.trim().toLowerCase();

        // Reset highlights and collapse all nodes
        resetTree();

        if (!query) return;

        let found = false;

        treeItems.forEach(item => {
            const nodePath = item.querySelector(".node-value")?.id || "";
            const nodeName = item.querySelector(".node-name")?.textContent.toLowerCase();
            const nodeValue = item.querySelector(".node-value")?.textContent.toLowerCase();

            if (nodePath.includes(query) || (nodeName && nodeName.includes(query)) || (nodeValue && nodeValue.includes(query))) {
                found = true;

                // Highlight matching element
                const valueElement = item.querySelector(".node-value");
                const nameElement = item.querySelector(".node-name");

                if (nodePath.includes(query)) {
                    valueElement?.classList.add("highlight");
                } else if (nodeName.includes(query)) {
                    nameElement?.classList.add("highlight");
                } else if (nodeValue.includes(query)) {
                    valueElement?.classList.add("highlight");
                }

                // Expand parent nodes
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

    // Reset search and clear highlights
    clearButton.addEventListener("click", function () {
        searchBar.value = "";
        resetTree();
    });

    // Reset highlights and collapse nodes
    function resetTree() {
        document.querySelectorAll(".highlight").forEach(el => el.classList.remove("highlight"));
        document.querySelectorAll("ul").forEach(ul => ul.classList.add("collapsed"));
    }

        });

</script>

@endsection
