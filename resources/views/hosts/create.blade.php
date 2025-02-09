@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Add New Host</h2>
    <form action="{{ route('hosts.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="Model" class="form-label">Model</label>
            <input type="text" class="form-control" id="Model" name="Model" required>
        </div>
        <div class="mb-3">
            <label for="Product_Class" class="form-label">Product Class</label>
            <input type="text" class="form-control" id="Product_Class" name="Product_Class" required>
        </div>
        <div class="mb-3">
            <label for="HostName" class="form-label">Host Name</label>
            <input type="text" class="form-control" id="HostName" name="HostName">
        </div>
        <div class="mb-3">
            <label for="IPAddress" class="form-label">IP Address</label>
            <input type="text" class="form-control" id="IPAddress" name="IPAddress">
        </div>
        <div class="mb-3">
            <label for="MACAddress" class="form-label">MAC Address</label>
            <input type="text" class="form-control" id="MACAddress" name="MACAddress">
        </div>
        <div class="mb-3">
            <label for="RSSI" class="form-label">RSSI</label>
            <input type="number" class="form-control" id="RSSI" name="RSSI">
        </div>
        <div class="mb-3">
            <label for="hostPath" class="form-label">Host Path</label>
            <input type="text" class="form-control" id="hostPath" name="hostPath">
        </div>
        <button type="submit" class="btn btn-primary">Add Host</button>
    </form>
</div>
@endsection
