@extends('layouts.app')

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Users Management</div>
    <div class="ms-auto">
        @if (auth()->user()->access && auth()->user()->access->permissions['user_management']['create'])
            <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="bi bi-plus-lg me-2"></i> Add User
            </button>
        @endif
    </div>
</div>

<!-- Alerts -->
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card mt-4">
    <div class="card-body">
        <table class="table align-middle" id="users-table">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($users as $user)
                <tr>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge bg-info">{{ ucfirst(optional($user->access)->role ?? 'N/A') }}</span></td>
                    <td>{{ $user->created_at->format('M d, Y H:i A') }}</td>
                    <td>
                        @if ($user->access->role != "owner")
                            <div class="dropdown">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="actionDropdown{{ $user->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                    Actions
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="actionDropdown{{ $user->id }}">
                                    @if (auth()->user()->access && auth()->user()->access->permissions['user_management']['edit'])
                                        <li>
                                            <button class="dropdown-item edit-user-btn"
                                                    data-id="{{ $user->id }}"
                                                    data-name="{{ $user->name }}"
                                                    data-email="{{ $user->email }}"
                                                    data-role="{{ optional($user->access)->role }}"
                                                    data-permissions='@json($user->access->permissions ?? [])'
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editUserModal">
                                                <i class="bi bi-pencil"></i> Edit User
                                            </button>
                                        </li>
                                    @endif
                                    @if (auth()->user()->access && auth()->user()->access->permissions['user_management']['delete'])
                                        <li>
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item" onclick="return confirm('Are you sure?')">
                                                    <i class="bi bi-trash"></i> Delete User
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                    <li>
                                        <button class="dropdown-item check-logs-btn"
                                                data-id="{{ $user->id }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#logsModal">
                                            <i class="bi bi-file-earmark-text"></i> User Logs
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="create-user-form" method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select user-role" id="create-role" name="role" required>


                            <option value="cs">Customer Support</option>
                            @if(auth()->user()->access->role == 'owner')
                                <option value="eng">Engineer</option>
                                <option value="owner">Super Admin</option>
                            @endif
                        </select>
                    </div>

                    <div class="mb-3" id="deviceAssignmentSection">
                        <label class="form-label">Assign Devices?</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="assign_devices" 
                                id="assignDevicesYes" value="1">
                            <label class="form-check-label btn btn-outline-primary" for="assignDevicesYes">
                                Yes
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="assign_devices" 
                                id="assignDevicesNo" value="0" checked>
                            <label class="form-check-label btn btn-outline-secondary" for="assignDevicesNo">
                                No
                            </label>
                        </div>
                    </div> 
                    <!-- CSV Upload Section (Initially Hidden) -->
                    <div class="mb-3 d-none" id="csvUploadSection">
                        <label class="form-label">Upload Device Serial Numbers (CSV)</label>
                        <input type="file" class="form-control" 
                               name="device_csv" 
                               id="deviceCsv"
                               accept=".csv">
                        <small class="form-text text-muted">
                            CSV file should contain one serial number per line
                        </small>
                        <small class="text-muted">
                    <a href="{{ asset('storage/devices_serial.csv') }}" download class="text-gray-600">
                        Download Template
                    </a>
                </small>
                    </div>

                    <div id="create-permissions-container" class="mt-3 d-none">
                        <label>Permissions</label>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Section</th>
                                    <th>View</th>
                                    <th>Create</th>
                                    <th>Delete</th>
                                    <th>Edit</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                            @foreach(['bulk_actions', 'files_management', 'models_management', 'user_management'] as $section)
                                    <tr>
                                        <td>{{ ucwords(str_replace('_', ' ', $section)) }}</td>
                                        @foreach(['view', 'create', 'delete', 'edit'] as $action)
                                            <td>
                                                <input type="checkbox"
                                                       name="permissions[{{ $section }}][{{ $action }}]"
                                                       value="1"
                                                       class="edit-permission-checkbox"
                                                       data-section="{{ $section }}"
                                                       data-action="{{ $action }}">
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg"> {{-- Made it wider --}}
        <div class="modal-content">
            <form id="edit-user-form" method="POST" action="{{ route('users.update', $user->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                <input type="hidden" id="edit-user-id" name="user_id">

                    {{-- Basic Info --}}
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit-email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select user-role" id="edit-role" name="role" required>
                            <option value="cs">Customer Support</option>
                            <option value="eng">Engineer</option>
                            <option value="owner">Super Admin</option>
                        </select>
                    </div>

                    {{-- Assign Devices --}}
                    <div class="mb-3" id="editDeviceAssignmentSection">
                    <label class="form-label">Assign Devices?</label>
                    
                    <!-- Yes Option (Active when assigned) -->
                    <div class="form-check form-check-inline">
                        <input 
                            style="display: none" 
                            class="form-check-input" 
                            type="radio" 
                            name="assign_devices" 
                            value="1" 
                            id="editAssignDevicesYes"
                            {{ isset($user->access->permissions['assign_devices']['assign']) && $user->access->permissions['assign_devices']['assign'] ? 'checked' : '' }}>
                        <label 
                            class="form-check-label btn btn-outline-primary {{ isset($user->access->permissions['assign_devices']['assign']) && $user->access->permissions['assign_devices']['assign'] ? 'active' : '' }}" 
                            for="editAssignDevicesYes">
                            Yes
                        </label>
                    </div>
                    
                    <!-- No Option (Active when not assigned) -->
                    <div class="form-check form-check-inline">
                        <input 
                            style="display: none" 
                            class="form-check-input" 
                            type="radio" 
                            name="assign_devices" 
                            value="0" 
                            id="editAssignDevicesNo">
                        <label 
                            class="form-check-label btn btn-outline-secondary {{ isset($user->access->permissions['assign_devices']['assign']) && $user->access->permissions['assign_devices']['assign'] == 0 ? 'active' : '' }}" 
                            for="editAssignDevicesNo">
                            No
                        </label>
                    </div>
                    </div>



                    {{-- Permissions Table --}}
                    <div id="edit-permissions-container" class="mt-3 d-none">
                        <label>Permissions</label>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Section</th>
                                    <th>View</th>
                                    <th>Create</th>
                                    <th>Delete</th>
                                    <th>Edit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(['bulk_actions', 'files_management', 'models_management', 'user_management'] as $section)
                                    <tr>
                                        <td>{{ ucwords(str_replace('_', ' ', $section)) }}</td>
                                        @foreach(['view', 'create', 'delete', 'edit'] as $action)
                                            <td>
                                                <input type="checkbox"
                                                       name="permissions[{{ $section }}][{{ $action }}]"
                                                       value="1"
                                                       class="edit-permission-checkbox"
                                                       data-section="{{ $section }}"
                                                       data-action="{{ $action }}"
                                                       {{ isset($user->access->permissions[$section][$action]) && $user->access->permissions[$section][$action] ? 'checked' : '' }}>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- 🔒 Device Access Section --}}
                    <div id="editCsvUploadSection" class="mt-4 d-none">

                        {{-- Full Access Form --}}
                        <div class="alert alert-info p-2 mb-2">
                            <small>⚠️ Grant user access to <strong>all devices</strong>.</small>
                        </div>
                        <button type="submit" formaction="{{ route('assign.devices.full-access') }}" class="btn btn-warning w-100 mb-3">
                            Grant Full Access
                        </button>

                        {{-- Upload CSV --}}
                        <div class="mb-3">
                            <label class="form-label">Upload Device Serial Numbers (CSV)</label>
                            <input type="file" class="form-control" name="device_csv" accept=".csv">
                            <small class="form-text text-muted">Uploading a new file will replace the existing list.</small><br>
                            <small class="text-muted">
                                <a href="{{ asset('storage/devices_serial.csv') }}" download class="text-gray-600">
                                    Download Template
                                </a>
                            </small>
                        </div>

                        {{-- Export Existing Devices --}}
                        <div class="d-grid">
                            <a href="{{ route('export.devices.csv', ['user' => $user->id]) }}" class="btn btn-outline-secondary">
                                Export Existing Device List
                            </a>
                        </div>

                    </div>

                </div> {{-- End modal-body --}}

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>

            </form>
        </div>
    </div>
</div>



<!-- Logs Modal -->
<div class="modal fade" id="logsModal" tabindex="-1" aria-labelledby="logsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logsModalLabel">User Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="logs-container">
                    <!-- Logs will be loaded here -->
                    <p class="text-center">Loading logs...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="assignDevicesModal" tabindex="-1" aria-labelledby="assignDevicesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            
            <div class="modal-header">
                <h5 class="modal-title" id="assignDevicesModalLabel">Device Access Management</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                {{-- 🔒 Full Access Form --}}
                <form method="POST" action="{{ route('assign.devices.full-access') }}" class="mb-3">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <div class="alert alert-info p-2 mb-2">
                        <small>⚠️ Grant user access to <strong>all devices</strong>.</small>
                    </div>
                    <button type="submit" class="btn btn-warning w-100">Grant Full Access</button>
                </form>

                <hr>

                {{-- 📤 Upload CSV Form --}}
                <form method="POST" action="{{ route('assign.devices.csv') }}" enctype="multipart/form-data" class="mb-3">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <div class="mb-2">
                        <label for="device_file" class="form-label">Upload New Device List (CSV)</label>
                        <input type="file" class="form-control" name="device_file" id="device_file" accept=".csv" required>
                        <div class="form-text">Uploading a new file will replace the existing list.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Upload Device List</button>
                </form>

                <hr>

                {{-- 📥 Export CSV --}}
                <div class="d-grid">
                    <a href="{{ route('export.devices.csv', ['user' => $user->id]) }}" class="btn btn-outline-secondary">
                        Export Existing Device List
                    </a>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>




@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Function to toggle permissions visibility
    function togglePermissionsVisibility(selectElement, permissionsContainer) {
        if (selectElement.value === 'cs') {
            permissionsContainer.classList.add('d-none');
            permissionsContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        } else {
            permissionsContainer.classList.remove('d-none');
        }
    }

    // Create Modal Role Change Handler
    const createRoleSelect = document.getElementById('create-role');
    const createPermissionsContainer = document.getElementById('create-permissions-container');
    createRoleSelect.addEventListener('change', () => {
        togglePermissionsVisibility(createRoleSelect, createPermissionsContainer);
    });
    // Initial check for create modal
    togglePermissionsVisibility(createRoleSelect, createPermissionsContainer);

    // Edit Modal Role Change Handler
    const editRoleSelect = document.getElementById('edit-role');
    const editPermissionsContainer = document.getElementById('edit-permissions-container');
    editRoleSelect.addEventListener('change', () => {
        togglePermissionsVisibility(editRoleSelect, editPermissionsContainer);
    });

    // Edit Button Handler
    document.querySelectorAll(".edit-user-btn").forEach(button => {
        button.addEventListener("click", function () {
            const userId = this.dataset.id;
            document.getElementById("edit-user-form").action = `/dashboard/users/${userId}`;
            document.getElementById("edit-user-id").value = userId;
            document.getElementById("edit-name").value = this.dataset.name;
            document.getElementById("edit-email").value = this.dataset.email;
            document.getElementById("edit-role").value = this.dataset.role;

            const permissions = JSON.parse(this.dataset.permissions || '{}');
            document.querySelectorAll(".edit-permission-checkbox").forEach(cb => {
                const section = cb.dataset.section;
                const action = cb.dataset.action;
                cb.checked = permissions[section]?.[action] === true;
            });

            // Initial toggle for edit modal
            togglePermissionsVisibility(editRoleSelect, editPermissionsContainer);
        });
    });
});
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
    // Handle Check Logs button click
    document.querySelectorAll(".check-logs-btn").forEach(button => {
        button.addEventListener("click", function () {
            const userId = this.dataset.id;
            const logsContainer = document.getElementById("logs-container");

            // Clear the previous logs and show loading text
            logsContainer.innerHTML = "<p class='text-center'>Loading logs...</p>";

            // Fetch logs for the selected user
            fetch(`/users/${userId}/logs`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        let logsHtml = '<table class="table table-bordered"><thead><tr><th>Date</th><th>Action</th><th>Details</th></tr></thead><tbody>';
                        data.forEach(log => {
                            logsHtml += `
                                <tr>
                                    <td>${new Date(log.created_at).toLocaleString()}</td>
                                    <td>${log.action}</td>
                                    <td>${log.response}</td>
                                </tr>
                            `;
                        });
                        logsHtml += '</tbody></table>';
                        logsContainer.innerHTML = logsHtml;
                    } else {
                        logsContainer.innerHTML = "<p class='text-center'>No logs found for this user.</p>";
                    }
                })
                .catch(error => {
                    logsContainer.innerHTML = "<p class='text-center text-danger'>Error fetching logs.</p>";
                });
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Toggle CSV section for both modals
    function toggleCsvSection(assignId, sectionId) {
        document.querySelectorAll(assignId).forEach(radio => {
            radio.addEventListener('change', () => {
                const csvSection = document.querySelector(sectionId);
                if (radio.checked && radio.value === '1') {
                    csvSection.classList.remove('d-none');
                } else {
                    csvSection.classList.add('d-none');
                }
            });
        });
    }

    toggleCsvSection('#assignDevicesYes, #assignDevicesNo', '#csvUploadSection');
    toggleCsvSection('#editAssignDevicesYes, #editAssignDevicesNo', '#editCsvUploadSection');

    // Toggle permissions section based on role selection
    function togglePermissions(roleSelectorId, containerId) {
        const roleSelect = document.querySelector(roleSelectorId);
        const permissionsContainer = document.querySelector(containerId);

        if (!roleSelect || !permissionsContainer) return;

        function handleRoleChange() {
            if (roleSelect.value === 'eng') {
                permissionsContainer.classList.remove('d-none');
            } else {
                permissionsContainer.classList.add('d-none');
            }
        }

        roleSelect.addEventListener('change', handleRoleChange);
        handleRoleChange(); // initialize
    }

    togglePermissions('#create-role', '#create-permissions-container');
    togglePermissions('#edit-role', '#edit-permissions-container');
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
const assignYes = document.getElementById('editAssignDevicesYes');
const assignNo = document.getElementById('editAssignDevicesNo');
const deviceSection = document.getElementById('editCsvUploadSection');

function toggleDeviceSection() {
    if (assignYes.checked) {
        deviceSection.classList.remove('d-none');
    } else {
        deviceSection.classList.add('d-none');
    }
}

assignYes.addEventListener('change', toggleDeviceSection);
assignNo.addEventListener('change', toggleDeviceSection);

// Initial check on load
toggleDeviceSection();
});

</script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Get the radio buttons and labels
    const yesButton = document.getElementById("editAssignDevicesYes");
    const noButton = document.getElementById("editAssignDevicesNo");
    const yesLabel = document.querySelector('label[for="editAssignDevicesYes"]');
    const noLabel = document.querySelector('label[for="editAssignDevicesNo"]');

    // Add event listeners to update active class when the buttons are clicked
    yesButton.addEventListener("change", function() {
        if (yesButton.checked) {
            yesLabel.classList.add('active');
            noLabel.classList.remove('active');
        }
    });

    noButton.addEventListener("change", function() {
        if (noButton.checked) {
            noLabel.classList.add('active');
            yesLabel.classList.remove('active');
        }
    });
});
</script>


@endsection