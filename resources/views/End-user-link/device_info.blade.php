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
        #simplePopup {
            transition: opacity 0.3s ease-in-out;
            opacity: 0;
            transform: translateY(-20px);
        }
        #simplePopup.show {
            opacity: 1;
            transform: translateY(0);
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
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1>Device Info: {{ $device['_deviceId']['_SerialNumber'] ?? 'Unknown' }}</h1>
            </div>
            <div class="col-md-6 text-end">

                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateLinkModal">
                    Generate End-User Link
                </button>
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


                                        <form method="POST" action="{{ route('node.update') }}">
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            function showLoadingOverlay() {
                document.getElementById('loadingOverlay').style.display = 'block';
            }

            function hideLoadingOverlay() {
                document.getElementById('loadingOverlay').style.display = 'none';
            }

            function showSimplePopup(message) {
                const popup = document.getElementById('simplePopup');
                const popupMessage = document.getElementById('popupMessage');
                popupMessage.textContent = message;
                popup.style.display = 'block';
                popup.classList.add('show');
                setTimeout(hideSimplePopup, 3000);
            }

            function hideSimplePopup() {
                const popup = document.getElementById('simplePopup');
                popup.classList.remove('show');
                setTimeout(() => {
                    popup.style.display = 'none';
                }, 300);
            }

        //     document.querySelectorAll('form').forEach(form => {
        //         form.addEventListener('submit', function (e) {
        //             e.preventDefault();
        //             showLoadingOverlay();
        //             fetch(this.action, {
        //                 method: 'POST',
        //                 body: new FormData(this),
        //                 headers: {
        //                     'X-CSRF-TOKEN': '{{ csrf_token() }}',
        //                     'Accept': 'application/json'
        //                 }
        //             })
        //             .then(response => {
        //                 hideLoadingOverlay();
        //                 if (!response.ok) {
        //                     throw new Error('Network response was not ok');
        //                 }
        //                 return response.json();
        //             })
        //             .then(data => {
        //                 showSimplePopup(data.message || (data.success ? 'Action completed successfully.' : 'Failed to complete action.'));
        //                 if (data.success && form.id === 'generateLinkForm') {
        //                     navigator.clipboard.writeText(document.getElementById('link').value);
        //                     showSimplePopup('Link copied to clipboard!');
        //                 }
        //             })
        //             .catch(error => {
        //                 hideLoadingOverlay();
        //                 showSimplePopup('An error occurred: ' + error.message);
        //                 console.error('Error:', error);
        //             });
        //         });
        //     });
        // });
        document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        // Ensure url_Id is not a DOM object
        const urlIdInput = form.querySelector('input[name="url_Id"]');
        if (!urlIdInput || !urlIdInput.value || urlIdInput.value.includes('object')) {
            showSimplePopup('Invalid device ID in form submission.');
            return;
        }
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
            hideLoadingOverlay();
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            showSimplePopup(data.message || (data.success ? 'Action completed successfully.' : 'Failed to complete action.'));
            if (data.success && form.id === 'generateLinkForm') {
                navigator.clipboard.writeText(document.getElementById('link').value);
                showSimplePopup('Link copied to clipboard!');
            }
        })
        .catch(error => {
            hideLoadingOverlay();
            showSimplePopup('An error occurred: ' + error.message);
            console.error('Error:', error);
        });
    });
});
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const urlIdInput = form.querySelector('input[name="url_Id"]');
        if (!urlIdInput || !urlIdInput.value || urlIdInput.value.includes('object')) {
            showSimplePopup('Invalid device ID in form submission.');
            return;
        }
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
            hideLoadingOverlay();
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'Network response was not ok');
                });
            }
            return response.json();
        })
        .then(data => {
            showSimplePopup(data.message || (data.success ? 'Action completed successfully.' : 'Failed to complete action.'));
            if (data.success && form.id === 'generateLinkForm') {
                navigator.clipboard.writeText(document.getElementById('link').value);
                showSimplePopup('Link copied to clipboard!');
            }
            // Update node values in the UI for GET requests
            if (data.success && data.results) {
                Object.keys(data.results).forEach(path => {
                    const input = document.querySelector(`input[name="nodes[${path}][value]"]`);
                    if (input && data.results[path].value) {
                        input.value = data.results[path].value;
                    }
                });
            }
        })
        .catch(error => {
            hideLoadingOverlay();
            showSimplePopup('Error: ' + error.message);
            console.error('Error:', error);
        });
    });
});
    </script>
</body>
</html>
