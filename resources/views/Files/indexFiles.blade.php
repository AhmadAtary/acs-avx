@extends('layouts.app')

@section('content')
<div class="container mt-5">
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
  <div class="breadcrumb-title pe-3">Files Management</div>
  <div class="ms-auto">
      <!-- Add User Button -->
      @if (auth()->user()->access && auth()->user()->access->permissions['files_management']['create'])
      <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#createFileModal"><i class="bi bi-plus-lg me-2"></i>Add File</button>
      @endif
      
  </div>
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

    <!-- Add File Button -->
    
    <!-- Files Table -->
    <table class="table table-bordered">
    <thead>
        <tr>
            <th>Filename</th>
            <th>File Type</th>
            <th>OUI</th>
            <th>Product Class</th>
            <th>Version</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @if ($files->isEmpty())
            <tr>
                <td colspan="6" class="text-center">There are no files added Yet.</td>
            </tr>
        @else
            @foreach ($files as $file)
                <tr>
                    <td>{{ $file->filename }}</td>
                    <td>{{ $file->metadata['fileType'] ?? 'N/A' }}</td>
                    <td>{{ $file->metadata['oui'] ?? 'N/A' }}</td>
                    <td>{{ $file->metadata['productClass'] ?? 'N/A' }}</td>
                    <td>{{ $file->metadata['version'] ?? 'N/A' }}</td>
                    <td>
                        {{-- Uncomment the Edit button if you want editing functionality --}}
                        {{-- <button class="btn btn-warning btn-sm edit-file-btn"
                                data-id="{{ $file->_id }}"
                                data-filename="{{ $file->filename }}"
                                data-filetype="{{ $file->metadata['fileType'] ?? '' }}"
                                data-oui="{{ $file->metadata['oui'] ?? '' }}"
                                data-productclass="{{ $file->metadata['productClass'] ?? '' }}"
                                data-version="{{ $file->metadata['version'] ?? '' }}"
                                data-bs-toggle="modal"
                                data-bs-target="#editFileModal">
                            Edit
                        </button> --}}
                        @if (auth()->user()->access && auth()->user()->access->permissions['files_management']['delete'])
                        <form action="{{ route('files.destroy', $file->_id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>

</div>

<div class="modal fade" id="createFileModal" tabindex="-1" aria-labelledby="createFileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createFileModalLabel">Add File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- File Type Dropdown -->
                    <div class="mb-3">
                        <label for="fileType" class="form-label">File Type</label>
                        <select class="form-select" id="fileType" name="fileType" required>
                            <option value="" selectedOption>Select File Type</option>
                            <option value="1 Firmware Upgrade Image">Firmware Upgrade Image</option>
                            <option value="3 Vendor Configuration File">Vendor Configuration File</option>
                        </select>
                    </div>

                    <!-- Product Class Dropdown -->
                    <div class="mb-3">
                        <label for="productClass" class="form-label">Device Model</label>
                        <select class="form-select" id="productClass" name="productClass" required>
                                <option value="" selectedOption>Select Device Model</option>
                            @foreach ($models as $model)
                                <option value="{{ $model->product_class }}" data-oui="{{ $model->oui }}">
                                    {{ $model->model_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    <!-- OUI Input -->
                    <div class="mb-3">
                        <label for="oui" class="form-label">OUI</label>
                        <input type="text" class="form-control" id="oui" name="oui" readonly required>
                    </div>

                    

                    <!-- Version Input -->
                    <div class="mb-3">
                        <label for="version" class="form-label">Version</label>
                        <input type="text" class="form-control" id="version" name="version" required>
                    </div>

                    <!-- File Upload -->
                    <div class="mb-3">
                        <label for="file" class="form-label">Upload File</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".bin,.img,.iso,.fw" required>
                        <small class="form-text text-muted">Only software image files are allowed.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add File</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Edit File Modal -->
<div class="modal fade" id="editFileModal" tabindex="-1" aria-labelledby="editFileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST" id="editFileForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editFileModalLabel">Edit File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-oui" class="form-label">OUI</label>
                        <input type="text" class="form-control" id="edit-oui" name="metadata[oui]" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-productClass" class="form-label">Product Class</label>
                        <input type="text" class="form-control" id="edit-productClass" name="metadata[productClass]" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-version" class="form-label">Version</label>
                        <input type="text" class="form-control" id="edit-version" name="metadata[version]" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('.edit-file-btn').forEach(button => {
        button.addEventListener('click', function () {
            const fileId = this.dataset.id;
            const oui = this.dataset.oui;
            const productClass = this.dataset.productclass;
            const version = this.dataset.version;

            // Populate the edit form
            document.getElementById('editFileForm').action = `/files/update/${fileId}`;
            document.getElementById('edit-oui').value = oui;
            document.getElementById('edit-productClass').value = productClass;
            document.getElementById('edit-version').value = version;
        });
    });
</script>
<script>
    document.getElementById('productClass').addEventListener('change', function () {
        // Get the selected option
        const selectedOption = this.options[this.selectedIndex];
        
        // Get the OUI value from the data attribute
        const oui = selectedOption.getAttribute('data-oui');
        
        // Set the OUI input value
        document.getElementById('oui').value = oui;
    });
</script>

@endsection
