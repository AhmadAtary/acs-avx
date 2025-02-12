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
                <!-- Search Bar with Dropdown and Clear Button -->
                <div class="mb-3 d-flex gap-2">
                    <input type="hidden" id="searchType" value="_deviceId._SerialNumber">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search By serial Number">
                    <button id="clearButton" class="btn btn-secondary">Clear</button>
                </div>
                <!-- Devices Table -->
                <div class="table-responsive">
                  <table id="devicesTable" class="table table-striped table-hover">
                    <thead class="table">
                      <tr>
                        <th>
                          <input type="text" class="form-control form-control-sm column-search" data-column="0" placeholder="Serial Number">
                        </th>
                        <th>
                          <input type="text" class="form-control form-control-sm column-search" data-column="1" placeholder="Manufacturer">
                        </th>
                        <th>
                          <input type="text" class="form-control form-control-sm column-search" data-column="2" placeholder="OUI">
                        </th>
                        <th>
                          <input type="text" class="form-control form-control-sm column-search" data-column="3" placeholder="Product Class">
                        </th>
                        <th>
                          <input type="text" class="form-control form-control-sm column-search" data-column="4" placeholder="Software Version">
                        </th>
                        <th>
                          <input type="text" class="form-control form-control-sm column-search" data-column="5" placeholder="Up Time">
                        </th>
                        <th>
                          <input type="text" class="form-control form-control-sm column-search" data-column="6" placeholder="Last Inform">
                        </th>
                      </tr>
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
                            <a href="{{ route('device.info', ['serialNumber' => $device->_deviceId['_SerialNumber']]) }}" class="btn btn-link text-decoration-none">
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
                <!-- Pagination -->
                <div id="paginationContainer" class="d-flex mt-3 justify-content-end">
                    {{ $devices->links('vendor.pagination.bootstrap-5') }}
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
    let clearButton = document.getElementById("clearButton");
    let tableBody = document.getElementById("devicesTable").getElementsByTagName("tbody")[0];
    let originalRows = Array.from(tableBody.rows); // Store original rows for clearing
    let paginationContainer = document.getElementById("paginationContainer");

    // Function to update table rows
    function updateTable(rows) {
        tableBody.innerHTML = '';
        rows.forEach(row => tableBody.appendChild(row));
    }

    // Function to perform frontend search
    function frontendSearch() {
        let searchTerms = Array.from(document.querySelectorAll('.column-search')).map(input => input.value.toLowerCase());
        let filteredRows = originalRows.filter(row => {
            return searchTerms.every((term, index) => {
                let cellText = row.cells[index] ? row.cells[index].textContent.toLowerCase() : '';
                return term === '' || cellText.includes(term);
            });
        });
        updateTable(filteredRows);
    }

    // Event listeners for column search inputs
    document.querySelectorAll('.column-search').forEach(input => {
        input.addEventListener("keyup", frontendSearch);
    });

    searchInput.addEventListener("keyup", function () {
        let searchTerm = searchInput.value.trim();
        if (searchTerm.length >= 4) {
            fetch(`/devices/search?type=${encodeURIComponent(searchType.value)}&query=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    // Ensure the response contains the expected structure
                    if (data.devices && data.devices.data) {
                        tableBody.innerHTML = '';
                        data.devices.data.forEach(device => {
                            let row = tableBody.insertRow();
                            row.insertCell(0).innerHTML = `<a href="/device/info/${device._deviceId._SerialNumber}" class="btn btn-link text-decoration-none">${device._deviceId._SerialNumber || 'N/A'}</a>`;
                            row.insertCell(1).textContent = device._deviceId._Manufacturer || 'N/A';
                            row.insertCell(2).textContent = device._deviceId._OUI || 'N/A';
                            row.insertCell(3).textContent = device._deviceId._ProductClass || 'N/A';
                            row.insertCell(4).textContent = device.InternetGatewayDevice.DeviceInfo.SoftwareVersion._value || 'N/A';
                            row.insertCell(5).textContent = device.InternetGatewayDevice.DeviceInfo.UpTime._value || 'N/A';
                            row.insertCell(6).textContent = device._lastInform || 'N/A';
                        });
                        // Hide pagination if less than 200 devices
                        if (data.devices.data.length < 200) {
                            paginationContainer.style.display = 'none';
                        } else {
                            paginationContainer.style.display = 'block';
                        }
                    } else {
                        console.error('Unexpected response structure:', data);
                    }
                })
                .catch(error => console.error('Error:', error));
        } else {
            // If search term is less than 4 characters, show original rows and pagination
            updateTable(originalRows);
            paginationContainer.style.display = 'block';
        }
    });

    clearButton.addEventListener("click", function () {
        searchInput.value = '';
        searchType.value = '_deviceId._SerialNumber'; // Reset to default search type
        document.querySelectorAll('.column-search').forEach(input => input.value = '');
        updateTable(originalRows);
        paginationContainer.style.display = 'block';
        searchInput.focus(); // Focus back to the search input
    });

    searchType.addEventListener("change", function () {
        searchInput.value = ''; // Clear the search input when changing search type
        document.querySelectorAll('.column-search').forEach(input => input.value = '');
        updateTable(originalRows);
        paginationContainer.style.display = 'block';
        searchInput.focus(); // Focus back to the search input
    });
});
</script>
@endsection