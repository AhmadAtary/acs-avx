@extends('layouts.app')

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Device Model Management</div>
</div>

<div class="row">
    <div class="col-12 col-lg-8">
        <form method="POST" action="{{ route('device-models.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="card">
                <div class="card-body">
                    <!-- Device Model Basic Info -->
                    <div class="mb-4">
                        <h5 class="mb-3">Add New Model Information</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Model Name</label>
                                <input type="text" name="model_name" class="form-control" placeholder="Enter model name" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Product Class</label>
                                <input type="text" name="product_class" class="form-control" placeholder="Enter product class" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">OUI (Organizationally Unique Identifier)</label>
                                <input type="text" name="oui" class="form-control" placeholder="Enter OUI (8C9996)" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Model Image</label>
                                <input type="file" name="image" accept=".jpg,.png,image/jpeg,image/png" id="imageInput" required>
                                <br>
                                <img id="preview" src="#" alt="Image Preview" class="mt-2" style="display: none; max-width: 200px;">
                            </div>
                        </div>
                    </div>

                    <!-- CS Nodes Section -->
                    <div class="mb-4">
                        <h5 class="mb-3">Customer Service Nodes</h5>
                        <div id="cs-nodes-container">
                            <!-- <div class="row g-3 mb-3 cs-node">
                                <div class="col-md-12">
                                    <input type="text" name="cs_nodes[0][name]" class="form-control" placeholder="Node Name (Displayed on CS Dashboard)">
                                </div>
                                <div class="col-md-12">
                                    <input type="text" name="cs_nodes[0][path]" class="form-control" placeholder="Node Path (e.g., InternalGateway.DeviceInfo)">
                                </div>
                                <div class="col-md-12">
                                    <select name="cs_nodes[0][type]" class="form-control">
                                        <option value="">Select Node Type</option>
                                        <option value="xsd:string">xsd:string</option>
                                        <option value="xsd:bool">xsd:bool</option>
                                        <option value="readonly">No Type (Readonly)</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <select name="cs_nodes[0][category]" class="form-control">
                                        <option value="">Select Node Category</option>
                                        <option value="Wifi 2.4GHz">Wifi 2.4GHz</option>
                                        <option value="Wifi 5GHz">Wifi 5GHz</option>
                                        <option value="Wifi Band Steering">Wifi Band Steering</option>
                                        <option value="RF Nodes">RF Nodes</option>
                                        <option value="DHCP Nodes">DHCP Nodes</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div> -->
                        </div>
                        <button type="button" class="btn btn-outline-primary" id="add-cs-node">Add CS Node</button>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <button type="button" class="btn btn-outline-danger flex-fill" onclick="window.history.back()">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-outline-primary flex-fill">
                            <i class="bi bi-send me-2"></i>Save Model
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-body">
                @if($deviceModels->isEmpty())
                    <p class="text-center">There are no devices for the CS to control.</p>
                @else
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Model Name</th>
                                <th>OUI</th>
                                <th>Actions</th> <!-- Action Buttons Area -->
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deviceModels as $model)
                            <tr>
                                <td>{{ $model->model_name }}</td>
                                <td>{{ $model->oui }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('device-models.edit', $model->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> <!-- Edit Icon -->
                                        </a>

                                        <form action="{{ route('device-models.destroy', $model->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                <i class="bi bi-trash"></i> <!-- Delete Icon -->
                                            </button>
                                        </form>
                                    </div>
                                </td>                            
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.getElementById('imageInput').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview').src = e.target.result;
                document.getElementById('preview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('add-cs-node').addEventListener('click', function() {
        let index = document.querySelectorAll('#cs-nodes-container .cs-node').length;
        let newNode = document.createElement('div');
        newNode.classList.add('row', 'g-3', 'mb-3', 'cs-node');
        newNode.innerHTML = `
            <hr>
            <div class="col-md-12">
                <input type="text" name="cs_nodes[${index}][name]" class="form-control" placeholder="Node Name (Displayed on CS Dashboard)" required>
            </div>
            <div class="col-md-12">
                <input type="text" name="cs_nodes[${index}][path]" class="form-control" placeholder="Node Path (e.g., InternalGateway.DeviceInfo)" required>
            </div>
            <div class="col-md-12">
                <select name="cs_nodes[${index}][type]" class="form-control" required>
                    <option value="">Select Node Type</option>
                    <option value="xsd:string">xsd:string</option>
                    <option value="xsd:booleanbool">xsd:boolean</option>
                    <option value="readonly">No Type (Readonly)</option>
                </select>
            </div>
            <div class="col-md-12">
                <select name="cs_nodes[${index}][category]" class="form-control" required>
                    <option value="">Select Node Category</option>
                    <option value="Wifi 2.4GHz">Wifi 2.4GHz</option>
                    <option value="Wifi 5GHz">Wifi 5GHz</option>
                    <option value="Wifi Band Steering">Wifi Band Steering</option>
                    <option value="RF Nodes">RF Nodes</option>
                    <option value="DHCP Nodes">DHCP Nodes</option>
                </select>
            </div>
            <div class="col-md-12">
                <button type="button" class="btn btn-danger remove-cs-node">Remove</button>
            </div>
        `;
        document.getElementById('cs-nodes-container').appendChild(newNode);
    });

    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-cs-node')) {
            event.target.closest('.cs-node').remove();
        }
    });
</script>
@endsection
