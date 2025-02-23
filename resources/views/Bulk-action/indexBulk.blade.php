@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Bulk Actions</div>
    </div>

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
        <!-- Bulk Actions Form -->
        <div class="col-md-6">
            <div class="card w-100 h-100 rounded-4 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('bulk-actions.upload') }}" method="POST" enctype="multipart/form-data" id="bulkActionForm">
                        @csrf
                        <div class="mb-3">
                            <label for="model" class="form-label">Select Model:</label>
                            <select name="model" id="model" class="form-select" required>
                                <option value="">-- Select a model --</option>
                                @foreach ($models as $model)
                                    <option value="{{ $model->id }}">{{ $model->model_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="csvFile" class="form-label">Upload CSV File:</label>
                            <input type="file" name="csvFile" id="csvFile" accept=".csv" required>
                            <small class="text-muted">
                                <a href="{{ asset('storage/cs_nodes_template.csv') }}" download class="text-gray-600">
                                    Download Template
                                </a>
                            </small>
                        </div>
                        <div class="mb-3">
                            <label for="action" class="form-label">Select Action:</label>
                            <select name="action" id="action" class="form-select" required>
                                <option value="">-- Select an action --</option>
                                <option value="set">Set Parameters</option>
                                <option value="get">Get Parameters</option>
                            </select>
                        </div>
                        <div class="mb-3 d-none" id="nodeValueContainer">
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
                            <input type="text" name="nodeTypeDetailed" id="nodeTypeDetailed" class="form-control" required readonly>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Submit</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Progress List -->
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
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        // ✅ Fetch Nodes on Model Selection
        $('#model').on('change', function () {
            var modelId = $(this).val();
            var nodePathSelect = $('#nodePath');
            var nodeTypeDetailed = $('#nodeTypeDetailed');

            // Reset fields if no model is selected
            if (!modelId) {
                nodePathSelect.html('<option value="">-- Select a node path --</option>');
                nodeTypeDetailed.val('');
                return;
            }

            // Fetch nodes via AJAX
            $.ajax({
                url: "{{ route('bulk-actions.nodes', ':modelId') }}".replace(':modelId', modelId),
                method: 'GET',
                success: function (nodes) {
                    nodePathSelect.html('<option value="">-- Select a node path --</option>');
                    
                    // Populate the select dropdown with node paths
                    nodes.forEach(function (node) {
                        nodePathSelect.append(
                            `<option value="${node.path}" data-node-type="${node.type}">${node.path}</option>`
                        );
                    });
                },
                error: function () {
                    alert('Error fetching nodes. Please try again.');
                }
            });
        });

        // ✅ Update "Node Type Detailed" on Node Selection
        $('#nodePath').on('change', function () {
            var selectedOption = $(this).find(':selected'); // Get selected option
            var nodeType = selectedOption.data('node-type') || ''; // Extract node type
            $('#nodeTypeDetailed').val(nodeType); // Update input field
        });

        // ✅ Toggle "New Value" field for SET action
        $('#action').on('change', function () {
            $('#nodeValueContainer').toggleClass('d-none', $(this).val() !== 'set');
        });

        // ✅ Confirm Bulk Action Submission
        $('#bulkActionForm').on('submit', function (e) {
            e.preventDefault();
            $('#confirmUploadModal').modal('show');
        });

        $('#confirmUploadBtn').on('click', function () {
            $('#bulkActionForm').off('submit').submit();
        });

        // ✅ Polling Function for Progress Updates (Auto Refresh)
        function pollProgress() {
            $('#progress-list li').each(function () {
                var progressId = $(this).attr('id').replace('progress-', ''); // Extract ID

                $.ajax({
                    url: "{{ route('bulk-actions.progress', ':progressId') }}".replace(':progressId', progressId),
                    method: 'GET',
                    success: function (data) {
                        var progressItem = $('#progress-' + progressId);
                        progressItem.find('.progress-status').text(data.status);
                        progressItem.find('.processed-count').text(data.processed);
                        progressItem.find('.success-count').text(data.success_count);
                        progressItem.find('.fail-count').text(data.fail_count);
                        progressItem.find('.not-found-count').text(data.not_found_count);
                    },
                    error: function () {
                        console.error("Error fetching progress for ID " + progressId);
                    }
                });
            });

            setTimeout(pollProgress, 5000); // Refresh every 5 seconds
        }

        // ✅ Start polling for progress updates
        pollProgress();
    });
</script>
@endsection

