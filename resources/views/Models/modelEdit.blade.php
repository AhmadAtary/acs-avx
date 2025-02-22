@extends('layouts.app')

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Edit Device Model</div>
</div>

<form method="POST" action="{{ route('device-models.update', $deviceModel->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">Model Information</h5>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Model Name</label>
                            <input type="text" name="model_name" class="form-control" value="{{ $deviceModel->model_name }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Product Class</label>
                            <input type="text" name="product_class" class="form-control" value="{{ $deviceModel->product_class }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">OUI</label>
                            <input type="text" name="oui" class="form-control" value="{{ $deviceModel->oui }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Model Image</label>
                            <input type="file" name="image" accept=".jpg,.png,image/jpeg,image/png" id="imageInput">
                            <br>
                            <img id="preview" src="{{ asset('storage/' . $deviceModel->image) }}" alt="Image Preview" class="mt-2" style="max-width: 200px;">
                        </div>
                    </div>
                    <h5 class="mt-4">Customer Service Nodes</h5>
                    <div id="cs-nodes-container">
                        @foreach ($deviceModel->nodes as $index => $node)
                            <div class="row g-3 mb-3 cs-node">
                                <div class="col-md-12">
                                    <input type="text" name="cs_nodes[{{ $index }}][name]" class="form-control" value="{{ $node->name }}" required>
                                </div>
                                <div class="col-md-12">
                                    <input type="text" name="cs_nodes[{{ $index }}][path]" class="form-control" value="{{ $node->path }}" required>
                                </div>
                                <div class="col-md-12">
                                    <select name="cs_nodes[{{ $index }}][type]" class="form-control" required>
                                        <option value="xsd:string" {{ $node->type == 'xsd:string' ? 'selected' : '' }}>xsd:string</option>
                                        <option value="xsd:boolean" {{ $node->type == 'xsd:boolean' ? 'selected' : '' }}>xsd:boolean</option>
                                        <option value="readonly" {{ $node->type == 'readonly' ? 'selected' : '' }}>No Type (Readonly)</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <select name="cs_nodes[{{ $index }}][category]" class="form-control" required>
                                        <option value="Wifi 2.4GHz" {{ $node->category == 'Wifi 2.4GHz' ? 'selected' : '' }}>Wifi 2.4GHz</option>
                                        <option value="Wifi 5GHz" {{ $node->category == 'Wifi 5GHz' ? 'selected' : '' }}>Wifi 5GHz</option>
                                        <option value="Wifi Band Steering" {{ $node->category == 'Wifi Band Steering' ? 'selected' : '' }}>Wifi Band Steering</option>
                                        <option value="RF Nodes" {{ $node->category == 'RF Nodes' ? 'selected' : '' }}>RF Nodes</option>
                                        <option value="DHCP Nodes" {{ $node->category == 'DHCP Nodes' ? 'selected' : '' }}>DHCP Nodes</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-danger remove-cs-node">Remove</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-outline-primary mt-3" id="add-cs-node">Add CS Node</button>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-12 text-center">
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-save me-2"></i>Save Changes
            </button>
        </div>
    </div>
</form>
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
                    <option value="xsd:boolean">xsd:boolean</option>
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
