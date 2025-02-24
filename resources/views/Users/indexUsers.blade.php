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
                        @if (auth()->user()->access && auth()->user()->access->permissions['user_management']['edit'])
                            <button class="btn btn-sm btn-warning edit-user-btn"
                                    data-id="{{ $user->id }}"
                                    data-name="{{ $user->name }}"
                                    data-email="{{ $user->email }}"
                                    data-role="{{ optional($user->access)->role }}"
                                    data-permissions='@json($user->access->permissions ?? [])'
                                    data-bs-toggle="modal"
                                    data-bs-target="#editUserModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                        @endif
                        @if (auth()->user()->access && auth()->user()->access->permissions['user_management']['delete'])
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="create-user-form" method="POST" action="{{ route('users.store') }}">
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
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="edit-user-form" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit-user-id" name="user_id">
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
                            <tbody id="edit-permissions-table">
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
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

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
@endsection