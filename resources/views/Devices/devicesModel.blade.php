@extends('layouts.app')
@section('title', 'Device Per Model')

@section('content')
<style>
    .card-img-top {
        width: 100%; /* Ensures the image fits the card width */
        height: 300px; /* Set a fixed height for consistency across cards */
        object-fit: contain; /* Maintains the aspect ratio and prevents distortion */
        /* background-color: #f8f9fa; Optional: Adds a light background to make smaller images look centered */
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
            <a href="{{ route('device.modelShow', ['model' => $device['Product_Class']]) }}" class="text-decoration-none">
                <div class="card shadow-sm">
                    <!-- Dynamically set the image source based on Product_Class -->
                    <img src="{{ asset('assets/Devices/' . $device['Product_Class'] . '.png') }}" 
                         class="card-img-top" 
                         alt="{{ $device['Model'] }}">

                    <div class="card-body">
                        <!-- Display the model as the card title -->
                        <h5 class="card-title">{{ $device['Model'] }}</h5>
                        <!-- Display additional details if needed -->
                        <p class="card-text">
                            This is the device {{ $device['Model'] }}. It belongs to the product class {{ $device['Product_Class'] }}.
                        </p>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>
@endsection
