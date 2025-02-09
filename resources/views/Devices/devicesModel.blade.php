@extends('layouts.app')
@section('title', 'Device Per Model')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <h1>Device Per Model</h1>
        </div>
        <div class="col-md-6 text-end">
            <div class="btn-group">
                <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    Actions
                </button>
                <ul class="dropdown-menu">
                    <li><a class="reboot-device dropdown-item" href="#" data-serial-number="{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? 'Unknown' }}">Reboot</a></li>
                    <li><a class="reset-device dropdown-item" href="#" data-serial-number="{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? 'Unknown' }}">Factory Reset</a></li>
                    <li><a class="dropdown-item" href="#">Push Upgrade</a></li>
                </ul>
            </div>
        </div>
        <hr>
    </div>

    <div class="row">
        <div class="col-md-6">
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <th>Serial Number:</th>
                        <td>{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? 'Unknown Serial Number' }}</td>
                    </tr>
                    <tr>
                        <th>Device ID:</th>
                        <td>{{ $deviceData['_id'] ?? 'Unknown' }}</td>
                    </tr>
                    <tr>
                        <th>OUI:</th>
                        <td>{{ $deviceData['_deviceId']['children']['_OUI']['value'] ?? 'Unknown' }}</td>
                    </tr>
                    <tr>
                        <th>Manufacturer:</th>
                        <td>{{ $deviceData['_deviceId']['children']['_Manufacturer']['value'] ?? 'Unknown' }}</td>
                    </tr>
                    <tr>
                        <th>Product Class:</th>
                        <td>{{ $deviceData['_deviceId']['children']['_ProductClass']['value'] ?? 'Unknown' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
 
    </div>

    <div class="row">
        <div class="col-md-8">
            <h2>Heatmap</h2>
            @include('partials.heatmap')
        </div>
        <div class="col-md-4">
            <h2>Connected Devices</h2>
            <div class="d-flex justify-content-center">
            <table class="table table-striped">
        <thead>
            <tr>
                <th>Device Name</th>
                <th>RSSI</th>
            </tr>
        </thead>
        <tbody id="deviceTableBody">
            <!-- Table rows will be populated dynamically -->
        </tbody>
    </table>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <h2>Device Data Tree</h2>
            @if ($deviceData)
                @foreach ($deviceData as $key => $value)
                    @include('partials.tree-item', ['key' => $key, 'value' => $value])
                @endforeach
            @else
                <p>No device information found.</p>
            @endif
        </div>
    </div>


<!-- Modal for Set Value -->
<div class="modal fade" id="setValueModal" tabindex="-1" role="dialog" aria-labelledby="setValueModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="setValueModalLabel">Set New Value</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="currentValue">Current Value:</label>
                        <input type="text" class="form-control" id="currentValue" readonly>
                    </div>
                    <div class="form-group">
                        <label for="newValue">New Value:</label>
                        <input type="text" class="form-control" id="newValue" placeholder="Enter new value">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveValueButton">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 9998;">
    <div id="loadingSpinner" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>

<!-- Simple Popup -->
<div id="simplePopup" style="display: none; position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 300px; background-color: #fff; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); border-radius: 8px; padding: 20px; font-family: Arial, sans-serif;">
    <div id="popupMessage" style="font-size: 16px; color: #333;"></div>
</div>
@endsection
