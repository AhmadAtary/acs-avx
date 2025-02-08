@extends('layouts.app')

@section('title', 'AVXAV ACS | All Devices')

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
  <div class="breadcrumb-title pe-3">All Devices</div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <!-- Search Bar with Dropdown -->
                <div class="mb-3 d-flex gap-2">
                    <select id="searchType" class="form-select w-auto">
                        <option value="_deviceId._SerialNumber">Serial Number</option>
                        <option value="_deviceId._Manufacturer">Manufacturer</option>
                        <option value="_deviceId._OUI">OUI</option>
                        <option value="_deviceId._ProductClass">Product Class</option>
                    </select>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                </div>

                <!-- Devices Table -->
                <div class="table-responsive">
                  <table id="devicesTable" class="table table-bordered">
                    <thead class="table-dark">
                      <tr>
                        <th>Serial Number</th>
                        <th>Manufacturer</th>
                        <th>OUI</th>
                        <th>Product Class</th>
                        <th>Software Version</th>
                        <th>Up Time</th>
                        <th>Last Inform</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($devices as $device)
                        <tr>
                          <td>
                            <a href="{{ route('device.info', ['serialNumber' => $device->_deviceId['_SerialNumber']]) }}" class="btn btn-link">
                              {{ $device->_deviceId['_SerialNumber'] ?? 'N/A' }}
                            </a>
                          </td>
                          <td>{{ $device->_deviceId['_Manufacturer'] ?? 'N/A' }}</td>
                          <td>{{ $device->_deviceId['_OUI'] ?? 'N/A' }}</td>
                          <td>{{ $device->_deviceId['_ProductClass'] ?? 'N/A' }}</td>
                          <td>{{ $device->InternetGatewayDevice['DeviceInfo']['SoftwareVersion']['_value'] ?? 'N/A' }}</td>
                          <td>{{ $device->InternetGatewayDevice['DeviceInfo']['UpTime']['_value'] ?? 'N/A' }}</td>
                          <td>{{ $device->_lastInform ?? 'N/A' }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    let searchInput = document.getElementById("searchInput");
    let searchType = document.getElementById("searchType");
    let table = document.getElementById("devicesTable");

    searchInput.addEventListener("keyup", function () {
        let filter = searchInput.value.toLowerCase();
        let filterType = searchType.value;
        let rows = table.getElementsByTagName("tr");

        for (let i = 1; i < rows.length; i++) { // Start from 1 to skip the header
            let cellValue = rows[i].getElementsByTagName("td")[searchType.selectedIndex]?.innerText.toLowerCase();
            rows[i].style.display = cellValue.includes(filter) ? "" : "none";
        }
    });
});
</script>
@endsection
