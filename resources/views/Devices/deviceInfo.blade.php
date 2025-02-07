@extends('layouts.app')
@section('title', 'AVXAV ACS | Device Info')
@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
  <div class="breadcrumb-title pe-3">Device Info</div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4>Device Information</h4>
                @if ($deviceData)
                    <ul id="device-tree" class="tree">
                        @foreach ($deviceData as $key => $value)
                            @include('partials.tree-item', ['key' => $key, 'value' => $value])
                        @endforeach
                    </ul>
                @else
                    <p>No device information found.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Add click event to toggle child nodes
    const toggles = document.querySelectorAll('.tree-toggle');
    toggles.forEach(toggle => {
        toggle.addEventListener('click', function () {
            const content = this.nextElementSibling;
            content.style.display = content.style.display === 'none' ? 'block' : 'none';
        });
    });
});
</script>
@endsection