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

{{--Start Add task pop-up by Email --}}
 <!-- Trigger Button -->
<button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#SendTaskModal">
    <i class="bi bi-plus-lg me-2"></i> Send Ticket by Email
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
                <button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item reboot-device" href="#" data-serial-number="{{ $device['_deviceId']['_SerialNumber'] ?? 'Unknown' }}">Reboot</a></li>
                    <li><a class="dropdown-item reset-device" href="#" data-serial-number="{{ $device['_deviceId']['_SerialNumber'] ?? 'Unknown' }}">Factory Reset</a></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#pushSoftware">Push Upgrade</a></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#diagnosticsModal">Run Diagnostics</a></li>
                </ul>
            </div>
        </div>
    </div>
    <hr>

    <!-- Success Alert -->
    @if(session('success'))
        <div class="alert alert-success mt-3" id="success-alert">{{ session('success') }}</div>
    @endif

    <!-- Device Info Section -->
    <div class="row">
        <div class="col-md-6">
            <table class="table table-striped">
                <tbody>
                    <tr><th>Serial Number:</th><td>{{ $device['_deviceId']['_SerialNumber'] ?? 'Unknown' }}</td></tr>
                    <tr><th>Device ID:</th><td>{{ $device['_id'] ?? 'Unknown' }}</td></tr>
                    <tr><th>OUI:</th><td>{{ $device['_deviceId']['_OUI'] ?? 'Unknown' }}</td></tr>
                    <tr><th>Manufacturer:</th><td>{{ $device['_deviceId']['_Manufacturer'] ?? 'Unknown' }}</td></tr>
                    <tr><th>Product Class:</th><td>{{ $device['_deviceId']['_ProductClass'] ?? 'Unknown' }}</td></tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <img src="{{ asset(file_exists(public_path('assets/Devices/' . ($device['_deviceId']['_ProductClass'] ?? '') . '.png')) ? 'assets/Devices/' . $device['_deviceId']['_ProductClass'] . '.png' : 'assets/AVXAV Logos/default.png') }}"
                 class="card-img-top" alt="Device Image">
        </div>
    </div>

    <!-- Heatmap and Connected Devices -->
    <div class="row heatmap-row" style="display: none;">
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

    <!-- Node Tabs -->
    <div class="row mt-4">
        <div class="col-12">
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
                                    <input type="hidden" name="device_id" value="{{ $device['_id'] ?? '' }}">
                                    <input type="hidden" name="url_Id" value="{{ $url_Id }}">
                                    <table class="table">
                                        <thead><tr><th>Node</th><th>Value</th></tr></thead>
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
                                                <tr><td colspan="2" class="text-center">No nodes available</td></tr>
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

    <!-- Modals -->
    <!-- Send Task by Email Modal -->
    <div class="modal fade" id="sendTaskModal" tabindex="-1" aria-labelledby="sendTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="send-task-form" method="POST" action="{{ route('send.task') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="sendTaskModalLabel">Send Ticket via Email</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                            <input type="text" class="form-control" name="device_id" value="{{ $device['_deviceId']['_SerialNumber'] ?? 'Unknown' }}" readonly>
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
                            <label class="form-label">Ticket Description</label>
                            <textarea class="form-control" name="description" placeholder="Describe the ticket here..." rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success"><i class="bi bi-send me-1"></i> Send Ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                        <input type="hidden" name="device_id" value="{{ $device['_deviceId']['_SerialNumber'] ?? '' }}">
                        <div class="mb-3">
                            <label for="swFile" class="form-label">Select Software File</label>
                            <select id="swFile" name="swFile" class="form-select" required>
                                <option value="" disabled selected>Select a software file</option>
                                @foreach ($swFiles as $file)
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
                            <input type="text" class="form-control" id="diagnostics-host" name="host" required placeholder="e.g., 8.8.8.8" value="8.8.8.8">
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
    #diagnostics-data {
        max-height: 200px;
        overflow-y: auto;
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {
    const serialNumber = "{{ $device['_deviceId']['_SerialNumber'] ?? 'Unknown' }}";
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

    // Tree View Management
    const treeView = {
        init: () => {
            document.querySelectorAll('.expand-icon').forEach(toggle => {
                toggle.addEventListener('click', () => {
                    const parentLi = toggle.closest('li');
                    const childUl = parentLi.querySelector('ul');
                    if (childUl) {
                        childUl.classList.toggle('collapsed');
                        childUl.classList.toggle('expanded');
                        toggle.textContent = childUl.classList.contains('expanded') ? '▼' : '▶';
                    }
                });
            });
        },
        search: () => {
            const searchBar = document.getElementById('search-bar');
            if (!searchBar) return;
            const clearButton = document.getElementById('clear-search');
            const treeItems = document.querySelectorAll('.node-content');

            searchBar.addEventListener('input', () => {
                const query = searchBar.value.trim().toLowerCase();
                treeView.reset();
                if (!query) return;

                treeItems.forEach(item => {
                    const valueEl = item.querySelector('.node-value');
                    const nameEl = item.querySelector('.node-name');
                    const path = valueEl?.id?.toLowerCase() || '';
                    const name = nameEl?.textContent?.toLowerCase() || '';
                    const value = valueEl?.textContent?.toLowerCase() || '';

                    if (path.includes(query) || name.includes(query) || value.includes(query)) {
                        if (path.includes(query)) valueEl.classList.add('highlight');
                        else if (name.includes(query)) nameEl.classList.add('highlight');
                        else valueEl.classList.add('highlight');

                        let parent = item.closest('ul');
                        while (parent) {
                            parent.classList.remove('collapsed');
                            parent.classList.add('expanded');
                            parent = parent.parentElement.closest('ul');
                        }
                    }
                });
            });

            clearButton.addEventListener('click', () => {
                searchBar.value = '';
                treeView.reset();
            });
        },
        reset: () => {
            document.querySelectorAll('.highlight').forEach(el => el.classList.remove('highlight'));
            document.querySelectorAll('ul').forEach(ul => ul.classList.add('collapsed'));
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

    // Heatmap Management
    const heatmap = {
        init: async () => {
            const heatmapRow = document.querySelector('.heatmap-row');
            const heatmapContainer = document.getElementById('heatmap');
            const tableBody = document.getElementById('deviceTableBody');

            try {
                const { data: devices } = await utils.fetchData(`/device/hosts/${serialNumber}`);
                if (!devices || devices.length === 0) {
                    heatmapRow.style.display = 'none';
                    return;
                }

                heatmapRow.style.display = 'flex';
                heatmapContainer.style.width = '500px';
                heatmapContainer.style.height = '500px';
                const containerWidth = heatmapContainer.offsetWidth;
                const containerHeight = heatmapContainer.offsetHeight;

                // Create Radar Circles
                [30, 60, 90, 120, 150, 200].forEach(radius => {
                    const circle = document.createElement('div');
                    circle.className = 'radar-circle';
                    circle.style.cssText = `width: ${radius * 2}px; height: ${radius * 2}px; left: ${containerWidth / 2 - radius}px; top: ${containerHeight / 2 - radius}px;`;
                    heatmapContainer.appendChild(circle);
                });

                // Place Devices
                devices.forEach((device, index) => {
                    const rssi = device.signalStrength || 0;
                    const isWired = rssi === 0;
                    const angle = (index / devices.length) * 2 * Math.PI;
                    const distance = isWired ? 40 : Math.max(20, Math.min(200, 200 - (rssi / 100 * 180)));
                    const x = containerWidth / 2 + Math.cos(angle) * distance;
                    const y = containerHeight / 2 + Math.sin(angle) * distance;

                    const node = document.createElement('div');
                    node.className = 'device-node';
                    node.style.cssText = `left: ${x - 15}px; top: ${y - 15}px;`;
                    node.innerHTML = `<i class="fa-solid fa-user" style="color: ${isWired ? 'lightblue' : 'white'};"></i>`;
                    node.addEventListener('mouseenter', () => {
                        const tooltip = document.getElementById('tooltip');
                        tooltip.style.opacity = '1';
                        tooltip.style.left = `${x + 20}px`;
                        tooltip.style.top = `${y}px`;
                        tooltip.innerHTML = `
                            <strong>${device.hostName || 'Unknown'}</strong><br>
                            IP: ${device.ipAddress || 'N/A'}<br>
                            MAC: ${device.macAddress || 'N/A'}<br>
                            ${isWired ? 'Connection: <strong>Cable</strong>' : `RSSI: -${rssi} dBm`}
                        `;
                    });
                    node.addEventListener('mouseleave', () => document.getElementById('tooltip').style.opacity = '0');
                    heatmapContainer.appendChild(node);

                    // Add to Table
                    const row = document.createElement('tr');
                    row.innerHTML = `<td>${device.hostName || 'Unknown'}</td><td>${isWired ? 'N/A' : `${rssi} dBm`}</td>`;
                    tableBody.appendChild(row);
                });
            } catch {
                heatmapRow.style.display = 'none';
            }
        }
    };

    // Diagnostics Management
    const diagnostics = {
        init: () => {
            document.getElementById('run-diagnostics-btn').addEventListener('click', async () => {
                const host = document.getElementById('diagnostics-host').value;
                const method = document.getElementById('diagnostics-method').value;
                if (!host || !method) {
                    utils.showPopup('Please provide a target IP and select a method.');
                    return;
                }

                document.getElementById('diagnostics-loading').style.display = 'block';
                document.getElementById('diagnostics-result').style.display = 'none';

                try {
                    const data = await utils.fetchData(`/device/${serialNumber}/diagnostics?host=${encodeURIComponent(host)}&method=${method}`);
                    document.getElementById('diagnostics-loading').style.display = 'none';
                    document.getElementById('diagnostics-result').style.display = 'block';
                    const results = data.results;
                    if (data.success) {
                        if (method === 'Ping') {
                            document.getElementById('diagnostics-data').textContent = `Success Count: ${results.SuccessCount ?? 'N/A'}\nFailure Count: ${results.FailureCount ?? 'N/A'}`;
                        } else {
                            document.getElementById('diagnostics-data').textContent = results.map(hop => 
                                `${hop.HopNumber}. ${hop.HopHostAddress ?? '*'} (${hop.HopRTTimes ?? '*'} ms)`
                            ).join('\n') || 'No hops found.';
                        }
                    } else {
                        document.getElementById('diagnostics-data').textContent = data.message || 'Diagnostics failed.';
                    }
                } catch {
                    document.getElementById('diagnostics-loading').style.display = 'none';
                    document.getElementById('diagnostics-result').style.display = 'block';
                    document.getElementById('diagnostics-data').textContent = 'Failed to run diagnostics.';
                }
            });
        }
    };

    // Initialize Components
    treeView.init();
    treeView.search();
    heatmap.init();
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