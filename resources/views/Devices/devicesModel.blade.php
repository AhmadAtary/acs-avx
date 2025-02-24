@extends('layouts.app')
@section('title', 'Device Per Model')

@section('content')
<style>
    .card-img-top {
        width: 100%; /* Ensures the image fits the card width */
        height: 300px; /* Set a fixed height for consistency across cards */
        object-fit: contain; /* Maintains the aspect ratio and prevents distortion */
    }
    .card {
        cursor: pointer; /* Makes the card show a pointer cursor to indicate it's clickable */
    }
    .card-body {
        text-align: center; /* Centers the content inside the card */
    }
</style>


<div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
    @if($devices_Models->isNotEmpty())
        @foreach($devices_Models as $device)
            <div class="col mb-4">
                <!-- Wrap the entire card with an anchor tag -->
                <a href="{{ route('device.modelShow', ['model' => $device->product_class]) }}" class="text-decoration-none">
                    <div class="card shadow-sm">
                        <!-- Dynamically set the image source based on stored path -->
                        <img src="{{ $device->image ? asset('storage/' . $device->image) : asset('assets/default-device.png') }}" 
                             class="card-img-top" 
                             alt="{{ $device->model_name }}">

                        <div class="card-body">
                            <!-- Display the model name as the card title -->
                            <h5 class="card-title">{{ $device->model_name }}</h5>
                            <!-- Display additional details if needed -->
                            <p class="card-text">
                                This is the device {{ $device->model_name }}. It belongs to the product class {{ $device->product_class }}.
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    @else
    <div class="col-12 d-flex justify-content-center align-items-center mt-5">
        <div class="card shadow-lg border-0" style="max-width: 600px; width: 100%;">
            <div class="card-body text-center p-4">
                @if(auth()->user()->access->permissions['models_management']['create'] ?? false)
                    <!-- User has permission to add a model -->
                    <div class="alert alert-info d-flex align-items-center justify-content-center" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <span>You need to add a model before you can start using this page.</span>
                    </div>
                    <a href="{{ route('device-models.index') }}" class="btn btn-primary btn-lg mt-2">
                        <i class="fas fa-plus"></i> Add a New Model
                    </a>
                @else
                    <!-- User does not have permission to add a model -->
                    <div class="alert alert-warning d-flex align-items-center justify-content-center" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span>No device models found. You need to add a model before using this page.</span>
                    </div>
                    <p class="text-muted mt-2">
                        Please contact the system administrator for assistance.
                    </p>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

@endsection
