@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Insert Standard Nodes</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('standard-nodes.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="device_model_id">Select Device Model:</label>
            <select name="device_model_id" id="device_model_id" class="form-control" required>
                <option value="">-- Choose Device Model --</option>
                @foreach($deviceModels as $model)
                    <option value="{{ $model->id }}">{{ $model->model_name }} ({{ $model->product_class }})</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mt-3">
            <label for="base_path">Base Path:</label>
            <input type="text" name="base_path" id="base_path" class="form-control" placeholder="e.g., InternetGatewayDevice.SystemConfig.WiFi.NeighborAP.{i}" required>
        </div>

        <div class="form-group mt-3">
            <label>Node Attributes:</label>
            <div id="attributes-list">
                <div class="input-group mb-2">
                    <input type="text" name="attributes[]" class="form-control" placeholder="e.g., BSSID" required>
                    <button type="button" class="btn btn-danger remove-attribute">×</button>
                </div>
            </div>
            <button type="button" class="btn btn-secondary mt-2" id="add-attribute">Add Attribute</button>
        </div>

        <button type="submit" class="btn btn-primary mt-4">Insert Nodes</button>
    </form>
</div>

<script>
    document.getElementById('add-attribute').addEventListener('click', () => {
        const list = document.getElementById('attributes-list');
        const div = document.createElement('div');
        div.classList.add('input-group', 'mb-2');
        div.innerHTML = `
            <input type="text" name="attributes[]" class="form-control" placeholder="e.g., SSID" required>
            <button type="button" class="btn btn-danger remove-attribute">×</button>
        `;
        list.appendChild(div);
    });

    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-attribute')) {
            e.target.parentElement.remove();
        }
    });
</script>
@endsection
