@extends('layouts.app')
@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
  <div class="breadcrumb-title pe-3">Users Management</div>
  <div class="ms-auto">
      <!-- Add User Button -->
        @if (auth()->user()->access && auth()->user()->access->permissions['create_user'])
            <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="bi bi-plus-lg me-2"></i>Add User
            </button>
        @endif
  </div>
</div>
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-3">
    <div class="col-auto">
        <div class="position-relative">
            <input id="search-input" class="form-control px-5" type="search" placeholder="Search Users">
            <span class="material-icons-outlined position-absolute ms-3 translate-middle-y start-0 top-50 fs-5">search</span>
        </div>
    </div>
</div><!--end row-->
<div class="card mt-4">
    <div class="card-body">
        <div class="customer-table">
            <div class="table-responsive white-space-nowrap">
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
                            <td>
                                <p class="mb-0 customer-name fw-bold">{{ $user->name }}</p>
                            </td>
                            <td>
                                <a href="javascript:;" class="font-text1">{{ $user->email }}</a>
                            </td>
                            <td>
                            @if(($user->access)->role == 'owner')
                            <span class="badge bg-info">
                                    Super-admin
                                </span>
                            @else
                                <span class="badge bg-info">
                                    {{ ucfirst(optional($user->access)->role ?? 'N/A') }}
                                </span>
                            @endif
                                
                            </td>
                            <td>
                                {{ $user->created_at->format('M d, Y H:i A') }}
                            </td>
                            <td>
                                @if (auth()->user()->access && auth()->user()->access->permissions['update_user'])
                                    <button class="btn btn-sm btn-warning update-user-btn"
                                            data-id="{{ $user->id }}"
                                            data-name="{{ $user->name }}"
                                            data-email="{{ $user->email }}"
                                            data-role="{{ optional($user->access)->role }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#updateUserModal">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                @endif

                                @if (auth()->user()->access && auth()->user()->access->permissions['delete_user'])
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
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
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createUserModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="create-user-form" method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="modal-body">
                    <!-- User Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Role -->
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="cs" {{ old('role', 'cs') == 'cs' ? 'selected' : '' }}>Customer-Support</option>
                            <option value="eng" {{ old('role') == 'eng' ? 'selected' : '' }}>Engineer</option>
                            <option value="owner" {{ old('role') == 'owner' ? 'selected' : '' }}>Super-admin</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Permissions -->
                    <div class="mb-3">
                        <label for="permissions" class="form-label">Permissions</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="create_user" name="permissions[create_user]" value="1">
                            <label class="form-check-label" for="create_user">Create User</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="update_user" name="permissions[update_user]" value="1">
                            <label class="form-check-label" for="update_user">Update User</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="delete_user" name="permissions[delete_user]" value="1">
                            <label class="form-check-label" for="delete_user">Delete User</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="view_user" name="permissions[view_user]" value="1">
                            <label class="form-check-label" for="view_user">View User</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update User Modal -->
<div class="modal fade" id="updateUserModal" tabindex="-1" aria-labelledby="updateUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateUserModalLabel">Update User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="update-user-form" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Name -->
                    <div class="mb-3">
                        <label for="update-name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="update-name" name="name" required>
                    </div>
                    <!-- Email -->
                    <div class="mb-3">
                        <label for="update-email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="update-email" name="email" required>
                    </div>
                    <!-- Role -->
                    <div class="mb-3">
                        <label for="update-role" class="form-label">Role</label>
                        <select class="form-select" id="update-role" name="role" required>
                            <option value="cs">Customer-Support</option>
                            <option value="eng">Engineer</option>
                            <option value="owner">Super-admin</option>
                        </select>
                    </div>
                    <!-- Permissions -->
                    <div class="mb-3">
                        <label for="permissions" class="form-label">Permissions</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="update-create_user" name="permissions[create_user]" value="1">
                            <label class="form-check-label" for="update-create_user">Create User</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="update-update_user" name="permissions[update_user]" value="1">
                            <label class="form-check-label" for="update-update_user">Update User</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="update-delete_user" name="permissions[delete_user]" value="1">
                            <label class="form-check-label" for="update-delete_user">Delete User</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="update-view_user" name="permissions[view_user]" value="1">
                            <label class="form-check-label" for="update-view_user">View User</label>
                        </div>
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
    // Search functionality
    document.getElementById('search-input').addEventListener('input', function () {
        const query = this.value.toLowerCase();
        document.querySelectorAll('#users-table tbody tr').forEach(function (row) {
            const name = row.querySelector('td:first-child p').textContent.toLowerCase();
            row.style.display = name.includes(query) ? '' : 'none';
        });
    });
    // Update user modal population
    document.querySelectorAll('.update-user-btn').forEach(function (button) {
    button.addEventListener('click', function () {
        const userId = this.dataset.id;
        const permissions = JSON.parse(this.dataset.permissions || '{}');

        document.getElementById('update-user-form').action = `/dashboard/users/${userId}`;
        document.getElementById('update-name').value = this.dataset.name;
        document.getElementById('update-email').value = this.dataset.email;
        document.getElementById('update-role').value = this.dataset.role;

        // Populate permissions
        document.getElementById('update-create_user').checked = permissions.create_user || false;
        document.getElementById('update-update_user').checked = permissions.update_user || false;
        document.getElementById('update-delete_user').checked = permissions.delete_user || false;
        document.getElementById('update-view_user').checked = permissions.view_user || false;
    });
});

</script>
@endsection