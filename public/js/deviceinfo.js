    document.addEventListener("DOMContentLoaded", function () {
        // Function to show the loading overlay
        function showLoadingOverlay() {
            document.getElementById('loadingOverlay').style.display = 'block';
        }

        // Function to hide the loading overlay
        function hideLoadingOverlay() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }

        // Function to show the popup with a message
        function showSimplePopup(message) {
            const popup = document.getElementById('simplePopup');
            const popupMessage = document.getElementById('popupMessage');

            popupMessage.textContent = message;
            popup.style.display = 'block';
            popup.style.opacity = '1';
            popup.style.transform = 'translateY(0)';

            // Hide the popup after 3 seconds
            setTimeout(hideSimplePopup, 3000);
        }

        // Function to hide the popup
        function hideSimplePopup() {
            const popup = document.getElementById('simplePopup');
            popup.style.opacity = '0';
            popup.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                popup.style.display = 'none';
            }, 300); // Matches CSS transition duration
        }

        // Expand/Collapse functionality for tree items
        const toggles = document.querySelectorAll(".expand-icon");
        toggles.forEach((toggle) => {
            toggle.addEventListener("click", function () {
                const parentLi = this.closest("li");
                const childUl = parentLi.querySelector("ul");
                if (childUl) {
                    childUl.classList.toggle("collapsed");
                    childUl.classList.toggle("expanded");
                    this.textContent = childUl.classList.contains("expanded") ? "▼" : "▶";
                }
            });
        });

        // Function to handle fetching node data (GET action)
        function handleGetButton(button) {
            const path = button.dataset.path;
            const type = button.dataset.type;
            const serialNumber = "{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? 'Unknown' }}";

            showLoadingOverlay();

            fetch('/device-action/get-Node', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ serialNumber, path, type })
            })
            .then(response => {
                hideLoadingOverlay();
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.status_code === 200) {
                    showSimplePopup('Value fetched successfully.');
                } else if (data.status_code === 202) {
                    showSimplePopup('Fetch value saved as a task.');
                }
            })
            .catch(error => {
                hideLoadingOverlay();
                console.error('Error fetching value:', error);
                showSimplePopup('An error occurred while fetching the value.');
            });
        }

        // Function to handle setting a new value
        function handleSetValue(button) {
            const path = button.dataset.path;
            const type = button.dataset.type;
            const currentValue = button.dataset.value;
            const serialNumber = "{{ $deviceData['_deviceId']['children']['_SerialNumber']['value'] ?? 'Unknown' }}";

            // Show modal with current and new value inputs
            $('#setValueModal').modal('show');
            document.getElementById('setValueModalLabel').textContent = 'Set New Value';
            document.getElementById('currentValue').value = currentValue;
            document.getElementById('newValue').value = ''; // Clear new value input
            document.getElementById('saveValueButton').setAttribute('data-path', path);
            document.getElementById('saveValueButton').setAttribute('data-type', type);
            document.getElementById('saveValueButton').setAttribute('data-serial-number', serialNumber);
        }

        // Save the new value from the modal
        document.getElementById('saveValueButton').addEventListener('click', function () {
            const newValue = document.getElementById('newValue').value;
            const path = this.getAttribute('data-path');
            const type = this.getAttribute('data-type');
            const serialNumber = this.getAttribute('data-serial-number');

            if (!newValue) {
                alert('Please enter a new value.');
                return;
            }

            showLoadingOverlay();

            fetch('/device-action/set-Node', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ serialNumber, path, type, value: newValue })
            })
            .then(response => {
                hideLoadingOverlay();
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.status_code === 200) {
                    showSimplePopup(`Value set successfully for ${path}: ${newValue}`);
                } else if (data.status_code === 202) {
                    showSimplePopup(`Set value saved as a task: ${data.message}`);
                }
            })
            .catch(error => {
                hideLoadingOverlay();
                console.error('Error setting value:', error);
                showSimplePopup('An error occurred while setting the value.');
            })
            .finally(() => {
                $('#setValueModal').modal('hide');
            });
        });

        // Handle reboot action
        document.querySelectorAll(".reboot-device").forEach((button) => {
            button.addEventListener("click", function () {
                const serialNumber = this.getAttribute('data-serial-number');

                showLoadingOverlay();

                fetch('/device-action/reboot', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ serialNumber })
                })
                .then(response => {
                    hideLoadingOverlay();
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showSimplePopup('Device reboot request accepted.');
                    } else {
                        showSimplePopup(`Failed to reboot device: ${data.message}`);
                    }
                })
                .catch(error => {
                    hideLoadingOverlay();
                    console.error('Error rebooting device:', error);
                    showSimplePopup('An error occurred while rebooting the device.');
                });
            });
        });

        // Handle reset action
        document.querySelectorAll(".reset-device").forEach((button) => {
            button.addEventListener("click", function () {
                const serialNumber = this.getAttribute('data-serial-number');

                showLoadingOverlay();

                fetch('/device-action/reset', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ serialNumber })
                })
                .then(response => {
                    hideLoadingOverlay();
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showSimplePopup('Device reset request accepted.');
                    } else {
                        showSimplePopup(`Failed to reset device: ${data.message}`);
                    }
                })
                .catch(error => {
                    hideLoadingOverlay();
                    console.error('Error resetting device:', error);
                    showSimplePopup('An error occurred while resetting the device.');
                });
            });
        });

        // Attach event listeners for "Get" and "Set" buttons
        document.querySelectorAll(".get-button").forEach(button => button.addEventListener('click', () => handleGetButton(button)));
        document.querySelectorAll(".set-button").forEach(button => button.addEventListener('click', () => handleSetValue(button)));
        
        const devices = [
            { name: 'Device 1', rssi: -40 },
            { name: 'Device 2', rssi: -50 },
            { name: 'Device 3', rssi: -65 },
            { name: 'Device 4', rssi: -80 },
            { name: 'Device 5', rssi: -30 },
        ];

        const container = document.getElementById('heatmap');
        const tooltip = document.getElementById('tooltip');

        const containerWidth = container.offsetWidth;
        const containerHeight = container.offsetHeight;

        // Create Circular Range Indicators
        const radarRanges = [50, 100, 150, 200, 250, 300, 350, 400]; // Distances for circles
        radarRanges.forEach((radius) => {
            const circle = document.createElement('div');
            circle.className = 'radar-circle';
            circle.style.width = `${radius * 2}px`;
            circle.style.height = `${radius * 2}px`;
            circle.style.left = `${containerWidth / 2 - radius}px`;
            circle.style.top = `${containerHeight / 2 - radius}px`;
            container.appendChild(circle);
        });

        // Map RSSI to Distance
        function mapRssiToDistance(rssi) {
            const minRssi = -90; // Weakest
            const maxRssi = -30; // Strongest
            const minDistance = 400; // Furthest
            const maxDistance = 100; // Closest

            return (
                maxDistance +
                ((rssi - maxRssi) / (minRssi - maxRssi)) * (minDistance - maxDistance)
            );
        }

        // Map RSSI to Color (Red to Blue)
        function mapRssiToColor(rssi) {
            const minRssi = -90;
            const maxRssi = -30;

            const ratio = (rssi - minRssi) / (maxRssi - minRssi);
            const red = Math.round(255 * (1 - ratio));
            const blue = Math.round(255 * ratio);

            return `rgb(${red}, 0, ${blue})`;
        }

        // Place Devices in Circular Layout
        devices.forEach((device, index) => {
            const angle = (index / devices.length) * 2 * Math.PI; // Distribute evenly
            const distance = mapRssiToDistance(device.rssi); // Map RSSI to distance
            const x = containerWidth / 2 + Math.cos(angle) * distance;
            const y = containerHeight / 2 + Math.sin(angle) * distance;

            // Create Device Node
            const deviceNode = document.createElement('div');
            deviceNode.className = 'device-node';
            deviceNode.style.backgroundColor = mapRssiToColor(device.rssi);
            deviceNode.style.left = `${x - 15}px`; // Center the node
            deviceNode.style.top = `${y - 15}px`; // Center the node

            // Tooltip on Hover
            deviceNode.addEventListener('mouseenter', () => {
                tooltip.style.opacity = 1;
                tooltip.style.left = `${x + 20}px`;
                tooltip.style.top = `${y}px`;
                tooltip.textContent = `${device.name}\nRSSI: ${device.rssi} dBm`;
            });

            deviceNode.addEventListener('mouseleave', () => {
                tooltip.style.opacity = 0;
            });

            container.appendChild(deviceNode);
        });
    
    });