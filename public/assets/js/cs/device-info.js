document.addEventListener("DOMContentLoaded", () => {
    // Retrieve configuration from global window object or data attributes
    const config = window.deviceConfig || {};
    const serialNumber = config.serialNumber || document.querySelector('[data-serial-number]')?.dataset.serialNumber || 'Unknown';
    const csrfToken = config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content;
    const manageCustomerRoute = '/Customer-serves/device/manage';

    if (!csrfToken) {
        console.error('CSRF token not found');
        return;
    }

    if (!serialNumber || serialNumber === 'Unknown') {
        console.warn('Serial number not provided, some features may not work');
    }

    // Utility Functions
    const utils = {
        showLoading: () => {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'block';
        },
        hideLoading: () => {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'none';
        },
        showPopup: (message, isError = false, duration = 5000) => {
            const popup = document.getElementById('simplePopup');
            const popupMessage = document.getElementById('popupMessage');
            if (popup && popupMessage) {
                popupMessage.innerHTML = message;
                popup.style.display = 'block';
                popup.style.background = isError ? '#f8d7da' : '#fff';
                popup.classList.add('show');
                setTimeout(() => {
                    popup.classList.remove('show');
                    setTimeout(() => popup.style.display = 'none', 300);
                }, duration);
            }
        },
        fetchData: async (url, options = {}) => {
            utils.showLoading();
            try {
                const response = await fetch(url, {
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', ...options.headers },
                    ...options
                });
                if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
                return await response.json();
            } catch (error) {
                console.error(`Fetch error: ${error}`);
                utils.showPopup(`Error: ${error.message}`, true);
                throw error;
            } finally {
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

    // Generate Link Functions
    const linkGeneration = {
        initializeForm: () => {
            const linkInput = document.getElementById('link');
            const passwordInput = document.getElementById('password');
            const expiresAtInput = document.getElementById('expires_at');
            
            linkInput.value = `${window.location.origin}/end-user-login/${linkGeneration.generateRandomString(32)}`;
            passwordInput.value = linkGeneration.generateRandomString(12);
            const now = new Date();
            now.setMinutes(now.getMinutes() + 10);
            expiresAtInput.value = now.toISOString().slice(0, 16);
        },
        generateRandomString: (length) => {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let result = '';
            for (let i = 0; i < length; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return result;
        },
        copyText: async (fieldId) => {
            const input = document.getElementById(fieldId);
            try {
                await navigator.clipboard.writeText(input.value);
                utils.showPopup(`Copied to clipboard!`, false, 2000);
            } catch (err) {
                console.error('Copy error:', err);
                utils.showPopup('Failed to copy text.', true, 2000);
            }
        },
        regenerateLink: async () => {
            utils.showLoading();
            try {
                const linkInput = document.getElementById('link');
                const passwordInput = document.getElementById('password');
                linkInput.value = `${window.location.origin}/end-user-login/${linkGeneration.generateRandomString(32)}`;
                passwordInput.value = linkGeneration.generateRandomString(12);
                const now = new Date();
                now.setMinutes(now.getMinutes() + 10);
                document.getElementById('expires_at').value = now.toISOString().slice(0, 16);
                utils.showPopup(`New Link Generated!<br>Password: ${passwordInput.value}`, false, 3000);
            } catch (error) {
                console.error('Regenerate link error:', error);
                utils.showPopup(`Error regenerating link: ${error.message}`, true, 3000);
            } finally {
                utils.hideLoading();
            }
        }
    };

    // Tree View Management
    const treeView = {
        init: () => {
            document.querySelectorAll('.expand-icon').forEach(toggle => {
                toggle.addEventListener('click', () => {
                    const parentLi = toggle.closest('li');
                    const childUl = parentLi.querySelector('ul');
                    if (childUl) {
                        childUl.classList.toggle('collapsed');
                        childUl.classList.toggle('expanded');
                        toggle.textContent = childUl.classList.contains('expanded') ? '▼' : '▶';
                    }
                });
            });
        },
        search: () => {
            const searchBar = document.getElementById('search-bar');
            if (!searchBar) return;
            const clearButton = document.getElementById('clear-search');
            const treeItems = document.querySelectorAll('.node-content');

            searchBar.addEventListener('input', () => {
                const query = searchBar.value.trim().toLowerCase();
                treeView.reset();
                if (!query) return;

                treeItems.forEach(item => {
                    const valueEl = item.querySelector('.node-value');
                    const nameEl = item.querySelector('.node-name');
                    const path = valueEl?.id?.toLowerCase() || '';
                    const name = nameEl?.textContent?.toLowerCase() || '';
                    const value = valueEl?.textContent?.toLowerCase() || '';

                    if (path.includes(query) || name.includes(query) || value.includes(query)) {
                        if (path.includes(query)) valueEl.classList.add('highlight');
                        else if (name.includes(query)) nameEl.classList.add('highlight');
                        else valueEl.classList.add('highlight');

                        let parent = item.closest('ul');
                        while (parent) {
                            parent.classList.remove('collapsed');
                            parent.classList.add('expanded');
                            parent = parent.parentElement.closest('ul');
                        }
                    }
                });
            });

            clearButton.addEventListener('click', () => {
                searchBar.value = '';
                treeView.reset();
            });
        },
        reset: () => {
            document.querySelectorAll('.highlight').forEach(el => el.classList.remove('highlight'));
            document.querySelectorAll('ul').forEach(ul => ul.classList.add('collapsed'));
        }
    };

    // Device Actions
    const deviceActions = {
        init: () => {
            console.log('loading device actions started');
        },
        getNode: async (button) => {
            const { path, type } = button.dataset;
            try {
                const data = await utils.fetchData('/device-action/get-Node', {
                    method: 'POST',
                    body: JSON.stringify({ serialNumber, path, type })
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
            $('#setValueModal').modal('show');
            document.getElementById('currentValue').value = currentValue;
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
                    body: JSON.stringify({ serialNumber, path, type, value: newValue })
                });
                if (data.status_code === 200) {
                    utils.updateFieldValue(path, newValue);
                    utils.showPopup('Value set successfully.');
                    console.log('loading device actions finished');
                } else if (data.status_code === 202) {
                    utils.showPopup('Set value saved as task.');
                }
                $('#setValueModal').modal('hide');
            } catch {
                utils.showPopup('Error setting value.');
                $('#setValueModal').modal('hide');
            }
        },
        executeCommand: async (action, serialNumber) => {
            try {
                const data = await utils.fetchData(`/device-action/${action}`, {
                    method: 'POST',
                    body: JSON.stringify({ serialNumber })
                });
                utils.showPopup(data.success ? `Device ${action} request accepted.` : `${action} failed: ${data.message}`);
            } catch (error) {
                utils.showPopup(`${action} error occurred: ${error.message}`, true);
            }
        }
    };

    // Heatmap Management
    const heatmap = {
        init: async () => {
            const heatmapRow = document.querySelector('.heatmap-row');
            const heatmapContainer = document.getElementById('heatmap');
            const MAX_DISPLAY_RADIUS = 180;

            try {
                const { data: devices } = await utils.fetchData(`/device/hosts/${encodeURIComponent(serialNumber)}`);
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
            } catch (error) {
                console.error('Error initializing heatmap:', error);
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
        hideTooltip: () => {
            const tooltip = document.getElementById('tooltip');
            if (tooltip) tooltip.style.opacity = '0';
        }
    };

    // Diagnostics Management
    const diagnostics = {
        init: () => {
            document.getElementById('run-diagnostics-btn').addEventListener('click', async () => {
                const host = document.getElementById('diagnostics-host').value;
                const method = document.getElementById('diagnostics-method').value;
                if (!host || !method) {
                    utils.showPopup('Please provide a target IP and select a method.');
                    return;
                }

                document.getElementById('diagnostics-loading').style.display = 'block';
                document.getElementById('diagnostics-result').style.display = 'none';

                try {
                    const data = await utils.fetchData(`/device/${encodeURIComponent(serialNumber)}/diagnostics?host=${encodeURIComponent(host)}&method=${method}`);
                    document.getElementById('diagnostics-loading').style.display = 'none';
                    document.getElementById('diagnostics-result').style.display = 'block';
                    const results = data.results;
                    if (data.success) {
                        if (method === 'Ping') {
                            document.getElementById('diagnostics-data').textContent = `Success Count: ${results.SuccessCount ?? 'N/A'}\nFailure Count: ${results.FailureCount ?? 'N/A'}`;
                        } else {
                            document.getElementById('diagnostics-data').textContent = results.map(hop =>
                                `${hop.HopNumber}. ${hop.HopHostAddress ?? '*'} (${hop.HopRTTimes ?? '*'} ms)`
                            ).join('\n') || 'No hops found.';
                        }
                    } else {
                        document.getElementById('diagnostics-data').textContent = data.message || 'Diagnostics failed.';
                    }
                } catch {
                    document.getElementById('diagnostics-loading').style.display = 'none';
                    document.getElementById('diagnostics-result').style.display = 'block';
                    document.getElementById('diagnostics-data').textContent = 'Failed to run diagnostics.';
                }
            });
        }
    };

    // Node Management
    const nodeManagement = {
        init: () => {
            document.querySelectorAll('.manage-btn').forEach(button => {
                button.addEventListener('click', async function () {
                    const tabPane = this.closest('.tab-pane');
                    const deviceId = tabPane.querySelector('.device-id').value;
                    const urlId = tabPane.querySelector('.url-id').value;
                    const action = this.getAttribute('data-action');

                    if (!manageCustomerRoute) {
                        utils.showPopup('Error: Customer management route not configured.', true);
                        return;
                    }

                    utils.showLoading();

                    const nodeInputs = tabPane.querySelectorAll('.node-value');
                    const nodes = {};
                    nodeInputs.forEach(input => {
                        const nodePath = input.getAttribute('data-node');
                        if (action === 'SET') {
                            nodes[nodePath] = { value: input.value.trim() };
                        } else {
                            nodes[nodePath] = {};
                        }
                    });

                    try {
                        const response = await fetch(manageCustomerRoute, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                device_id: deviceId,
                                url_Id: urlId,
                                action: action,
                                nodes: nodes
                            })
                        });

                        const result = await response.json();

                        if (response.ok) {
                            utils.showPopup(`Action ${action} completed successfully.`);
                        } else {
                            utils.showPopup(`Error: ${result.message || 'An error occurred.'}`, true);
                        }
                    } catch (error) {
                        utils.showPopup(`Error: ${error.message}`, true);
                    } finally {
                        utils.hideLoading();
                    }
                });
            });
        }
    };

    // Link Generation Form Handler
    const generateLinkForm = document.getElementById('generate-link-form');
    if (generateLinkForm) {
        document.getElementById('generateLinkModal').addEventListener('show.bs.modal', linkGeneration.initializeForm);
        generateLinkForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            utils.showLoading();

            try {
                const linkInput = document.getElementById('link');
                const passwordInput = document.getElementById('password');
                const expiresAtInput = document.getElementById('expires_at');
                const usernameInput = document.getElementById('username');

                const expiresAt = new Date(expiresAtInput.value);
                if (isNaN(expiresAt.getTime())) {
                    throw new Error('Invalid expiration date');
                }

                const formData = new FormData(this);
                formData.set('link', linkInput.value);
                formData.set('password', passwordInput.value);
                formData.set('username', usernameInput.value);
                formData.set('expires_at', expiresAtInput.value);

                const response = await fetch(generateLinkForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    linkInput.value = data.link || linkInput.value;
                    passwordInput.value = data.password || passwordInput.value;
                    expiresAtInput.value = data.expires_at ?
                        new Date(data.expires_at).toISOString().slice(0, 16) :
                        expiresAtInput.value;

                    await linkGeneration.copyText('link');
                    utils.showPopup(`Link Generated Successfully!<br>Password: ${passwordInput.value}`, false, 3000);
                } else {
                    throw new Error(data.message || 'Failed to generate link.');
                }
            } catch (error) {
                console.error('Generate link error:', error);
                utils.showPopup(`Error generating link: ${error.message}`, true, 3000);
            } finally {
                utils.hideLoading();
            }
        });
    }

    // Initialize Components
    treeView.init();
    treeView.search();
    heatmap.init();
    diagnostics.init();
    deviceActions.init();
    nodeManagement.init();

    // Event Listeners
    document.querySelectorAll('.get-button').forEach(btn => btn.addEventListener('click', () => deviceActions.getNode(btn)));
    document.querySelectorAll('.set-button').forEach(btn => btn.addEventListener('click', () => deviceActions.setNode(btn)));
    document.getElementById('saveValueButton').addEventListener('click', deviceActions.saveNodeValue);
    document.querySelectorAll('.reboot-device, .reset-device').forEach(btn => {
        btn.addEventListener('click', () => deviceActions.executeCommand(btn.classList.contains('reboot-device') ? 'reboot' : 'reset', btn.dataset.serialNumber));
    });
});