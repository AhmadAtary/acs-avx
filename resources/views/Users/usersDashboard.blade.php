@extends('layouts.app')

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
  <div class="breadcrumb-title pe-3">Users Management</div>
  <div class="ms-auto">
      <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#createUserModal">
          <i class="bi bi-plus-lg me-2"></i>Add User
      </button>
  </div>
</div>

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
                                    <span class="badge bg-info">{{ ucfirst($user->role) }}</span>
                                </td>
                                <td>
                                    {{ $user->created_at->format('M d, Y H:i A') }}
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning update-user-btn" data-id="{{ $user->id }}" data-name="{{ $user->name }}" data-email="{{ $user->email }}" data-role="{{ $user->role }}" data-bs-toggle="modal" data-bs-target="#updateUserModal">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
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
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="cs">Customer-Support</option>
                            <option value="eng">Engineer</option>
                            <option value="owner">Owner</option>
                        </select>
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
                    <div class="mb-3">
                        <label for="update-name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="update-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="update-email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="update-email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="update-role" class="form-label">Role</label>
                        <select class="form-select" id="update-role" name="role" required>
                            <option value="cs">Customer-Support</option>
                            <option value="eng">Engineer</option>
                            <option value="owner">Owner</option>
                        </select>
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
<script src="{{ asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/plugins/metismenu/metisMenu.min.js') }}"></script>
<script src="{{ asset('assets/plugins/simplebar/js/simplebar.min.js')}}"></script>
<script src="{{ asset('assets/js/main.js')}}"></script>
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
            document.getElementById('update-user-form').action = `/users/${userId}`;
            document.getElementById('update-name').value = this.dataset.name;
            document.getElementById('update-email').value = this.dataset.email;
            document.getElementById('update-role').value = this.dataset.role;
        });
    });
</script>
@endsection
