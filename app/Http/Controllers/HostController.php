<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Host;
use App\Models\Device;
use Illuminate\Support\Facades\DB;




class HostController extends Controller
{
        // Show a form to create a new Host
        public function create()
        {
            return view('hosts.create'); // Create a view for this form
        }
    
        // Store a new Host in the database
        public function store(Request $request)
        {
            // Validate the request
            $validated = $request->validate([
                'Model' => 'required|string|unique:Hosts,Model',
                'Product_Class' => 'required|string',
                'HostName' => 'nullable|string',
                'IPAddress' => 'nullable|string',
                'MACAddress' => 'nullable|string',
                'RSSI' => 'nullable|integer',
                'hostPath' => 'nullable|string',
            ]);
    
            // Create a new Host
            $host = Host::create($validated);
    
            return redirect()->back()->with('success', 'Host added successfully!');
        }
    
        // Show a form to edit an existing Host
        public function edit($id)
        {
            $host = Host::findOrFail($id);
    
            return view('hosts.edit', compact('host'));
        }
    
        // Update an existing Host in the database
        public function update(Request $request, $id)
        {
            // Validate the request
            $validated = $request->validate([
                'Model' => 'required|string|unique:Hosts,Model,' . $id,
                'Product_Class' => 'required|string',
                'HostName' => 'nullable|string',
                'IPAddress' => 'nullable|string',
                'MACAddress' => 'nullable|string',
                'RSSI' => 'nullable|integer',
                'hostPath' => 'nullable|string',
            ]);
    
            // Find the Host and update it
            $host = Host::findOrFail($id);
            $host->update($validated);
    
            return redirect()->back()->with('success', 'Host updated successfully!');
        }

        public function HostsInfo($serialNumber)
        {
            try {
                // Step 1: Fetch the device by serial number
                $device = Device::where('_deviceId._SerialNumber', $serialNumber)->first();
        
                if (!$device) {
                    return response()->json([
                        'success' => false,
                        'message' => "No device found with serial number: $serialNumber",
                    ], 404);
                }
        
                // Convert device to an array
                $deviceArray = $device->toArray();
        
                // Step 2: Extract the model name
                $modelName = $deviceArray['_deviceId']['_ProductClass'] ?? null;
        
                if (!$modelName) {
                    return response()->json([
                        'success' => false,
                        'message' => "No model information found for serial number: $serialNumber",
                    ], 404);
                }
        
                // Step 3: Fetch host node definitions
                $hostNodes = Host::where('Model', $modelName)->first();
        
                if (!$hostNodes) {
                    return response()->json([
                        'success' => false,
                        'message' => "No host nodes configuration found for model: $modelName",
                    ], 404);
                }
        
                $hostNodesArray = $hostNodes->toArray();
        
                // Step 4: Retrieve the count of hosts
                $countPath = $hostNodesArray['Count'] ?? "InternetGatewayDevice.LANDevice.1.Hosts.HostNumberOfEntries";
                $hostCountData = $this->getValueFromArrayByPath($deviceArray, $countPath);

                if (!$hostCountData || !isset($hostCountData['_value']) || $hostCountData['_value'] <= 0) {
                    return response()->json([
                        'success' => true,
                        'data' => [],
                        'message' => 'No hosts found on the device',
                    ]);
                }
        
                $hostCount = $hostCountData['_value'];
                

                // Step 5: Collect host information
                $hosts = [];
                $fields = ['HostName', 'IPAddress', 'MACAddress', 'RSSI'];
                $hostPathTemplate = $hostNodesArray['hostPath'];
        
                for ($i = 1; $i <= $hostCount; $i++) {
                    $hostEntry = [];
        
                    foreach ($fields as $field) {
                        $pathTemplate = $hostNodesArray[$field] ?? null;
        
                        if (!$pathTemplate) {
                            continue; // Skip if field path is not defined
                        }
        
                        $path = str_replace('{i}', $i, $pathTemplate);
        
                        // Fetch the value using the path
                        $hostEntry[$field] = $this->getValueFromArrayByPath($deviceArray, $path)['_value'] ?? null;
                    }
        
                    // Only add the host if it has valid IP and MAC Address
                    if (!empty($hostEntry['IPAddress']) && !empty($hostEntry['MACAddress'])) {
                        $hosts[] = $hostEntry;
                    }
                }
        
                // Step 6: Prepare heatmap data
                $heatmapData = array_map(function ($host) {
                    return [
                        'latitude' => $host['Latitude'] ?? null,
                        'longitude' => $host['Longitude'] ?? null,
                        'signalStrength' => $host['RSSI'] ?? 0,
                        'hostName' => $host['HostName'],
                        'ipAddress' => $host['IPAddress'],
                        'macAddress' => $host['MACAddress'],
                    ];
                }, $hosts);
        
                return response()->json([
                    'success' => true,
                    'data' => $heatmapData,
                    'message' => 'Heatmap data retrieved successfully',
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage(),
                ], 500);
            }
        }
        
        /**
         * Get a value from a nested array using a dot-notated path.
         *
         * @param array $array The array to search.
         * @param string $path The dot-notated path to the value.
         * @return mixed|null The value at the specified path, or null if not found.
         */
        function getValueFromArrayByPath(array $array, string $path)
        {
            $keys = explode('.', $path);
        
            foreach ($keys as $key) {
                if (isset($array[$key])) {
                    $array = $array[$key];
                } else {
                    return null; // Return null if the key doesn't exist
                }
            }
        
            return $array; // Return the final value
        }
        
        
        


    
}
