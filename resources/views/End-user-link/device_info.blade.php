<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Info - {{ $device['_deviceId']['_SerialNumber'] ?? 'Unknown' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .alert {
            margin-top: 20px;
        }
        .debug-section {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        #loadingOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9998;
        }
        #loadingSpinner {
            position: absolute;
            top: 50%;
            left: 50%;
            z-index: 9999;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- Display success/error messages from server -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="row align-items-center">
            <div class="col-md-6">
                <h1>Device Info: {{ $device['_deviceId']['_SerialNumber'] ?? 'Unknown' }}</h1>
            </div>

            <div class="text-end text-muted">
                Session expires in: <span id="sessionTimer">10:00</span>
            </div>
            <div class="col-md-6 text-end">
                <!-- Empty for now -->
            </div>
        </div>
        <!-- Node Table -->
        <div class="row mt-4">
            <div class="col-12">
                @if (empty($uniqueNodeTypes))
                    <div class="alert alert-warning" role="alert">
                        No node categories available for this device. Please contact support.
                    </div>
                @else
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
                                        <form method="POST" action="{{ route('node.update') }}" onsubmit="showLoading()">
                                            @csrf
                                            <input type="hidden" name="device_id" value="{{ $device['_id'] ?? '' }}">
                                            <input type="hidden" name="url_Id" value="{{ $url_Id }}">
                                            <input type="hidden" name="serialNumber" value="{{ $device['_deviceId']['_SerialNumber'] ?? '' }}">
                                            <table class="table table-hover">
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
                                                                    @if (isset($nodeValues[$nodeKey]['value']) && $nodeValues[$nodeKey]['value'] !== 'No value found')
                                                                        @if ($nodeValues[$nodeKey]['nodeMode'] ?? false)
                                                                            <input type="text" name="nodes[{{ $nodeKey }}][value]"
                                                                                   value="{{ $nodeValues[$nodeKey]['value'] }}"
                                                                                   class="form-control">
                                                                            <input type="hidden" name="nodes[{{ $nodeKey }}][path]"
                                                                                   value="{{ $nodeValues[$nodeKey]['path'] }}">
                                                                            <input type="hidden" name="nodes[{{ $nodeKey }}][type]"
                                                                                   value="{{ $nodeValues[$nodeKey]['type'] }}">
                                                                        @else
                                                                            <input type="hidden" name="nodes[{{ $nodeKey }}][value]"
                                                                                   value="{{ $nodeValues[$nodeKey]['value'] }}">
                                                                            <span>{{ $nodeValues[$nodeKey]['value'] }}</span>
                                                                        @endif
                                                                    @else
                                                                        <span class="text-muted">No value available</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr>
                                                            <td colspan="2" class="no-data">No nodes available in this category</td>
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
                @endif
            </div>
        </div>

        <!-- Loading Overlay -->
        <div id="loadingOverlay">
            <div id="loadingSpinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    const COUNTDOWN_DURATION = 10 * 60 * 1000;
    const LOGIN_URL = "{{ route('end.session') }}";

    function formatTime(ms) {
        const totalSeconds = Math.max(0, Math.floor(ms / 1000));
        const minutes = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
        const seconds = String(totalSeconds % 60).padStart(2, '0');
        return `${minutes}:${seconds}`;
    }

    function startTimer() {
        const display = document.getElementById("sessionTimer");
        let endTime = localStorage.getItem("sessionEndTime");

        if (!endTime) {
            endTime = Date.now() + COUNTDOWN_DURATION;
            localStorage.setItem("sessionEndTime", endTime);
        } else {
            endTime = parseInt(endTime);
        }

        const timerInterval = setInterval(() => {
            const remaining = endTime - Date.now();

            if (remaining <= 0) {
                clearInterval(timerInterval);
                localStorage.removeItem("sessionEndTime");
                // Force full page reload (bypass any AJAX/Single Page App behavior)
                window.location.href = LOGIN_URL; // Or window.location.replace(LOGIN_URL)
            } else {
                if (display) display.textContent = formatTime(remaining);
            }
        }, 1000);
    }

    document.addEventListener('DOMContentLoaded', startTimer);
    </script>
    <script>
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'block';
        }

        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });

        // Response handling script
        document.addEventListener('DOMContentLoaded', function() {
            // Check for success/error messages in the response
            @if(session('response_data'))
                const responseData = @json(session('response_data'));
                handleResponse(responseData);
            @endif
        });

        function handleResponse(data) {
            if (data.status_code === 200) {
                // Handle successful response
                if (data.value !== undefined) {
                    // Update field values if this was a GET operation
                    updateFieldValues(data);
                }
                showAlert('Operation completed successfully', 'success');
            } else if (data.status_code === 202) {
                // Handle task queued response
                showAlert('Operation saved as task and will be processed shortly', 'info');
            } else {
                // Handle error response
                showAlert(data.message || 'Operation failed', 'danger');
            }
        }

        function updateFieldValues(data) {
            // This function should update the UI with the new values
            // You'll need to implement this based on your specific UI structure
            // Example:
            // document.querySelector(`[data-path="${data.path}"]`).value = data.value;
            console.log('Update fields with:', data);
        }

        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.role = 'alert';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.querySelector('.container').prepend(alertDiv);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alertDiv);
                bsAlert.close();
            }, 5000);
        }
    </script>
</body>
</html>
