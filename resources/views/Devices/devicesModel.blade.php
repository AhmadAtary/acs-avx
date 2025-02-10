@extends('layouts.app')
@section('title', 'Device Per Model')

@section('content')
<style>
    .card-img-top {
        width: 100%; /* Ensures the image fits the card width */
        height: 200px; /* Set a fixed height */
        object-fit: cover; /* Ensures the image is cropped properly */
    }
    .card {
        cursor: pointer; /* Makes the card show a pointer cursor to indicate it's clickable */
    }
</style>

<div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
@foreach($devices_Models as $device)
    <div class="col">
        <!-- Wrap the entire card with an anchor tag -->
        <a href="{{ route('device.modelShow', ['model' => $device['Product_Class']]) }}" class="text-decoration-none">
            <div class="card">
                <!-- Dynamically set the image source based on Product_Class -->
                <img src="{{ asset('assets/Devices/' . $device['Product_Class'] . '.png') }}" class="card-img-top" alt="{{ $device['Model'] }}">

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
