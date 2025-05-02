@extends('layouts.app')
@section('title', 'AVXAV ACS | Add Network Node')

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
  <div class="breadcrumb-title pe-3">Add Network Node</div>
</div>

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
  <div class="col-12">
    <div class="card">
      <div class="card-body">

        <form method="POST" action="{{ route('signal-nodes.storeMultiple') }}">
          @csrf

          <div class="mb-3">
            <label for="model_id" class="form-label">Device Model</label>
            <select name="model_id" id="model_id" class="form-select" required>
              <option value="" disabled selected>Select Model</option>
              @foreach($models as $model)
                <option value="{{ $model->id }}">{{ $model->model_name }}</option>
              @endforeach
            </select>
          </div>

          <div id="nodeFieldsWrapper">
            <div class="node-group border rounded p-3 mb-3">
              <div class="mb-3">
                <label class="form-label">Parameter Name</label>
                <input type="text" name="nodes[0][param_name]" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">JSON Path</label>
                <input type="text" name="nodes[0][json_path]" class="form-control" required>
              </div>

              <button type="button" class="btn btn-danger btn-sm removeNodeBtn d-none">Remove</button>
            </div>
          </div>

          <div class="mb-3">
            <button type="button" class="btn btn-secondary" id="addNodeBtn">Add Another Node</button>
          </div>

          <div class="mb-3 text-end">
            <button type="submit" class="btn btn-primary">Save Nodes</button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

<script>
  let nodeIndex = 1;

  document.getElementById('addNodeBtn').addEventListener('click', () => {
    const wrapper = document.getElementById('nodeFieldsWrapper');
    const newNode = document.createElement('div');
    newNode.classList.add('node-group', 'border', 'rounded', 'p-3', 'mb-3');

    newNode.innerHTML = `
      <div class="mb-3">
        <label class="form-label">Parameter Name</label>
        <input type="text" name="nodes[${nodeIndex}][param_name]" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">JSON Path</label>
        <input type="text" name="nodes[${nodeIndex}][json_path]" class="form-control" required>
      </div>
      <button type="button" class="btn btn-danger btn-sm removeNodeBtn">Remove</button>
    `;

    wrapper.appendChild(newNode);
    nodeIndex++;
  });

  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('removeNodeBtn')) {
      e.target.closest('.node-group').remove();
    }
  });
</script>
@endsection
