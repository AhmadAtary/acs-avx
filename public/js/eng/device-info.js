const DeviceInfoModule = (function () {
    let config = {};

    // Utility Functions
    const utils = {
        loadingCount: 0,
        loadingTimeout: null,
        MIN_LOADING_TIME: 300,
        DEBOUNCE_TIME: 100,

        showLoading: () => {
            if (utils.loadingCount === 0) {
                utils.loadingTimeout = setTimeout(() => {
                    const overlay = document.getElementById('loadingOverlay');
                    if (overlay) overlay.style.display = 'block';
                }, utils.DEBOUNCE_TIME);
            }
            utils.loadingCount++;
        },

        hideLoading: () => {
            utils.loadingCount = Math.max(0, utils.loadingCount - 1);
            if (utils.loadingCount === 0) {
                clearTimeout(utils.loadingTimeout);
                setTimeout(() => {
                    const overlay = document.getElementById('loadingOverlay');
                    if (overlay) overlay.style.display = 'none';
                }, utils.MIN_LOADING_TIME);
            }
        },

        showPopup: (message) => {
            const popup = document.getElementById('simplePopup');
            const popupMessage = document.getElementById('popupMessage');
            if (popup && popupMessage) {
                popupMessage.textContent = message;
                popup.style.display = 'block';
                popup.classList.add('show');
                setTimeout(() => {
                    popup.classList.remove('show');
                    setTimeout(() => popup.style.display = 'none', 300);
                }, 3000);
            }
        },

        fetchData: async (url, options = {}) => {
            utils.showLoading();
            const startTime = Date.now();
            try {
                const response = await fetch(url, {
                    headers: { 'X-CSRF-TOKEN': config.csrfToken, 'Content-Type': 'application/json', ...options.headers },
                    ...options
                });
                if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
                return await response.json();
            } catch (error) {
                console.error(`Fetch error: ${error}`);
                utils.showPopup(`Error: ${error.message}`);
                throw error;
            } finally {
                const elapsed = Date.now() - startTime;
                if (elapsed < utils.MIN_LOADING_TIME) {
                    await new Promise(resolve => setTimeout(resolve, utils.MIN_LOADING_TIME - elapsed));
                }
                utils.hideLoading();
            }
        },

        updateFieldValue: (path, value) => {
            const element = document.getElementById(path);
            if (element) {
                element.textContent = value;
                return;
            }
            const observer = new MutationObserver((_, obs) => {
                const el = document.getElementById(path);
                if (el) {
                    el.textContent = value;
                    obs.disconnect();
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }
    };

    // Tree View and Search
    const treeView = {
        init: () => {
            document.querySelectorAll(".expand-icon").forEach(toggle => {
                if (!toggle.dataset.listener) {
                    toggle.dataset.listener = 'true';
                    toggle.addEventListener("click", function () {
                        const parentLi = this.closest("li");
                        const childUl = parentLi.querySelector("ul");
                        if (childUl) {
                            childUl.classList.toggle("collapsed");
                            childUl.classList.toggle("expanded");
                            this.textContent = childUl.classList.contains("expanded") ? "▼" : "▶";
                        }
                    });
                }
            });
        },

        search: () => {
            const searchBar = document.getElementById("search-bar");
            const clearButton = document.getElementById("clear-search");
            const treeItems = document.querySelectorAll(".node-content");

            if (!searchBar || !clearButton) {
                console.warn("Search bar or clear button not found.");
                return;
            }

            searchBar.removeEventListener('input', searchBar.inputHandler);
            searchBar.inputHandler = function () {
                const query = searchBar.value.trim().toLowerCase();
                treeView.resetTree();
                if (!query) return;

                let found = false;
                treeItems.forEach(item => {
                    const valueElement = item.querySelector(".node-value");
                    const nameElement = item.querySelector(".node-name");
                    const nodePath = valueElement?.id?.toLowerCase() || "";
                    const nodeName = nameElement?.textContent?.toLowerCase() || "";
                    const nodeValue = valueElement?.textContent?.toLowerCase() || "";

                    if (nodePath.includes(query) || nodeName.includes(query) || nodeValue.includes(query)) {
                        found = true;
                        treeView.highlightMatches(item, query, nodePath, nodeName, nodeValue);
                        treeView.expandParentNodes(item);
                    }
                });

                if (!found) console.log("No matching nodes found.");
            };
            searchBar.addEventListener("input", searchBar.inputHandler);

            clearButton.removeEventListener('click', clearButton.clickHandler);
            clearButton.clickHandler = function () {
                searchBar.value = "";
                treeView.resetTree();
            };
            clearButton.addEventListener("click", clearButton.clickHandler);
        },

        highlightMatches: (item, query, path, name, value) => {
            const valueElement = item.querySelector(".node-value");
            const nameElement = item.querySelector(".node-name");
            if (path.includes(query)) valueElement?.classList.add("highlight");
            else if (name.includes(query)) nameElement?.classList.add("highlight");
            else if (value.includes(query)) valueElement?.classList.add("highlight");
        },

        expandParentNodes: (item) => {
            let parent = item.closest("ul");
            while (parent) {
                parent.classList.remove("collapsed");
                parent = parent.parentElement.closest("ul");
            }
        },

        resetTree: () => {
            document.querySelectorAll(".highlight").forEach(el => el.classList.remove("highlight"));
            document.querySelectorAll("ul").forEach(ul => ul.classList.add("collapsed"));
        }
    };

    // Device Actions
    const deviceActions = {
        getNode: async (button) => {
            const { path, type } = button.dataset;
            try {
                const data = await utils.fetchData('/device-action/get-Node', {
                    method: 'POST',
                    body: JSON.stringify({ serialNumber: config.serialNumber, path, type })
                });
                if (data.status_code === 200) {
                    utils.updateFieldValue(path, data.value);
                    utils.showPopup('Value fetched successfully.');
                } else if (data.status_code === 202) {
                    utils.showPopup('Fetch value saved as task.');
                } else {
                    utils.showPopup('Fetch failed.');
                }
            } catch {
                utils.showPopup('Error fetching value.');
            }
        },

        setNode: async (button) => {
            const { path, type, value: currentValue } = button.dataset;
            const modal = jQuery('#setValueModal');
            modal.modal('show');
            document.getElementById('currentValue').value = currentValue || '';
            document.getElementById('newValue').value = '';
            document.getElementById('saveValueButton').setAttribute('data-path', path);
            document.getElementById('saveValueButton').setAttribute('data-type', type);
        },

        saveNodeValue: async () => {
            const newValue = document.getElementById('newValue').value;
            const path = document.getElementById('saveValueButton').dataset.path;
            const type = document.getElementById('saveValueButton').dataset.type;

            if (!newValue) {
                utils.showPopup('Please enter a new value.');
                return;
            }

            try {
                const data = await utils.fetchData('/device-action/set-Node', {
                    method: 'POST',
                    body: JSON.stringify({ serialNumber: config.serialNumber, path, type, value: newValue })
                });
                if (data.status_code === 200) {
                    utils.updateFieldValue(path, newValue);
                    utils.showPopup(`Value set successfully. (Status Code: ${data.status_code})`);
                } else if (data.status_code === 202) {
                    utils.showPopup(`Set value saved as task. (Status Code: ${data.status_code})`);
                } else {
                    utils.showPopup(`Failed to set value. (Status Code: ${data.status_code})`);
                }
            } catch {
                utils.showPopup('Error setting value.');
            } finally {
                jQuery('#setValueModal').modal('hide');
            }
        },

        executeCommand: async (action, serialNumber) => {
            try {
                const data = await utils.fetchData(`/device-action/${action}`, {
                    method: 'POST',
                    body: JSON.stringify({ serialNumber })
                });
                utils.showPopup(data.success ? `Device ${action} request accepted.` : `${action} failed: ${data.message}`);
            } catch {
                utils.showPopup(`${action} error occurred.`);
            }
        }
    };

    // Device Logs
    const deviceLogs = {
        init: () => {
            const modal = document.getElementById('deviceLogsModal');
            if (!modal.dataset.listener) {
                modal.dataset.listener = 'true';
                modal.addEventListener('show.bs.modal', async (event) => {
                    const deviceId = event.relatedTarget.dataset.deviceId;
                    if (!deviceId) {
                        document.getElementById('deviceLogsTableBody').innerHTML = '<tr><td colspan="4" class="text-center text-warning">Device ID not found.</td></tr>';
                        return;
                    }
                    await deviceLogs.fetch(deviceId);
                });
            }
        },

        fetch: async (deviceId, page = 1) => {
            const tableBody = document.getElementById('deviceLogsTableBody');
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center">Loading...</td></tr>';

            try {
                const data = await utils.fetchData(`/device-logs/${deviceId}?page=${page}`);
                if (!data.logs || data.logs.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No logs available.</td></tr>';
                    return;
                }
                tableBody.innerHTML = data.logs.map(log => `
                    <tr>
                        <td>${log.username || 'Unknown'}</td>
                        <td>${log.action || 'N/A'}</td>
                        <td>${log.response || 'N/A'}</td>
                        <td>${log.created_at ? new Date(log.created_at).toLocaleString() : 'N/A'}</td>
                    </tr>
                `).join('');
            } catch {
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Failed to load logs.</td></tr>';
            }
        }
    };

    // Heatmap
    const heatmap = {
        init: async () => {
            const heatmapRow = document.getElementById('HeatmapRow');
            const heatmapContainer = document.getElementById('heatmap');
            const MAX_DISPLAY_RADIUS = 180;

            try {
                const { data: devices } = await utils.fetchData(`/device/hosts/${config.serialNumber}`);
                if (!devices || devices.length === 0) {
                    heatmapRow.style.display = 'none';
                    return;
                }

                heatmapRow.style.display = 'flex';
                heatmap.createCircles(heatmapContainer);

                const totalDevices = devices.length;
                devices.forEach((device, index) => {
                    const angle = (index / totalDevices) * Math.PI * 2;
                    const signal = device.signalStrength || 0;
                    const distance = heatmap.getDistance(signal);
                    heatmap.createNode(device, angle, distance, heatmapContainer);
                    heatmap.addToTable(device);
                });

                heatmap.ensureTooltip();
            } catch {
                heatmapRow.style.display = 'none';
            }
        },

        getDistance: (signal) => {
            if (signal == null || signal === 0) return 30;
            if (signal >= -20) return 60;
            if (signal >= -40) return 90;
            if (signal >= -60) return 120;
            if (signal >= -80) return 150;
            return 180;
        },

        createCircles: (container) => {
            [30, 60, 90, 120, 150, 180].forEach(radius => {
                const circle = document.createElement('div');
                circle.className = 'radar-circle';
                circle.style.cssText = `width: ${radius * 2}px; height: ${radius * 2}px; left: ${250 - radius}px; top: ${250 - radius}px;`;
                container.appendChild(circle);
            });
        },

        createNode: (device, angle, distance, container) => {
            const node = document.createElement('div');
            node.className = 'device-node';
            const signal = device.signalStrength || 0;
            node.setAttribute('data-signal', signal === 0 ? 'unknown' : signal >= -30 ? 'strong' : signal >= -70 ? 'medium' : 'weak');
            node.style.cssText = `left: ${250 + Math.cos(angle) * distance - 15}px; top: ${250 + Math.sin(angle) * distance - 15}px; z-index: 10;`;
            node.innerHTML = '<i class="fa-solid fa-user"></i>';
            node.addEventListener('mouseenter', (e) => heatmap.showTooltip(e, device));
            node.addEventListener('mouseleave', heatmap.hideTooltip);
            container.appendChild(node);
        },

        addToTable: (device) => {
            const row = document.createElement('tr');
            const signalClass = device.signalStrength ? (device.signalStrength < -70 ? 'weak-signal' : 'good-signal') : '';
            row.innerHTML = `
                <td>${device.hostName || 'Unknown Device'}</td>
                <td class="${signalClass}">${device.signalStrength ? `${device.signalStrength} dBm` : 'N/A'}</td>
            `;
            document.getElementById('deviceTableBody').appendChild(row);
        },

        ensureTooltip: () => {
            let tooltip = document.getElementById('tooltip');
            if (!tooltip) {
                tooltip = document.createElement('div');
                tooltip.id = 'tooltip';
                tooltip.className = 'tooltip';
                document.body.appendChild(tooltip);
            }
            tooltip.style.cssText = `
                position: fixed; padding: 10px; background: rgba(0, 0, 0, 0.85); color: white; border-radius: 4px;
                font-size: 12px; pointer-events: none; opacity: 0; transition: opacity 0.2s; z-index: 9999;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5); max-width: 200px; word-wrap: break-word;
            `;
        },

        showTooltip: (event, device) => {
            const tooltip = document.getElementById('tooltip');
            tooltip.innerHTML = `
                <strong>${device.hostName || 'Unknown'}</strong><br>
                IP: ${device.ipAddress || 'N/A'}<br>
                MAC: ${device.macAddress || 'N/A'}<br>
                ${device.signalStrength ? `RSSI: ${device.signalStrength} dBm` : 'RSSI: N/A'}
            `;
            tooltip.style.left = `${event.clientX + 15}px`;
            tooltip.style.top = `${event.clientY - 15}px`;
            tooltip.style.opacity = '1';
        },

        hideTooltip: () => document.getElementById('tooltip').style.opacity = '0'
    };

    // WiFi Signals
    const wifiSignals = {
        init: async () => {
            const checkWifiBtn = document.getElementById('checkWifiBtn');

            try {
                const response = await utils.fetchData(`/wifi/standard-nodes/${config.serialNumber}`);
                const wifiList = Array.isArray(response) ? response : [];

                if (wifiList.length > 0) {
                    checkWifiBtn.disabled = false;
                    if (!checkWifiBtn.dataset.listener) {
                        checkWifiBtn.dataset.listener = 'true';
                        checkWifiBtn.addEventListener('click', wifiSignals.fetch);
                    }
                } else {
                    checkWifiBtn.disabled = true;
                }
            } catch (error) {
                console.error('Failed to fetch Wi-Fi signals:', error);
                checkWifiBtn.disabled = true;
            }
        },

        fetch: async () => {
            const modal = document.getElementById('wifiModal');
            const tableBody = document.getElementById('wifiTableBody');
            const recommendation = document.getElementById('recommendation');

            try {
                const wifiList = await utils.fetchData(`/wifi/standard-nodes/${config.serialNumber}`);
                tableBody.innerHTML = wifiList.map(wifi => `
                    <tr>
                        <td>${wifi.SSID}</td>
                        <td>${wifi.Channel}</td>
                    </tr>
                `).join('');

                if (wifiList.length > 0 && wifiList.some(w => parseInt(w.Channel) <= 14)) {
                    const interferenceScore = Array(12).fill(0);

                    wifiList.forEach(w => {
                        const ch = parseInt(w.Channel);
                        const signal = parseFloat(w.Signal);
                        if (ch >= 1 && ch <= 11) {
                            for (let offset = -2; offset <= 2; offset++) {
                                const target = ch + offset;
                                if (target >= 1 && target <= 11) {
                                    interferenceScore[target] += (1 / (Math.abs(offset) + 1)) * Math.abs(signal);
                                }
                            }
                        }
                    });

                    const bestChannel = interferenceScore
                        .map((score, i) => ({ channel: i, score }))
                        .filter(c => c.channel >= 1 && c.channel <= 11)
                        .sort((a, b) => a.score - b.score)[0];

                    recommendation.textContent = `📶 Best 2.4GHz Channel: ${bestChannel.channel}`;
                } else {
                    recommendation.textContent = wifiList.length > 0
                        ? 'No 2.4GHz channels detected.'
                        : 'No WiFi networks detected.';
                }

                modal.style.display = 'block';
            } catch (error) {
                console.error('Error fetching Wi-Fi data:', error);
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Failed to load WiFi signals.</td></tr>';
            }
        },

        setupModal: () => {
            const modal = document.getElementById('wifiModal');
            const closeButton = document.querySelector('.modal .close');

            if (!closeButton.dataset.listener) {
                closeButton.dataset.listener = 'true';
                closeButton.addEventListener('click', () => modal.style.display = 'none');
            }

            if (!window.wifiModalListener) {
                window.wifiModalListener = true;
                window.addEventListener('click', e => {
                    if (e.target === modal) modal.style.display = 'none';
                });
            }
        }
    };

    // Diagnostics
    const diagnostics = {
        init: () => {
            let selectedSerial = null;
            document.querySelectorAll('.diagnostics-button').forEach(btn => {
                if (!btn.dataset.listener) {
                    btn.dataset.listener = 'true';
                    btn.addEventListener('click', () => {
                        selectedSerial = btn.dataset.serialNumber;
                        document.getElementById('diagnostics-result').style.display = 'none';
                        document.getElementById('diagnostics-loading').style.display = 'none';
                        document.getElementById('diagnostics-form').reset();
                    });
                }
            });

            const runBtn = document.getElementById('run-diagnostics-btn');
            if (!runBtn.dataset.listener) {
                runBtn.dataset.listener = 'true';
                runBtn.addEventListener('click', async () => {
                    const host = document.getElementById('diagnostics-host').value;
                    const method = document.getElementById('diagnostics-method').value;
                    if (!host || !method || !selectedSerial) {
                        utils.showPopup('Please fill in all fields.');
                        return;
                    }

                    document.getElementById('diagnostics-loading').style.display = 'block';
                    document.getElementById('diagnostics-result').style.display = 'none';

                    try {
                        const data = await utils.fetchData(`/device/${selectedSerial}/diagnostics?host=${encodeURIComponent(host)}&method=${method}`);
                        document.getElementById('diagnostics-loading').style.display = 'none';
                        document.getElementById('diagnostics-result').style.display = 'block';
                        document.getElementById('diagnostics-data').textContent = JSON.stringify(data, null, 2);
                    } catch {
                        document.getElementById('diagnostics-loading').style.display = 'none';
                        document.getElementById('diagnostics-result').style.display = 'block';
                        document.getElementById('diagnostics-data').textContent = 'Diagnostics failed.';
                    }
                });
            }
        }
    };

    // Public API
    return {
        init: (options) => {
            config = { ...options };
            treeView.init();
            treeView.search();
            deviceLogs.init();
            heatmap.init();
            wifiSignals.init();
            wifiSignals.setupModal();
            diagnostics.init();

            document.querySelectorAll('.get-button').forEach(btn => {
                if (!btn.dataset.listener) {
                    btn.dataset.listener = 'true';
                    btn.addEventListener('click', () => deviceActions.getNode(btn));
                }
            });

            document.querySelectorAll('.set-button').forEach(btn => {
                if (!btn.dataset.listener) {
                    btn.dataset.listener = 'true';
                    btn.addEventListener('click', () => deviceActions.setNode(btn));
                }
            });

            const saveValueButton = document.getElementById('saveValueButton');
            if (!saveValueButton.dataset.listener) {
                saveValueButton.dataset.listener = 'true';
                saveValueButton.addEventListener('click', deviceActions.saveNodeValue);
            }

            document.querySelectorAll('.reboot-device, .reset-device').forEach(btn => {
                if (!btn.dataset.listener) {
                    btn.dataset.listener = 'true';
                    btn.addEventListener('click', () => deviceActions.executeCommand(
                        btn.classList.contains('reboot-device') ? 'reboot' : 'reset',
                        btn.dataset.serialNumber
                    ));
                }
            });
        }
    };
})();