@extends('layouts.app')

@section('title', "AVXAV ACS | Devices - Model: {$model}")

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Devices - Model: {{ $model }}</div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <!-- Search Bar with Dropdown and Clear Button -->
                <div class="mb-3 d-flex gap-2">
                    <select id="searchType" class="form-select w-auto">
                        <option value="_deviceId._SerialNumber">Serial Number</option>
                        <option value="_deviceId._Manufacturer">Manufacturer</option>
                        <option value="_deviceId._OUI">OUI</option>
                        <option value="_deviceId._ProductClass">Product Class</option>
                    </select>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                    <button id="clearButton" class="btn btn-secondary">Clear</button>
                </div>

                <!-- Devices Table -->
                <div class="table-responsive">
                    <table id="devicesTable" class="table table-striped table-hover">
                        <thead class="table">
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
    const searchInput = document.getElementById("searchInput");
    const searchType = document.getElementById("searchType");
    const clearButton = document.getElementById("clearButton");
    const tableBody = document.getElementById("devicesTable").querySelector("tbody");
    const paginationContainer = document.getElementById("paginationContainer");

    if (data.devices.total <= data.devices.per_page) {
    paginationContainer.style.display = "none";
} else {
    paginationContainer.style.display = "block";
}

    /**
     * Updates the table with the provided rows.
     * @param {Array} rows - Array of rows to display in the table.
     */
    function updateTable(rows) {
        tableBody.innerHTML = ""; // Clear table body
        rows.forEach(row => tableBody.appendChild(row));
    }

    /**
     * Clears the search input, resets the table, and shows pagination.
     */
    function clearSearch() {
        searchInput.value = "";
        searchType.value = "_deviceId._SerialNumber"; // Reset to default search type
        fetchDevices(); // Reload the original dataset
        searchInput.focus(); // Focus on the search input
    }

    /**
     * Fetches filtered data from the server based on the search type and query.
     * @param {string} type - The selected search type.
     * @param {string} query - The search query.
     */
    async function fetchDevices(query = "", type = "_deviceId._SerialNumber") {
        try {
            const response = await fetch(`/devices/search/model?model={{ $model }}&type=${encodeURIComponent(type)}&query=${encodeURIComponent(query)}`);
            const data = await response.json();

            if (data.error) {
                alert(data.error);
                return;
            }

            if (data.devices && data.devices.data) {
                tableBody.innerHTML = ""; // Clear table
                data.devices.data.forEach(device => {
                    const row = tableBody.insertRow();
                    row.innerHTML = `
                        <td><a href="/device/info/${device._deviceId._SerialNumber}" class="btn btn-link text-decoration-none">${device._deviceId._SerialNumber || "N/A"}</a></td>
                        <td>${device._deviceId._Manufacturer || "N/A"}</td>
                        <td>${device._deviceId._OUI || "N/A"}</td>
                        <td>${device._deviceId._ProductClass || "N/A"}</td>
                        <td>${device.SoftwareVersion || "N/A"}</td>
                        <td>${device.UpTime || "N/A"}</td>
                        <td>${device._lastInform || "N/A"}</td>
                    `;
                });

                // Update pagination if needed
                paginationContainer.innerHTML = ""; // Clear existing pagination
                if (data.devices.links) {
                    data.devices.links.forEach(link => {
                        const paginationLink = document.createElement("a");
                        paginationLink.href = link.url || "#";
                        paginationLink.className = `btn btn-${link.active ? "primary" : "secondary"} mx-1`;
                        paginationLink.textContent = link.label;
                        paginationLink.addEventListener("click", function (e) {
                            e.preventDefault();
                            if (link.url) {
                                fetchDevices(searchInput.value, searchType.value);
                            }
                        });
                        paginationContainer.appendChild(paginationLink);
                    });
                }
            } else {
                console.error("Unexpected response structure:", data);
            }
        } catch (error) {
            console.error("Error fetching devices:", error);
        }
    }

    /**
     * Handles the search functionality based on the input value and selected type.
     */
    function handleSearch() {
        const query = searchInput.value.trim();
        const type = searchType.value;

        fetchDevices(query, type);
    }

    // Event listeners
    searchInput.addEventListener("keyup", handleSearch);
    clearButton.addEventListener("click", clearSearch);
    searchType.addEventListener("change", handleSearch);

    // Initial load
    fetchDevices();
});
</script>
@endsection
