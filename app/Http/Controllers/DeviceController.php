<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Device;
use App\Models\DeviceModel;
use App\Models\Host;
use App\Models\File;
use App\Http\Controllers\LogController; 


class DeviceController extends Controller
{
    public function index(){
        $devices_count = Device::count();
        $devices = Device::select(
            '_deviceId._SerialNumber', 
            '_deviceId._Manufacturer', 
            '_deviceId._OUI', 
            '_deviceId._ProductClass', 
            'InternetGatewayDevice.DeviceInfo.SoftwareVersion._value', 
            'InternetGatewayDevice.DeviceInfo.UpTime._value', 
            '_lastInform'
        )
        ->orderBy('_lastInform', 'desc') // Sort by _lastInform in descending order
        ->paginate(200);

        LogController::saveLog('devices_page_access', "User  opened devices page");
    
        return view('Devices.allDevices', compact('devices_count','devices'));
    }

    public function searchDevices(Request $request)
    {
        $type = $request->get('type'); // Search type
        $query = $request->get('query'); // Search query
    
        // Validate the search type
        $allowedTypes = ['_deviceId._SerialNumber', '_deviceId._Manufacturer', '_deviceId._OUI', '_deviceId._ProductClass'];
        if (!in_array($type, $allowedTypes)) {
            return response()->json(['error' => 'Invalid search type.'], 400);
        }
    
        // Perform the search with pagination
        $devices = Device::select(
            '_deviceId._SerialNumber', 
            '_deviceId._Manufacturer', 
            '_deviceId._OUI', 
            '_deviceId._ProductClass', 
            'InternetGatewayDevice.DeviceInfo.SoftwareVersion._value', 
            'InternetGatewayDevice.DeviceInfo.UpTime._value', 
            '_lastInform'
        )->where($type, 'LIKE', "%{$query}%")
          ->paginate(100);
    
        return response()->json([
            'devices' => $devices,
        ]);
    }
    

    public function getRfValues($deviceData)
    {
        $rfValues = [];
        $signalStatus = "Unknown";
    
        // Determine which model the device uses (TR-069 or TR-181)
        $useTR181 = isset($deviceData['Device']);
        $useTR069 = isset($deviceData['InternetGatewayDevice']);
    
        // Set base path based on detected model
        $basePath = $useTR181 ? $deviceData['Device'] : ($useTR069 ? $deviceData['InternetGatewayDevice'] : []);
    
        // Check for 5G and 4G nodes
        $is5G = $this->checkFor5GNodes($basePath);
        $is4G = $this->checkFor4GNodes($basePath);
    
        if ($is5G) {
            $rfValues = [
                '5G RSRP' => $basePath['children']['X_Web']['children']['MobileNetwork']['children']['SignalStatus']['children']['ENDC_RSRP']['value'] ?? null,
                '5G RSRQ' => $basePath['children']['X_Web']['children']['MobileNetwork']['children']['SignalStatus']['children']['ENDC_RSRQ']['value'] ?? null,
                '5G SNR'  => $basePath['children']['X_Web']['children']['MobileNetwork']['children']['SignalStatus']['children']['ENDC_SNR']['value'] ?? null,
                'RSRP'     => $basePath['children']['X_Web']['children']['MobileNetwork']['children']['SignalStatus']['children']['RSRP']['value'] ?? null,
                'RSRQ'     => $basePath['children']['X_Web']['children']['MobileNetwork']['children']['SignalStatus']['children']['RSRQ']['value'] ?? null,
                'SINR'     => $basePath['children']['X_Web']['children']['MobileNetwork']['children']['SignalStatus']['children']['SINR']['value'] ?? null,
            ];
            $signalStatus = $this->getSignalStrength($rfValues, true);
        } 
        elseif ($is4G) {
            $rfValues = [
                'RSCP' => $basePath['children']['WANDevice']['children']['1']['children']['X_WANNetConfigInfo']['children']['RSCP']['value'] ?? null,
                'RSRP' => $basePath['children']['WANDevice']['children']['1']['children']['X_WANNetConfigInfo']['children']['RSRP']['value'] ?? null,
                'RSRQ' => $basePath['children']['WANDevice']['children']['1']['children']['X_WANNetConfigInfo']['children']['RSRQ']['value'] ?? null,
            ];
            $signalStatus = $this->getSignalStrength($rfValues);
        }
    
        return ['rfValues' => $rfValues, 'signalStatus' => $signalStatus];
    }
    
    // Function to check for 5G nodes (TR-181 and TR-069)
    public function checkFor5GNodes($deviceData)
    {
        return isset($deviceData['children']['X_Web']['children']['MobileNetwork']['children']['SignalStatus']);
    }
    
    // Function to check for 4G nodes (TR-181 and TR-069)
    public function checkFor4GNodes($deviceData)
    {
        return isset($deviceData['children']['WANDevice']['children']['1']['children']['X_WANNetConfigInfo']);
    }
    
    // Function to determine signal strength
    public function getSignalStrength($rfValues, $is5G = false)
    {
        $parseDbm = function($value) {
            if (is_string($value) && preg_match('/(-?\d+) dBm/', $value, $matches)) {
                return (int) $matches[1];
            }
            return $value;
        };
    
        // Parse RSRP values
        $rsrp    = isset($rfValues['RSRP']) ? $parseDbm($rfValues['RSRP']) : null;
        $rsrq    = isset($rfValues['RSRQ']) ? $parseDbm($rfValues['RSRQ']) : null;
        $sinr    = isset($rfValues['SINR']) ? $parseDbm($rfValues['SINR']) : null;
        $rsrp5G  = isset($rfValues['5G RSRP']) ? $parseDbm($rfValues['5G RSRP']) : null;
        $rsrq5G  = isset($rfValues['5G RSRQ']) ? $parseDbm($rfValues['5G RSRQ']) : null;
        $sinr5G  = isset($rfValues['5G SNR']) ? $parseDbm($rfValues['5G SNR']) : null;
    
        $signalStatus = [];
    
        if ($is5G) {
            if ($rsrp5G !== null) {
                $signalStatus['5G'] = ($rsrp5G >= -65) ? 'Strong' : (($rsrp5G >= -90) ? 'Medium' : 'Weak');
            } else {
                $signalStatus['5G'] = 'Unknown';
            }
    
            if ($rsrp !== null) {
                $signalStatus['4G'] = ($rsrp >= -65) ? 'Strong' : (($rsrp >= -90) ? 'Medium' : 'Weak');
            } else {
                $signalStatus['4G'] = 'Unknown';
            }
        } 
        else {
            if ($rsrp !== null) {
                $signalStatus['4G'] = ($rsrp >= -65) ? 'Strong' : (($rsrp >= -90) ? 'Medium' : 'Weak');
            } else {
                $signalStatus['4G'] = 'Unknown';
            }
        }
    
        return $signalStatus;
    }
    


    public function info($serialNumber)
    {
        // Fetch the device data based on the serial number
        $device = Device::where('_deviceId._SerialNumber', $serialNumber)->first();
    
        // If device is not found, redirect back with an error message
        if (!$device) {
            return redirect()->back()->with('error', 'Device not found.');
        }
    
        // Log the action
        LogController::saveLog('device_index', "User opened the Device Info Page for: {$serialNumber}");
    
        // Get the model name
        $modelName = $device['_deviceId']['_ProductClass'] ?? null;
        $hostNodes = Host::where('Model', $modelName)->first();
    
        // Fetch software files related to this model
        $softwareFiles = File::where('metadata->productClass', $modelName)->get();
    
        // Convert device data to an array and process it using traverseJson
        $rawDeviceData = $device->toArray();
        $deviceData = $this->traverseJson($rawDeviceData);
    
        // Get RF values and signal status
        $rfData = $this->getRfValues($deviceData);
    
        // dd($rfData['signalStatus']);
        // Pass the processed data to the view
        return view('Devices.deviceInfo', [
            'deviceData' => $deviceData,
            'softwareFiles' => $softwareFiles,
            'rfValues' => $rfData['rfValues'],
            'signalStatus' => $rfData['signalStatus'],
        ]);
    }
    
    
    
    Public function device_model()
    {

        $devices_Models = DeviceModel::get();
        LogController::saveLog('device_models_index', "User opened the Device Models Page");
    
        // dd($devices_Models);
        return view('Devices.devicesModel', compact('devices_Models'));
    }

    public function index_Models($model)
    {
        $timestamp = now(); // Current timestamp
    
        // Log: User accessed the Models page
        LogController::saveLog('model_page_access', "User opened the Models Page for Model: {$model}");
    
        // Fetch devices based on the model
        $devices = Device::select(
            '_deviceId._SerialNumber', 
            '_deviceId._Manufacturer', 
            '_deviceId._OUI', 
            '_deviceId._ProductClass', 
            'InternetGatewayDevice.DeviceInfo.SoftwareVersion._value', 
            'InternetGatewayDevice.DeviceInfo.UpTime._value', 
            '_lastInform'
        )->where('_deviceId._ProductClass', $model)->paginate(200);
    
        
        return view('Devices.devicesModelIndex', compact('devices', 'model'));
    }
    

    public function searchDevicesByModel(Request $request)
    {
        $model = $request->get('model'); // Get the model from the GET parameter
        $query = $request->get('query'); // Get the search query from the GET parameter
        $searchType = $request->get('type', '_deviceId._SerialNumber'); // Get the search type with a default value
    
        // Validate the required model parameter
        if (!$model) {
            return response()->json(['error' => 'Model parameter is required.'], 400);
        }
    
        // Validate the search type to prevent invalid column access
        $allowedTypes = [
            '_deviceId._SerialNumber',
            '_deviceId._Manufacturer',
            '_deviceId._OUI',
            '_deviceId._ProductClass',
        ];
    
        if (!in_array($searchType, $allowedTypes)) {
            return response()->json(['error' => 'Invalid search type provided.'], 400);
        }
    
        // Perform the search with filtering by model and optional query
        $devices = Device::select(
            '_deviceId._SerialNumber',
            '_deviceId._Manufacturer',
            '_deviceId._OUI',
            '_deviceId._ProductClass',
            'InternetGatewayDevice.DeviceInfo.SoftwareVersion._value as SoftwareVersion',
            'InternetGatewayDevice.DeviceInfo.UpTime._value as UpTime',
            '_lastInform'
        )
            ->where('_deviceId._ProductClass', $model) // Filter by the provided model
            ->when($query, function ($q) use ($query, $searchType) {
                $q->where($searchType, 'LIKE', "%{$query}%");
            })
            ->paginate(100); // Adjust the pagination size as needed
    
        // Ensure the response is returned as JSON
        return response()->json([
            'success' => true,
            'model' => $model,
            'devices' => $devices,
        ]);
    }
    
    
    /**
     * Recursively traverse and process the JSON structure
     */
    private function traverseJson($data, $path = '')
    {
        $nodes = [];
    
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                // Skip unnecessary metadata keys
                if (in_array($key, ['_type', '_timestamp', '_writable', '_object'])) {
                    continue;
                }
    
                // Build the full path
                $newPath = $path ? $path . '.' . $key : $key;
    
                if (is_array($value)) {
                    // If it contains `_value`, treat it as a parameter (leaf node)
                    if (isset($value['_value'])) {
                        $nodes[$key] = [
                            'is_object' => false,
                            'value' => $value['_value'], // Display the actual value
                            'path' => $newPath, // The path without `_value`
                            'writable' => $value['_writable'] ?? false,
                            'type' => $value['_type'] ?? 'string', // Default to 'string' if not present
                        ];
                    } else {
                        // Treat it as an object (container)
                        $nodes[$key] = [
                            'is_object' => true,
                            'value' => null, // No direct value for containers
                            'path' => $newPath,
                            'writable' => false, // Containers are not writable
                            'type' => 'object', // Type is 'object' for containers
                            'children' => $this->traverseJson($value, $newPath), // Recursively process children
                        ];
                    }
                } else {
                    // Leaf node (direct value)
                    $nodes[$key] = [
                        'is_object' => false,
                        'value' => $value,
                        'path' => $newPath,
                        'writable' => false,
                        'type' => gettype($value), // Derive type from the value
                    ];
                }
            }
        }
    
        return $nodes;
    }

    private function url_ID($device)
    {
        $model = $device['_deviceId']['_ProductClass'];
        $oui = $device['_deviceId']['_OUI'];
        $serial = $device['_deviceId']['_SerialNumber'];

        if (str_contains($model, '-')) {
            $model = str_replace('-', '%252D', $model);
            $url_Id = $oui . '-' . $model . '-' . $serial;
            return $url_Id;
        }
        return $device->_id;
    }

    public function setNodeValue(Request $request)
    {
        // Validate incoming data
        $validated = $request->validate([
            'serialNumber' => 'required|string',
            'path' => 'required|string',
            'type' => 'required|string',
            'value' => 'required|string',
        ]);
    
        $serialNumber = $validated['serialNumber'];
        $path = $validated['path'];
        $type = $validated['type'];
        $newValue = $validated['value'];
    
        // Log the user initiating the set value action
        LogController::saveLog('device_set_action', "User initiated setting value for device {$serialNumber} on Node {$path}, New Value: {$newValue}");
    
        try {
            // Fetch only the necessary fields (_OUI, _ProductClass, _SerialNumber) for the given serial number
            $deviceData = Device::where('_deviceId._SerialNumber', $serialNumber)->first();
    
            if (!$deviceData || empty($deviceData['_deviceId']['_OUI']) || empty($deviceData['_deviceId']['_ProductClass']) || empty($deviceData['_deviceId']['_SerialNumber'])) {
                // Log failure if device is not found or incomplete information
                LogController::saveLog('device_set_action_failed', "Device with serial number {$serialNumber} not found or has incomplete device ID information.");
                return response()->json([
                    'success' => false,
                    'message' => "Device with serial number $serialNumber not found or incomplete device ID information.",
                ], 404);
            }
    
            // Generate the Device ID using the url_ID function
            $deviceId = $this->url_ID($deviceData);
    
            // Construct the API URL using the generated Device ID
            $apiUrl = "http://17.18.0.1:7557/devices/$deviceId/tasks?connection_request";
    
            // Construct the request payload
            $payload = [
                'name' => 'setParameterValues',
                'parameterValues' => [
                    [$path, $newValue, $type],
                ],
            ];
    
            // Send the POST request to the external API
            $response = Http::withOptions(['verify' => false]) // Disable SSL verification if needed
                            ->post($apiUrl, $payload);
    
            // Check the HTTP status code and log accordingly
            if ($response->status() === 200) {
                $responseData = $response->json();
                // Log success action
                LogController::saveLog('device_set_action_success', "Successfully set value for device {$serialNumber} on Node {$path}, New Value: {$newValue}");
                return response()->json([
                    'status_code' => $response->status(),
                    'success' => true,
                    'message' => "Value set successfully for $path ($type): $newValue",
                    'data' => $responseData,
                ]);
            } elseif ($response->status() === 202) {
                $responseData = $response->json();
                // Log task acceptance as pending
                LogController::saveLog('device_set_action_task', "Set value request for device {$serialNumber} on Node {$path} accepted as task: $newValue");
                return response()->json([
                    'status_code' => $response->status(),
                    'success' => true,
                    'message' => "Value set request accepted as a task for $path ($type): $newValue",
                    'data' => $responseData,
                ]);
            } else {
                // Log failure response
                LogController::saveLog('device_set_action_failed', "Failed to set value for device {$serialNumber} on Node {$path}");
                return response()->json([
                    'success' => false,
                    'message' => "Failed to set value for $path ($type): " . $response->body(),
                    'status_code' => $response->status(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            // Log exception error
            LogController::saveLog('device_set_action_failed', "Error occurred while setting value for device {$serialNumber}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "An error occurred while setting the value: " . $e->getMessage(),
            ], 500);
        }
    }
    

    public function getNodeValue(Request $request)
    {
        // Validate incoming data
        $validated = $request->validate([
            'serialNumber' => 'required|string',
            'path' => 'required|string',
            'type' => 'required|string',
        ]);

        $serialNumber = $validated['serialNumber'];
        $path = $validated['path'];
        $type = $validated['type'];

        try {
            // Fetch only the necessary fields (_OUI, _ProductClass, _SerialNumber) for the given serial number
            $deviceData = Device::where('_deviceId._SerialNumber', $serialNumber)->first();

            if (!$deviceData || empty($deviceData['_deviceId']['_OUI']) || empty($deviceData['_deviceId']['_ProductClass']) || empty($deviceData['_deviceId']['_SerialNumber'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Device with serial number $serialNumber not found or incomplete device ID information.",
                ], 404);
            }

            LogController::saveLog('device_get_action', "User initiated getting value for device {$serialNumber} on Node {$path}");
    
            // Generate the Device ID using the url_ID function
            $deviceId = $this->url_ID($deviceData);

            // Construct the API URL using the generated Device ID
            $apiUrl = "http://17.18.0.1:7557/devices/$deviceId/tasks?connection_request";

            // Construct the request payload
            $payload = [
                'name' => 'getParameterValues',
                'parameterNames' => [
                    $path
                ],
            ];

            // Send the POST request to the external API
            $response = Http::withOptions(['verify' => false]) // Disable SSL verification if needed
                            ->post($apiUrl, $payload);

            // Check the HTTP status code
            if ($response->status() === 200) {
                // Fetch the latest value from the MongoDB database using the helper function
                $latestDeviceData = Device::where('_deviceId._SerialNumber', $serialNumber)->first();
                $value = $this->searchMongoData($latestDeviceData->toArray(), $path); // Use the helper function
                

                LogController::saveLog('device_get_action_success', "Successfully get value for device {$serialNumber} on Node {$path}, New Value: {$value}");
                return response()->json([
                    'success' => true,
                    'message' => 'Value fetched successfully',
                    'status_code' => $response->status(),
                    'value' => $value,
                    'response_data' => $response->json(), // Optional: Include external API response
                ]);
            } elseif ($response->status() === 202) {
                LogController::saveLog('device_get_action_task', "get value request for device {$serialNumber} on Node {$path} accepted as task");
                return response()->json([
                    'success' => true,
                    'message' => "Value fetch request accepted as a task for $path ($type)",
                    'status_code' => $response->status(),
                    'data' => $response->json(),
                ]);
            } else {
                LogController::saveLog('device_get_action_failed', "Failed to get value for device {$serialNumber} on Node {$path}");
                return response()->json([
                    'success' => false,
                    'message' => "Failed to fetch value for $path ($type): " . $response->body(),
                    'status_code' => $response->status(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            LogController::saveLog('device_set_action_failed', "Error occurred while setting value for device {$serialNumber}");
            return response()->json([
                'success' => false,
                'message' => "An error occurred while fetching the value: " . $e->getMessage(),
            ], 500);
        }
    }

    public function RebootDevice(Request $request)
    {
        // Validate incoming data
        $validated = $request->validate([
            'serialNumber' => 'required|string',
        ]);
    
        $serialNumber = $validated['serialNumber'];
    
        try {
            // Fetch only the necessary fields (_OUI, _ProductClass, _SerialNumber) for the given serial number
            $deviceData = Device::where('_deviceId._SerialNumber', $serialNumber)->first();
    
            if (!$deviceData || empty($deviceData['_deviceId']['_OUI']) || empty($deviceData['_deviceId']['_ProductClass']) || empty($deviceData['_deviceId']['_SerialNumber'])) {
                // Log failure if device not found
                LogController::saveLog('device_reboot_failed', "Device with serial number {$serialNumber} not found or incomplete device ID information.");
                return response()->json([
                    'success' => false,
                    'message' => "Device with serial number $serialNumber not found or incomplete device ID information.",
                ], 404);
            }
    
            // Log the reboot action initiation
            LogController::saveLog('device_reboot_action', "User initiated reboot for device {$serialNumber}");
    
            // Generate the Device ID using the url_ID function
            $deviceId = $this->url_ID($deviceData);
    
            // Construct the API URL using the generated Device ID
            $apiUrl = "http://17.18.0.1:7557/devices/$deviceId/tasks?connection_request";
    
            // Construct the request payload
            $payload = [
                'name' => 'reboot',
            ];
    
            // Send the POST request to the external API
            $response = Http::withOptions(['verify' => false]) // Disable SSL verification if needed
                            ->post($apiUrl, $payload);
    
            // Check the HTTP status code and log the result
            if ($response->status() === 200) {
                // Log success
                LogController::saveLog('device_reboot_success', "Device {$serialNumber} rebooted successfully.");
                return response()->json([
                    'success' => true,
                    'message' => 'Device Rebooted successfully',
                    'status_code' => $response->status(),
                ]);
            } elseif ($response->status() === 202) {
                // Log task acceptance
                LogController::saveLog('device_reboot_task', "Reboot request for device {$serialNumber} saved as a task.");
                return response()->json([
                    'success' => true,
                    'message' => "Device Reboot Saved as a Task",
                    'status_code' => $response->status(),
                    'data' => $response->json(),
                ]);
            } else {
                // Log failure
                LogController::saveLog('device_reboot_failed', "Failed to reboot device {$serialNumber}: " . $response->body());
                return response()->json([
                    'success' => false,
                    'message' => "Fail to Reboot this device: " . $response->body(),
                    'status_code' => $response->status(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            // Log exception
            LogController::saveLog('device_reboot_failed', "Error occurred while rebooting device {$serialNumber}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "An error occurred while rebooting the device: " . $e->getMessage(),
            ], 500);
        }
    }
    

    public function ResetDevice(Request $request)
    {
        // Validate incoming data
        $validated = $request->validate([
            'serialNumber' => 'required|string',
        ]);
    
        $serialNumber = $validated['serialNumber'];
    
        try {
            // Fetch only the necessary fields (_OUI, _ProductClass, _SerialNumber) for the given serial number
            $deviceData = Device::where('_deviceId._SerialNumber', $serialNumber)->first();
    
            if (!$deviceData || empty($deviceData['_deviceId']['_OUI']) || empty($deviceData['_deviceId']['_ProductClass']) || empty($deviceData['_deviceId']['_SerialNumber'])) {
                // Log failure if device not found
                LogController::saveLog('device_reset_failed', "Device with serial number {$serialNumber} not found or incomplete device ID information.");
                return response()->json([
                    'success' => false,
                    'message' => "Device with serial number $serialNumber not found or incomplete device ID information.",
                ], 404);
            }
    
            // Log the reset action initiation
            LogController::saveLog('device_reset_action', "User initiated factory reset for device {$serialNumber}");
    
            // Generate the Device ID using the url_ID function
            $deviceId = $this->url_ID($deviceData);
    
            // Construct the API URL using the generated Device ID
            $apiUrl = "http://17.18.0.1:7557/devices/$deviceId/tasks?connection_request";
    
            // Construct the request payload
            $payload = [
                'name' => 'factoryReset',
            ];
    
            // Send the POST request to the external API
            $response = Http::withOptions(['verify' => false]) // Disable SSL verification if needed
                            ->post($apiUrl, $payload);
    
            // Check the HTTP status code and log the result
            if ($response->status() === 200) {
                // Log success
                LogController::saveLog('device_reset_success', "Device {$serialNumber} reset successfully.");
                return response()->json([
                    'success' => true,
                    'message' => 'Device reset successfully',
                    'status_code' => $response->status(),
                ]);
            } elseif ($response->status() === 202) {
                // Log task acceptance
                LogController::saveLog('device_reset_task', "Factory reset request for device {$serialNumber} saved as a task.");
                return response()->json([
                    'success' => true,
                    'message' => "Factory reset request accepted as a task for device {$serialNumber}",
                    'status_code' => $response->status(),
                    'data' => $response->json(),
                ]);
            } else {
                // Log failure
                LogController::saveLog('device_reset_failed', "Failed to reset device {$serialNumber}: " . $response->body());
                return response()->json([
                    'success' => false,
                    'message' => "Failed to reset device: " . $response->body(),
                    'status_code' => $response->status(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            // Log exception
            LogController::saveLog('device_reset_failed', "Error occurred while resetting device {$serialNumber}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "An error occurred while resetting the device: " . $e->getMessage(),
            ], 500);
        }
    }
    

    public function destroy($id)
    {
        // Attempt to find the device by its serial number
        $device = Device::where('_deviceId._SerialNumber', $id)->first();
    
        if (!$device) {
            // Log failure if device not found
            LogController::saveLog('device_delete_failed', "Device with serial number {$id} not found.");
            return redirect()->route('dashboard')->with('error', 'Device not found.');
        }
    
        try {
            $device->delete();
            // Log success
            LogController::saveLog('device_delete_success', "Device with serial number {$id} deleted successfully.");
            return redirect()->route('dashboard')->with('success', 'Device deleted successfully.');
        } catch (\Exception $e) {
            // Log exception
            LogController::saveLog('device_delete_failed', "Error occurred while deleting device {$id}: " . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Failed to delete device.');
        }
    }
    
    


    /**
     * Search for a value in nested MongoDB data based on the given path.
     *
     * @param array $data The MongoDB document or array.
     * @param string $path The dot-separated path to the desired value.
     * @return mixed|null The value if found, or null if not found.
     */
    private function searchMongoData(array $data, string $path)
    {
        $keys = explode('.', $path); // Split the dot-separated path into keys.

        foreach ($keys as $key) {
            if (!isset($data[$key])) {
                return null; // If any key is missing, return null.
            }
            $data = $data[$key]; // Traverse deeper into the data.
        }

        // Check if the final node is an object
        if (is_array($data) && isset($data['_value'])) {
            return $data['_value']; // Return the _value if present
        } elseif (is_array($data)) {
            return ''; // Return empty if it's an object without _value
        }

        return $data; // Return the final value if it's not an object
    }

}