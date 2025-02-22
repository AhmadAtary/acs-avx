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
</div>
@endsection
