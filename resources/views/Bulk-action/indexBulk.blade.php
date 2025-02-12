@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <h1>Bulk Actions</h1>
    <!-- Success and Error Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

<div class="row">
<div class="col-md-6">
        <div class="card w-100 h-100 rounded-4">
            <div class="card-body">
                <form action="{{ route('bulk-actions.upload') }}" method="POST" enctype="multipart/form-data" id="bulkActionForm">
                    @csrf
                    <div class="mb-3">
                        <label for="model" class="form-label">Select Model:</label>
                        <select name="model" id="model" class="form-select" required>
                            <option value="">-- Select a model --</option>
                            @foreach ($models as $model)
                                <option value="{{ $model->Model }}">{{ $model->Model }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="csvFile" class="form-label">Upload CSV File:</label>
                        <input type="file" name="csvFile" id="csvFile" accept=".csv" required>
                    </div>
                    <div class="mb-3">
                        <label for="action" class="form-label">Select Action:</label>
                        <select name="action" id="action" class="form-select" required>
                            <option value="">-- Select an action --</option>
                            <option value="set">Set Parameters</option>
                            <option value="get">Get Parameters</option>
                        </select>
                    </div>
                    <div class="mb-3" id="nodeValueContainer">
                        <label for="newValue" class="form-label">New Value (for SET action):</label>
                        <input type="text" name="newValue" id="newValue" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="nodePath" class="form-label">Node Path:</label>
                        <select name="nodePath" id="nodePath" class="form-select" required>
                            <option value="">-- Select a node path --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="nodeTypeDetailed" class="form-label">Node Type Detailed:</label>
                        <input type="text" name="nodeTypeDetailed" id="nodeTypeDetailed" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
    <!-- Bulk Actions Form -->


    <div class="col-md-6">
    <div class="card w-100 h-100 rounded-4 shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-center mb-4">Progress List</h2>
            @if (count($progresses) > 0)
                <ul class="list-group" id="progress-list">
                    @foreach ($progresses as $progress)
                        <li id="progress-{{ $progress->id }}" class="list-group-item d-flex flex-column justify-content-start align-items-start mb-3 rounded">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h5 class="mb-1">ID: {{ $progress->id }}</h5>
                                <span class="badge bg-primary progress-status">{{ ucfirst($progress->status) }}</span>
                            </div>
                            <p class="mb-1">
                                <strong>Processed:</strong> <span class="processed-count">{{ $progress->processed }}</span> / {{ $progress->total }}<br>
                                <strong>Success:</strong> <span class="success-count">{{ $progress->success_count }}</span><br>
                                <strong>Fail:</strong> <span class="fail-count">{{ $progress->fail_count }}</span><br>
                                <strong>Not Found:</strong> <span class="not-found-count">{{ $progress->not_found_count }}</span>
                            </p>
                            <div class="d-flex gap-2 mt-3">
                                <button class="btn btn-warning btn-sm pause-btn" data-progress-id="{{ $progress->id }}">Pause</button>
                                <button class="btn btn-info btn-sm resume-btn" data-progress-id="{{ $progress->id }}">Resume</button>
                                <button class="btn btn-danger btn-sm stop-btn" data-progress-id="{{ $progress->id }}">Stop</button>
                                <button class="btn btn-danger btn-sm delete-btn" data-progress-id="{{ $progress->id }}">Delete</button>
                                <a href="{{ route('bulk-actions.export', $progress->id) }}" class="btn btn-success btn-sm">
                                    Export Report
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="alert alert-info text-center mt-4">
                    No progress records found.
                </div>
            @endif
        </div>
    </div>
</div>

</div>

</div>
</div>



</div>
<!-- Modal for CSV Upload Confirmation -->
<div class="modal fade" id="confirmUploadModal" tabindex="-1" aria-labelledby="confirmUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmUploadModalLabel">Confirm Bulk Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to start the bulk action?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmUploadBtn">Start Bulk Action</button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        // Handle model selection to fetch nodes
        $('#model').on('change', function () {
            var modelId = $(this).val();
            $.ajax({
                url: "{{ route('bulk-actions.nodes', ':modelId') }}".replace(':modelId', modelId),
                method: 'GET',
                success: function (nodes) {
                    var nodePathSelect = $('#nodePath');
                    nodePathSelect.empty();
                    nodePathSelect.append('<option value="">-- Select a node path --</option>');
                    nodes.forEach(function (node) {
                        nodePathSelect.append('<option value="' + node.Path + '" data-node-type="' + node.NodeType + '">' + node.Path + '</option>');
                    });
                },
                error: function (xhr) {
                    alert(xhr.responseJSON.message);
                }
            });
        });

        // Handle node path selection to set node type detailed
        $('#nodePath').on('change', function () {
            var selectedOption = $(this).find('option:selected');
            var nodeType = selectedOption.data('node-type');
            $('#nodeTypeDetailed').val(nodeType);
        });

        // Handle action selection to toggle new value field
        $('#action').on('change', function () {
            var action = $(this).val();
            if (action === 'get') {
                $('#nodeValueContainer').addClass('d-none');
            } else {
                $('#nodeValueContainer').removeClass('d-none');
            }
        });

        // Handle form submission
        $('#bulkActionForm').on('submit', function (e) {
            e.preventDefault();
            $('#confirmUploadModal').modal('show');
        });

        // Confirm and submit the form
        $('#confirmUploadBtn').on('click', function () {
            $('#bulkActionForm').off('submit').submit();
        });

        // Pause button click handler
        $('.pause-btn').on('click', function () {
            var progressId = $(this).data('progress-id');
            $.ajax({
                url: "{{ route('bulk-actions.pause', ':progressId') }}".replace(':progressId', progressId),
                method: 'GET',
                success: function (response) {
                    $('#progress-' + progressId).find('.progress-status').text(response.status);
                    alert(response.message);
                },
                error: function (xhr) {
                    alert(xhr.responseJSON.message);
                }
            });
        });

        // Resume button click handler
        $('.resume-btn').on('click', function () {
            var progressId = $(this).data('progress-id');
            $.ajax({
                url: "{{ route('bulk-actions.resume', ':progressId') }}".replace(':progressId', progressId),
                method: 'GET',
                success: function (response) {
                    $('#progress-' + progressId).find('.progress-status').text(response.status);
                    alert(response.message);
                },
                error: function (xhr) {
                    alert(xhr.responseJSON.message);
                }
            });
        });

        // Delete button click handler
        $('.delete-btn').on('click', function () {
            var progressId = $(this).data('progress-id');
            $.ajax({
                url: "{{ route('bulk-actions.delete', ':progressId') }}".replace(':progressId', progressId),
                method: 'GET',
                success: function (response) {
                    $('#progress-' + progressId).remove();
                    if ($('#progress-list li').length === 0) {
                        $('#progressCard').addClass('d-none');
                    }
                    alert(response.message);
                },
                error: function (xhr) {
                    alert(xhr.responseJSON.message);
                }
            });
        });

        // Polling for progress updates
        function pollProgress() {
            // Loop through each progress item
            $('#progress-list li').each(function () {
                var progressId = $(this).attr('id').replace('progress-', ''); // Extract the progress ID from the element's ID

                // Perform an AJAX request to fetch progress details
                $.ajax({
                    url: "{{ route('bulk-actions.progress', ':progressId') }}".replace(':progressId', progressId),
                    method: 'GET',
                    success: function (data) {
                        // Update the UI with the returned data
                        var progressItem = $('#progress-' + progressId);
                        progressItem.find('.progress-status').text(data.status);
                        progressItem.find('.processed-count').text(data.processed);
                        progressItem.find('.success-count').text(data.success_count);
                        progressItem.find('.fail-count').text(data.fail_count);
                        progressItem.find('.not-found-count').text(data.not_found_count);
                    },
                    error: function (xhr) {
                        console.error("Error fetching progress for ID " + progressId + ": " + xhr.responseText);
                    }
                });
            });

            // Continue polling every 5 seconds
            setTimeout(pollProgress, 5000);
        }

        // Start polling for progress updates after the DOM is ready
        $(document).ready(function () {
            pollProgress();
        });

    });
</script>
@endsection