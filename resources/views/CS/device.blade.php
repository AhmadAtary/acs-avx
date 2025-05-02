@extends('layouts.app')

@section('title', 'Device Info')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <h1>Device Info</h1>
        </div>
        <div class="col-md-6 text-end">
{{--Start Add task pop-up by Email --}}
 <!-- Trigger Button -->
<button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#SendTaskModal">
    <i class="bi bi-plus-lg me-2"></i> Send Task by Email
</button>

<!-- Generate End-User Link Button -->
<button class="btn btn-primary px-4 mt-2" data-bs-toggle="modal" data-bs-target="#GenerateLinkModal">
    <i class="bi bi-plus-lg me-2"></i> Generate end link
</button>

<!-- Modal -->
<div class="modal fade" id="SendTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="send-task-form" method="POST" action="{{ route('send.task') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Send Task via Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">



                    <div class="mb-3">
                        <label class="form-label">Your Name</label>
                        <input type="text" class="form-control" name="username" value="{{ auth()->user()->name }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Your Email</label>
                        <input type="text" class="form-control" name="user_email" value="{{ auth()->user()->email }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Device Serial Number</label>
                        <input type="text" class="form-control" name="device_id" value="{{ $device['_deviceId']['_SerialNumber'] ?? 'Unknown Serial Number' }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Recipient Email</label>
                        <input type="email" class="form-control" name="email" placeholder="Enter recipient's email address" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Subject</label>
                        <input type="text" class="form-control" name="subject" placeholder="Email Subject" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Task Description</label>
                        <textarea class="form-control" name="description" placeholder="Describe the task here..." rows="3" required></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" id="submit-btn">
                        <i class="bi bi-send me-1"></i> Send Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mt-3" id="success-alert">
        {{ session('success') }}
    </div>
@endif

{{-- End Add task pop-up by Email --}}

<!-- Generate End-User Link Modal -->
<div class="modal fade" id="GenerateLinkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('generate.link') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Generate End-User Link</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="m   odal-body">
                    <div class="mb-3">
                        <label class="form-label">End-User Link</label>
                        <input type="text" class="form-control" name="link" value="{{ url('/end-user-login/' . Str::random(32)) }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" value="{{ $device['_deviceId']['_SerialNumber'] ?? 'Unknown Serial Number' }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="text" class="form-control" name="password" value="{{ Str::random(12) }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expiration Date</label>
                        <input type="text" class="form-control" name="expires_at" value="{{ now()->addMinutes(5)->toDateTimeString() }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-link me-1"></i> Generate Link
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
 // Handle Generate Link button if the modal exists
 const generateLinkForm = document.getElementById('GenerateLinkModal');
            if (GenerateLinkModal) {
                GenerateLinkModal.addEventListener('submit', function(e) {
                    e.preventDefault();
                    showLoadingOverlay();

                    fetch(this.action, {
                        method: 'POST',
                        body: new FormData(this),
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        hideLoadingOverlay();
                        if (data.success) {
                            const linkInput = document.getElementById('link');
                            if (linkInput) {
                                linkInput.value = data.link;
                                navigator.clipboard.writeText(data.link)
                                    .then(() => {
                                        showSimplePopup('Link generated and copied to clipboard!');
                                    })
                                    .catch(() => {
                                        showSimplePopup('Link generated! Please copy it manually.');
                                    });
                            }
                        } else {
                            showSimplePopup(data.message || 'Failed to generate link.', true);
                        }
                    })
                    .catch(error => {
                        hideLoadingOverlay();
                        showSimplePopup('Error: ' + error.message, true);
                    });
                });
            }



</script>
@if(session('success'))
    <div class="alert alert-success mt-3" id="success-alert">
        {{ session('success') }}
    </div>
@endif







            <div class="btn-group">
                <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    Actions
                </button>
                <ul class="dropdown-menu">
                    <li><a class="reboot-device dropdown-item" href="#" data-serial-number="{{ $device['_deviceId']['_SerialNumber'] ?? 'Unknown' }}">Reboot</a></li>
                    <li><a class="reset-device dropdown-item" href="#" data-serial-number="{{ $device['_deviceId']['_SerialNumber'] ?? 'Unknown' }}">Factory Reset</a></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#pushSoftware">Push Upgrade</a></li>
                </ul>
            </div>
        </div>
        <hr>
    </div>

    <div class="row">
        <div class="col-md-6">
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <th>Serial Number:</th>
                        <td>{{ $device['_deviceId']['_SerialNumber'] ?? 'Unknown Serial Number' }}</td>
                    </tr>
                    <tr>
                        <th>Device ID:</th>
                        <td>{{ $device['_id'] ?? 'Unknown' }}</td>
                    </tr>
                    <tr>
                        <th>OUI:</th>
                        <td>{{ $device['_deviceId']['_OUI'] ?? 'Unknown' }}</td>
                    </tr>
                    <tr>
                        <th>Manufacturer:</th>
                        <td>{{ $device['_deviceId']['_Manufacturer'] ?? 'Unknown' }}</td>
                    </tr>
                    <tr>
                        <th>Product Class:</th>
                        <td>{{ $device['_deviceId']['_ProductClass'] ?? 'Unknown' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
        <img src="{{ asset('assets/Devices/' . $device['_deviceId']['_ProductClass'] . '.png') }}"
                         class="card-img-top">
        </div>
    </div>

    <div class="row HeatmapRow">
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

    <div class="row mt-4">
    <div class="">
        <ul class="nav nav-tabs" id="nodeTabs" role="tablist">
            @foreach ($uniqueNodeTypes as $index => $type)
                <li class="nav-item">
                    <a class="nav-link {{ $index == 0 ? 'active' : '' }}" id="{{ Str::slug($type) }}-tab"
                        data-bs-toggle="tab" href="#{{ Str::slug($type) }}" role="tab"
                        aria-controls="{{ Str::slug($type) }}" aria-selected="{{ $index == 0 ? 'true' : 'false' }}">
                        {{ $type }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content mt-3" id="nodeTabsContent">
            @foreach ($uniqueNodeTypes as $index => $category)
                <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="{{ Str::slug($category) }}"
                    role="tabpanel" aria-labelledby="{{ Str::slug($category) }}-tab">

                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="{{ route('node.manageCustomer') }}">
                                @csrf
                                <!-- Hidden input to send device_id -->
                                <input type="hidden" name="device_id" value="{{ $device['_id'] ?? '' }}">
                                <input type="hidden" id="url_Id" name="url_Id" class="form-control"
                                value="{{ $url_Id }}">

                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Node</th>
                                            <th>Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (!empty($nodeCategories[$category]))
                                            @foreach ($nodeCategories[$category] as $nodeKey)
                                                <tr>
                                                    <td>{{ $nodeKey }}</td>
                                                    <td>
                                                        @if (isset($nodeValues[$nodeKey]['value']))
                                                            @if ($nodeValues[$nodeKey]['nodeMode'])
                                                                <input type="text" name="nodes[{{ $nodeKey }}][value]"
                                                                    value="{{ $nodeValues[$nodeKey]['value'] }}"
                                                                    class="form-control">
                                                            @else
                                                                <input type="hidden" name="nodes[{{ $nodeKey }}][value]"
                                                                    value="{{ $nodeValues[$nodeKey]['value'] }}">
                                                                <span>{{ $nodeValues[$nodeKey]['value'] }}</span>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">No value found</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="2" class="text-center">No nodes available</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                                <div class="text-end mt-3">
                                    <button type="submit" name="action" value="GET" class="btn btn-primary me-2">Get</button>
                                    @if ($category != 'RF')
                                        <button type="submit" name="action" value="SET" class="btn btn-success">Set</button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>
    </div>
</div>



            </div>
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
                               value="{{ $device['_deviceId']['_SerialNumber'] }}">

                    </div>
                    <div class="mb-3">
                        <label for="swFile" class="form-label">Select Software File</label>
                        <select id="swFile" name="swFile" class="form-select" required>
                            <option value="" disabled selected>Select a software file</option>
                            @foreach ($swFiles as $file)
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
            const serialNumber = "{{ $device['_deviceId']['_SerialNumber'] ?? 'Unknown' }}";

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
            const serialNumber = "{{ $device['_deviceId']['_SerialNumber'] ?? 'Unknown' }}";

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
        const serialNumber = "{{ $device['_deviceId']['_SerialNumber'] }}";
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

        // Map RSSI to Distance (Smaller distances for stronger signals)
        function mapRssiToDistance(rssi) {
            const minRssi = 30;
            const maxRssi = 100;
            const minDistance = 20;
            const maxDistance = 200;

            if (rssi === 0) {
                return 40; // Cable connection (not based on RSSI)
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
                const isWired = rssi === 0; // Check if it's a wired connection

                const angle = (index / devices.length) * 2 * Math.PI; // Distribute evenly
                const distance = mapRssiToDistance(rssi);
                const x = containerWidth / 2 + Math.cos(angle) * distance;
                const y = containerHeight / 2 + Math.sin(angle) * distance;

                const deviceNode = document.createElement("div");
                deviceNode.className = "device-node";
                deviceNode.style.left = `${x - 15}px`;
                deviceNode.style.top = `${y - 15}px`;

                // Set color based on connection type


                const icon = document.createElement("i");
                icon.className = "fa-solid fa-user";
                icon.style.color = isWired ? "lightblue" : "white"; // Light blue for wired connections
                deviceNode.appendChild(icon);

                deviceNode.addEventListener("mouseenter", () => {
                    tooltip.style.opacity = 1;
                    tooltip.style.left = `${x + 20}px`;
                    tooltip.style.top = `${y}px`;
                    tooltip.innerHTML = `
                        <strong>${device.hostName || "Unknown"}</strong><br>
                        IP: ${device.ipAddress || "N/A"}<br>
                        MAC: ${device.macAddress || "N/A"}<br>
                        ${isWired ? "Connection: <strong>Cable</strong>" : `RSSI: -${rssi} dBm`}
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
                    <td>${isWired ? "N/A" : `${rssi} dBm`}</td>
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
