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
            {{-- Start Add task pop-up by Email --}}
            <!-- Trigger Button -->
            <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#SendTaskModal">
                <i class="bi bi-plus-lg me-2"></i> Send Ticket by Email
            </button>

            <!-- Generate End-User Link Button -->
            <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#generateLinkModal">
                <i class="bi bi-plus-lg me-2"></i> Generate end link
            </button>

            <!-- Modal -->
            <div class="modal fade" id="SendTaskModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="send-task-form" method="POST" action="{{ route('send.task') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Send Ticket via Email</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="" style="display: none">
                                <div class="mb-3">
                                    <label class="form-label">Your Name</label>
                                    <input type="hidden" class="form-control" name="username" value="{{ auth()->user()->name }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Your Email</label>
                                    <input type="hidden" class="form-control" name="user_email"  value="{{ auth()->user()->email }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Device Serial Number</label>
                                    <input type="hidden" class="form-control" name="device_id" value="{{ $device['_deviceId']['_SerialNumber'] ?? 'Unknown Serial Number' }}" readonly>
                                </div></div>
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
                                    <textarea class="form-control" name="description" placeholder="Describe the task here..." rows="3" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success" id="submit-btn">
                                    <i class="bi bi-send me-1"></i> Send Ticket
                                </button>
                            </div>
                        </form>


                    </div>
                </div>
            </div>


            {{-- End Add task pop-up by Email --}}

            <!-- Generate End-User Link Modal -->
            <div class="modal fade" id="generateLinkModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="generate-link-form" method="POST" action="{{ route('generate.link') }}">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Generate End-User Link</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">End-User Link</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="link" name="link" value="{{ url('/end-user-login/' . Str::random(32)) }}" readonly onclick="copyText('link')">
                                        <button type="button" class="btn btn-outline-secondary" onclick="copyText('link')">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" value="{{ $device['_deviceId']['_SerialNumber'] ?? 'Unknown Serial Number' }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="password" name="password" value="{{ Str::random(12) }}" readonly onclick="copyText('password')">
                                        <button type="button" class="btn btn-outline-secondary" onclick="copyText('password')">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Expiration Date</label>
                                    <div class="input-group">
                                        <input type="datetime-local" class="form-control" id="expires_at" name="expires_at" value="{{ now()->addMinutes(10)->format('Y-m-d\TH:i') }}">


                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" onclick="regenerateLink()">Generate New Data</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-link me-1"></i> Generate Link
                                </button>
                                <div style="color: red;font-size: initial;color:  red !important;" class="form-text text-muted">The link is valid for 10 minutes</div style="color: red;font-size: initial;color:  red !important;">

                            </div>
                        </form>
                    </div>
                </div>

            </div>



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

                    <input type="hidden" class="device-id" value="{{ $device['_id'] ?? '' }}">
                    <input type="hidden" class="url-id" value="{{ $url_Id }}">

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
                                                    <input type="text" class="form-control node-value" 
                                                           data-node="{{ $nodeKey }}"
                                                           value="{{ $nodeValues[$nodeKey]['value'] }}">
                                                @else
                                                    <input type="hidden" class="node-value" 
                                                           data-node="{{ $nodeKey }}"
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
                        <button type="button" class="btn btn-primary me-2 manage-btn" data-action="GET">Get</button>
                        @if ($category != 'RF')
                            <button type="button" class="btn btn-success manage-btn" data-action="SET">Set</button>
                        @endif
                    </div>

                    <div class="mt-3">
                        <div class="loading-spinner" style="display:none;">Loading...</div>
                        <div class="response-message"></div>
                    </div>

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
        showLoading: () => {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'block';
        },
        hideLoading: () => {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'none';
        },
        showPopup: (message, isError = false) => {
            const popup = document.getElementById('simplePopup');
            const popupMessage = document.getElementById('popupMessage');
            if (popup && popupMessage) {
                popupMessage.innerHTML = message;
                popup.style.display = 'block';
                popup.style.background = isError ? '#f8d7da' : '#fff';

                popup.classList.add('show');
                setTimeout(() => {
                    popup.classList.remove('show');
                    setTimeout(() => popup.style.display = 'none', 300);

                }, 5000); // Display for 5 seconds

            }
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
                utils.showPopup(`Error: ${error.message}`, true);
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





    // generate-link-form








    // Device Actions
    const deviceActions = {
        init: () => {
            console.log('loading device actions started');
        },
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
                    console.log('loading device actions finished');
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
            const MAX_DISPLAY_RADIUS = 180;

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
            } catch (error) {
                console.error('Error initializing heatmap:', error);
                heatmapRow.style.display = 'none';
            }
        },

        getDistance: (signal) => {
            if (signal == null || signal === 0) return 30;
            if (signal >= -20) return 60;
            if (signal >= -40) return 90;
            if (signal >= -60) return 120;
            if (signal >= -80) return 150;
            return 180;
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

        hideTooltip: () => {
            const tooltip = document.getElementById('tooltip');
            if (tooltip) tooltip.style.opacity = '0';
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


<script>

const utils = {
        showLoading: () => {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'block';
        },
        hideLoading: () => {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'none';
        },
        showPopup: (message, isError = false) => {
            const popup = document.getElementById('simplePopup');
            const popupMessage = document.getElementById('popupMessage');
            if (popup && popupMessage) {
                popupMessage.innerHTML = message;
                popup.style.display = 'block';
                popup.style.background = isError ? '#f8d7da' : '#fff';
                popup.classList.add('show');
                setTimeout(() => {
                    popup.classList.remove('show');
                    setTimeout(() => popup.style.display = 'none', 300);
                }, 5000); // Display for 5 seconds
            }
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
                utils.showPopup(`Error: ${error.message}`, true);
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

    const generateLinkForm = document.getElementById('generate-link-form');
    const linkInput = document.getElementById('link');
    const passwordInput = document.getElementById('password');
    const expiresAtInput = document.getElementById('expires_at');
    const usernameInput = document.getElementById('username');

    // Improved copy function
    async function copyText(fieldId) {
        const input = document.getElementById(fieldId);
        try {
            await navigator.clipboard.writeText(input.value);
            utils.showPopup(`Copied to clipboard!`, false, 2000);
        } catch (err) {
            console.error('Copy error:', err);
            utils.showPopup('Failed to copy text.', true, 2000);
        }
    }

    // Generate random string for link and password
    function generateRandomString(length) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let result = '';
        for (let i = 0; i < length; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }

    // Set default expiration (10 minutes from now)
    function setDefaultExpiration() {
        const now = new Date();
        now.setMinutes(now.getMinutes() + 10);
        expiresAtInput.value = now.toISOString().slice(0, 16);
    }

    // Initialize form with default values
    function initializeForm() {
        linkInput.value = `${window.location.origin}/end-user-login/${generateRandomString(32)}`;
        passwordInput.value = generateRandomString(12);
        setDefaultExpiration();
    }

    // Common function to handle API requests
    async function handleLinkGeneration(formData) {
        try {
            const response = await fetch(generateLinkForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || `Server error: ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error('API error:', error);
            throw error;
        }
    }

    // Regenerate link function
    async function regenerateLink() {
        utils.showLoading();
        try {
            // Generate new random values
            linkInput.value = `${window.location.origin}/end-user-login/${generateRandomString(32)}`;
            passwordInput.value = generateRandomString(12);
            setDefaultExpiration();

            utils.showPopup(`
                <strong>New Link Generated!</strong><br>

                Password: ${passwordInput.value}
            `, false, 3000);
        } catch (error) {
            console.error('Regenerate link error:', error);
            utils.showPopup(`Error regenerating link: ${error.message}`, true, 3000);
        } finally {
            utils.hideLoading();
        }
    }

    // Form submission handler
    if (generateLinkForm) {
        // Initialize form when modal is shown
        document.getElementById('generateLinkModal').addEventListener('show.bs.modal', initializeForm);

        generateLinkForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            utils.showLoading();

            try {
                // Validate expiration date
                const expiresAt = new Date(expiresAtInput.value);
                if (isNaN(expiresAt.getTime())) {
                    throw new Error('Invalid expiration date');
                }

                const formData = new FormData(this);
                formData.set('link', linkInput.value);
                formData.set('password', passwordInput.value);
                formData.set('username', usernameInput.value);
                formData.set('expires_at', expiresAtInput.value);

                const data = await handleLinkGeneration(formData);

                if (data.success) {
                    linkInput.value = data.link || linkInput.value;
                    passwordInput.value = data.password || passwordInput.value;
                    expiresAtInput.value = data.expires_at ?
                        new Date(data.expires_at).toISOString().slice(0, 16) :
                        expiresAtInput.value;

                    utils.showPopup(`
                        <strong>Link Generated Successfully!</strong><br>

                        Password: ${passwordInput.value}
                    `, false, 3000);
                } else {
                    utils.showPopup(data.message || 'Failed to generate link.', true, 3000);
                }
            } catch (error) {
                console.error('Generate link error:', error);
                utils.showPopup(`Error generating link: ${error.message}`, true, 3000);
            } finally {
                utils.hideLoading();
            }
        });
    }

</script>
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
<script>
    // Define the showLoadingOverlay and hideLoadingOverlay functions
    function showLoadingOverlay() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.display = 'block';
        }
    }
    
    function hideLoadingOverlay() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }
    
    document.querySelectorAll('.manage-btn').forEach(button => {
        button.addEventListener('click', async function () {
            const tabPane = this.closest('.tab-pane');
            const deviceId = tabPane.querySelector('.device-id').value;
            const urlId = tabPane.querySelector('.url-id').value;
            const action = this.getAttribute('data-action');

            showLoadingOverlay(); // Centered global loading

            const nodeInputs = tabPane.querySelectorAll('.node-value');
            const nodes = {};
            nodeInputs.forEach(input => {
                const nodePath = input.getAttribute('data-node');
                if (action === 'SET') {
                    nodes[nodePath] = { value: input.value.trim() };
                } else {
                    nodes[nodePath] = {};
                }
            });

            try {
                const response = await fetch("{{ route('node.manageCustomer') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        device_id: deviceId,
                        url_Id: urlId,
                        action: action,
                        nodes: nodes
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    showSimplePopup(`Action ${action} completed successfully.`);
                } else {
                    showSimplePopup(`Error: ${result.message || 'An error occurred.'}`, true);
                }

            } catch (error) {
                showSimplePopup(`Error: ${error.message}`, true);
            } finally {
                hideLoadingOverlay();
            }
        });
    });
</script>
@endsection

