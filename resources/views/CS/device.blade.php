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
                                        <input type="text" class="form-control" id="link" name="link" value="{{ url('/end-user-login/' . Str::random(32)) }}" readonly onclick="linkGeneration.copyText('link')">
                                        <button type="button" class="btn btn-outline-secondary" onclick="linkGeneration.copyText('link')">
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
                                        <input type="text" class="form-control" id="password" name="password" value="{{ Str::random(12) }}" readonly onclick="linkGeneration.copyText('password')">
                                        <button type="button" class="btn btn-outline-secondary" onclick="linkGeneration.copyText('password')">
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

{{-- //secript for copy text --}}

<script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            console.error('CSRF token not found');
        }

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
        showPopup: (message, isError = false, duration = 5000) => {
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
                }, duration);
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

    // Link Generation Functions
    const linkGeneration = {
        initializeForm: () => {
            const linkInput = document.getElementById('link');
            const passwordInput = document.getElementById('password');
            const expiresAtInput = document.getElementById('expires_at');

            linkInput.value = `${window.location.origin}/end-user-login/${linkGeneration.generateRandomString(32)}`;
            passwordInput.value = linkGeneration.generateRandomString(12);
            const now = new Date();
            now.setMinutes(now.getMinutes() + 10);
            expiresAtInput.value = now.toISOString().slice(0, 16);
        },
        generateRandomString: (length) => {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let result = '';
            for (let i = 0; i < length; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return result;
        },
        copyText: async (fieldId) => {
            const input = document.getElementById(fieldId);
            try {
                await navigator.clipboard.writeText(input.value);
                utils.showPopup(`Copied to clipboard!`, false, 2000);
            } catch (err) {
                console.error('Copy error:', err);
                utils.showPopup('Failed to copy text.', true, 2000);
            }
        },
        regenerateLink: async () => {
            utils.showLoading();
            try {
                const linkInput = document.getElementById('link');
                const passwordInput = document.getElementById('password');
                linkInput.value = `${window.location.origin}/end-user-login/${linkGeneration.generateRandomString(32)}`;
                passwordInput.value = linkGeneration.generateRandomString(12);
                const now = new Date();
                now.setMinutes(now.getMinutes() + 10);
                document.getElementById('expires_at').value = now.toISOString().slice(0, 16);
                utils.showPopup(`New Link Generated!<br>Password: ${passwordInput.value}`, false, 3000);
            } catch (error) {
                console.error('Regenerate link error:', error);
                utils.showPopup(`Error regenerating link: ${error.message}`, true, 3000);
            } finally {
                utils.hideLoading();
            }
        }
    };

    // Link Generation Form Handler
    const generateLinkForm = document.getElementById('generate-link-form');
    if (generateLinkForm) {
        document.getElementById('generateLinkModal').addEventListener('show.bs.modal', linkGeneration.initializeForm);
        generateLinkForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            utils.showLoading();

            try {
                const linkInput = document.getElementById('link');
                const passwordInput = document.getElementById('password');
                const expiresAtInput = document.getElementById('expires_at');
                const usernameInput = document.getElementById('username');

                const expiresAt = new Date(expiresAtInput.value);
                if (isNaN(expiresAt.getTime())) {
                    throw new Error('Invalid expiration date');
                }

                const formData = new FormData(this);
                formData.set('link', linkInput.value);
                formData.set('password', passwordInput.value);
                formData.set('username', usernameInput.value);
                formData.set('expires_at', expiresAtInput.value);

                const response = await fetch(generateLinkForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    linkInput.value = data.link || linkInput.value;
                    passwordInput.value = data.password || passwordInput.value;
                    expiresAtInput.value = data.expires_at ?
                        new Date(data.expires_at).toISOString().slice(0, 16) :
                        expiresAtInput.value;

                    await linkGeneration.copyText('link');
                    utils.showPopup(`Link Generated Successfully!<br>Password: ${passwordInput.value}`, false, 3000);
                } else {
                    throw new Error(data.message || 'Failed to generate link.');
                }
            } catch (error) {
                console.error('Generate link error:', error);
                utils.showPopup(`Error generating link: ${error.message}`, true, 3000);
            } finally {
                utils.hideLoading();
            }
        });
    }

    // Global function for regenerateLink to be called from HTML
    function regenerateLink() {
        linkGeneration.regenerateLink();
    }
    </script>
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
<link rel="stylesheet" href="{{ asset('assets/css/cs/device-info.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('assets/js/cs/device-info.js') }}"></script>
@endsection

