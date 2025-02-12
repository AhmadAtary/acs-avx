<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Device;
use App\Models\DModel;
use App\Models\Host;
use App\Models\File;


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

        return view('Devices.allDevices', compact('devices_count','devices'));
    }

    public function searchDevices(Request $request)
    {
        $query = $request->get('query');

        // Perform the search with pagination
        $devices = Device::select(
            '_deviceId._SerialNumber', 
            '_deviceId._Manufacturer', 
            '_deviceId._OUI', 
            '_deviceId._ProductClass', 
            'InternetGatewayDevice.DeviceInfo.SoftwareVersion._value', 
            'InternetGatewayDevice.DeviceInfo.UpTime._value', 
            '_lastInform'
            )->where('_deviceId._SerialNumber', 'LIKE', "%{$query}%")->paginate(10000);

        return response()->json([
            'devices' => $devices,
        ]);
    }

    public function info($serialNumber)
    {
        // Fetch the device data based on the serial number
        $device = Device::where('_deviceId._SerialNumber', $serialNumber)->first();
        $modelName = $device['_deviceId']['_ProductClass'] ?? null;
        $hostNodes = Host::where('Model', $modelName)->first();

        $softwareFiles = File::where('metadata->productClass', $modelName)->get();
    
        if ($device) {
            // Convert the device data to an array and process it using traverseJson
            $rawDeviceData = $device->toArray();
            $deviceData = $this->traverseJson($rawDeviceData);
        } else {
            $deviceData = null; // Handle case where no device is found
        }
        
        // dd($deviceData['_deviceId']['children']['_SerialNumber']);
        // Pass the processed data to the view
        return view('Devices.deviceInfo', compact('deviceData','softwareFiles'));
    }
    
    Public function device_model()
    {

        $devices_Models = DModel::get();

        return view('Devices.devicesModel', compact('devices_Models'));
    }

    public function index_Models($model)
    {
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
    
        if (!$model) {
            return response()->json(['error' => 'Model parameter is required'], 400);
        }
    
        // Search with pagination
        $devices = Device::select(
            '_deviceId._SerialNumber',
            '_deviceId._Manufacturer',
            '_deviceId._OUI',
            '_deviceId._ProductClass',
            'InternetGatewayDevice.DeviceInfo.SoftwareVersion._value',
            'InternetGatewayDevice.DeviceInfo.UpTime._value',
            '_lastInform'
        )
        ->where('_deviceId._ProductClass', $model) // Filter by model
        ->when($query, function ($q) use ($query) {
            $q->where('_deviceId._SerialNumber', 'LIKE', "%{$query}%");
        })
        ->paginate(200); // Adjust pagination as required
    
        // Return JSON response
        return response()->json([
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
    
        try {
            // Fetch only the necessary fields (_OUI, _ProductClass, _SerialNumber) for the given serial number
            $deviceData = Device::where('_deviceId._SerialNumber', $serialNumber)->first();
            
            if (!$deviceData || empty($deviceData['_deviceId']['_OUI']) || empty($deviceData['_deviceId']['_ProductClass']) || empty($deviceData['_deviceId']['_SerialNumber'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Device with serial number $serialNumber not found or incomplete device ID information.",
                ], 404);
            }
    
            // Generate the Device ID using the url_ID function
            $deviceId = $this->url_ID($deviceData);
    
            // Construct the API URL using the generated Device ID
            $apiUrl = "https://10.223.169.1:7557/devices/$deviceId/tasks?connection_request";
    
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
    
            // Check the HTTP status code
            if ($response->status() === 200) {
                $responseData = $response->json();
                return response()->json([
                    'status_code' => $response->status(),
                    'success' => true,
                    'message' => "Value set successfully for $path ($type): $newValue",
                    'data' => $responseData,
                ]);
            } elseif ($response->status() === 202) {
                $responseData = $response->json();
                return response()->json([
                    'status_code' => $response->status(),
                    'success' => true,
                    'message' => "Value set request accepted as a task for $path ($type): $newValue",
                    'data' => $responseData,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Failed to set value for $path ($type): " . $response->body(),
                    'status_code' => $response->status(),
                ], $response->status());
            }
        } catch (\Exception $e) {
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

            // Generate the Device ID using the url_ID function
            $deviceId = $this->url_ID($deviceData);

            // Construct the API URL using the generated Device ID
            $apiUrl = "https://10.223.169.1:7557/devices/$deviceId/tasks?connection_request";

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
                

                return response()->json([
                    'success' => true,
                    'message' => 'Value fetched successfully',
                    'status_code' => $response->status(),
                    'value' => $value,
                    'response_data' => $response->json(), // Optional: Include external API response
                ]);
            } elseif ($response->status() === 202) {
                return response()->json([
                    'success' => true,
                    'message' => "Value fetch request accepted as a task for $path ($type)",
                    'status_code' => $response->status(),
                    'data' => $response->json(),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Failed to fetch value for $path ($type): " . $response->body(),
                    'status_code' => $response->status(),
                ], $response->status());
            }
        } catch (\Exception $e) {
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
                return response()->json([
                    'success' => false,
                    'message' => "Device with serial number $serialNumber not found or incomplete device ID information.",
                ], 404);
            }
    
            // Generate the Device ID using the url_ID function
            $deviceId = $this->url_ID($deviceData);
    
            // Construct the API URL using the generated Device ID
            $apiUrl = "https://10.223.169.1:7557/devices/$deviceId/tasks?connection_request";
    
            // Construct the request payload
            $payload = [
                'name' => 'reboot',
            ];
            
 

            // Send the POST request to the external API
            $response = Http::withOptions(['verify' => false]) // Disable SSL verification if needed
                            ->post($apiUrl, $payload);
            // dd($response);
            // Check the HTTP status code
            if ($response->status() === 200) {
                $responseData = $response->json();

                return response()->json([
                    'success' => true,
                    'message' => 'Device reset successfully',
                    'status_code' => $response->status(),
                ]);
 
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Failed to fetch value for $path ($type): " . $response->body(),
                    'status_code' => $response->status(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "An error occurred while fetching the value: " . $e->getMessage(),
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
                return response()->json([
                    'success' => false,
                    'message' => "Device with serial number $serialNumber not found or incomplete device ID information.",
                ], 404);
            }
    
            // Generate the Device ID using the url_ID function
            $deviceId = $this->url_ID($deviceData);
    
            // Construct the API URL using the generated Device ID
            $apiUrl = "https://10.223.169.1:7557/devices/$deviceId/tasks?connection_request";
    
            // Construct the request payload
            $payload = [
                'name' => 'factoryReset',
            ];
            
 

            // Send the POST request to the external API
            $response = Http::withOptions(['verify' => false]) // Disable SSL verification if needed
                            ->post($apiUrl, $payload);
            // dd($response);
            // Check the HTTP status code
            if ($response->status() === 200) {
                $responseData = $response->json();
    
                // Extract the value from the response
                $value = isset($responseData['parameterValues'][0]) ? $responseData['parameterValues'][0] : null;
    
                return response()->json([
                    'success' => true,
                    'value' => $value,
                ]);
            } elseif ($response->status() === 202) {
                return response()->json([
                    'success' => true,
                    'message' => "Value fetch request accepted as a task for $path ($type)",
                    'data' => $response->json(),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Failed to fetch value for $path ($type): " . $response->body(),
                    'status_code' => $response->status(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "An error occurred while fetching the value: " . $e->getMessage(),
            ], 500);
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