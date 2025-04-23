<?php

namespace App\Http\Controllers;
use App\Models\Device;
use App\Models\DeviceStandardNode;
use App\Helpers\JsonHelper;
use App\Helpers\DeviceHelper;
use App\Models\DeviceModel;
use App\Models\SignalNode;

use Illuminate\Http\Request;

class NetworkController extends Controller
{

    public function create()
    {
        $models = DeviceModel::all(); // Pass all models to view
        return view('signal-nodes.create', compact('models'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'model_id' => 'required|exists:device_models,id',
            'param_name' => 'required|string|max:255',
            'node_path' => 'required|string|max:1000',
        ]);

        SignalNode::create($request->only(['model_id', 'param_name', 'node_path']));

        return redirect()->route('signal-nodes.create')->with('success', 'Network node added successfully!');
    }

    public function storeMultiple(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'model_id' => 'required|exists:device_models,id',
            'nodes' => 'required|array',
            'nodes.*.param_name' => 'required|string|max:255',
            'nodes.*.json_path' => 'required|string|max:1000',
        ]);
    
        try {
            foreach ($request->nodes as $node) {
                SignalNode::create([
                    'model_id' => $request->model_id,
                    'param_name' => $node['param_name'],
                    'node_path' => $node['json_path'],
                ]);
            }
    
            return redirect()->route('signal-nodes.create')->with('success', 'Network nodes added successfully!');
        } catch (\Exception $e) {
            \Log::error('Error storing multiple nodes: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
    
            return redirect()->route('signal-nodes.create')->with('error', 'An error occurred while adding network nodes.');
        }
    }
    

    public function analyzeNetwork()
    {
        // Fetch all devices
        $devices = Device::all();
    
        $signalData = [];
    
        // Define possible configurations for different device structures
        $configs = [
            [
                'node' => 'X_Web.MobileNetwork.SignalStatus',
                'cellID_key' => 'CellID',
                'rsrp_key' => 'RSRP',
                'rsrq_key' => 'RSRQ',
                'sinr_key' => 'SINR',
                'rssi_key' => 'RSSI',
            ],
            [
                'node' => 'WANDevice.1.X_WANNetConfigInfo',
                'cellID_key' => 'Cell_ID',
                'rsrp_key' => 'RSRP',
                'rsrq_key' => 'RSRQ',
                'sinr_key' => 'SINR',
                'rssi_key' => 'RSSI',
            ],
            [
                'node' => 'SystemConfig.Status.ModemStatus',
                'cellID_key' => 'CellID_Hex',
                'rsrp_key' => 'RSRP',
                'rsrq_key' => 'RSRQ',
                'sinr_key' => 'SINR',
                'rssi_key' => 'RSSI',
            ],
        ];
    
        // Helper function to get nested value from a dot-separated path
        function getNestedValue($array, $path)
        {
            $keys = explode('.', $path);
            $current = $array;
            foreach ($keys as $key) {
                if (isset($current[$key])) {
                    $current = $current[$key];
                } else {
                    return null;
                }
            }
            return $current;
        }
    
        // Process each device
        foreach ($devices as $device) {
            $serialNumber = $device['_deviceId']['_SerialNumber'];
            $deviceData = $device['InternetGatewayDevice'];
    
            foreach ($configs as $config) {
                $node = getNestedValue($deviceData, $config['node']);
                if ($node && isset($node[$config['cellID_key']]['_value'])) {
                    // Extract Cell ID and signal strengths from the same node
                    $cellID = $node[$config['cellID_key']]['_value'];
                    $rsrp = $node[$config['rsrp_key']]['_value'] ?? null;
                    $rsrq = $node[$config['rsrq_key']]['_value'] ?? null;
                    $sinr = $node[$config['sinr_key']]['_value'] ?? null;
                    $rssi = $node[$config['rssi_key']]['_value'] ?? null;
    
                    $signalData[] = [
                        'serialNumber' => $serialNumber,
                        'cellID' => $cellID,
                        'RSRP' => $rsrp,
                        'RSRQ' => $rsrq,
                        'SINR' => $sinr,
                        'RSSI' => $rssi,
                    ];
                    break; // Use the first matching configuration
                }
            }
        }
    
        // Convert to collection for easier manipulation
        $signalCollection = collect($signalData);
    
        // Compute summary statistics per Cell ID
        $cellSummary = $signalCollection->groupBy('cellID')->map(function ($group) {
            return [
                'count' => $group->count(),
                'avg_RSRP' => $group->pluck('RSRP')->filter(function ($value) {
                    return is_numeric($value);
                })->avg(),
                'avg_RSRQ' => $group->pluck('RSRQ')->filter(function ($value) {
                    return is_numeric($value);
                })->avg(),
                'avg_SINR' => $group->pluck('SINR')->filter(function ($value) {
                    return is_numeric($value);
                })->avg(),
                'avg_RSSI' => $group->pluck('RSSI')->filter(function ($value) {
                    return is_numeric($value);
                })->avg(),
            ];
        })->sortKeys();
    
        // Sort detailed data by Cell ID
        $signalData = $signalCollection->sortBy('cellID')->values()->all();
    
        // Return view with both detailed and summary data
        return view('Devices.networkAnalysis', [
            'signalData' => $signalData,
            'cellSummary' => $cellSummary,
        ]);
    }
}
