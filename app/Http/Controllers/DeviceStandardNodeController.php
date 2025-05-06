<?php

namespace App\Http\Controllers;
use App\Models\DeviceStandardNode;
use App\Models\DeviceModel;
use App\Models\Device;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class DeviceStandardNodeController extends Controller
{
    public function getStandardNodes($serialNumber)
    {
        // Step 1: Get the device document from Mongo
        $device = Device::where('_deviceId._SerialNumber', $serialNumber)->first();
        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }
    
        // Step 2: Get the model info
        $productClass = $device->_deviceId['_ProductClass'] ?? null;
        $model = DeviceModel::where('product_class', $productClass)->first();
        if (!$model) {
            return response()->json(['error' => 'Model not found'], 404);
        }
    
        // Step 3: Get all standard node paths
        $paths = DeviceStandardNode::where('device_model_id', $model->id)->pluck('node_path');
    
        $wifiSignals = [];
    
        foreach ($paths as $path) {
            if (!str_contains($path, 'Result')) continue;
    
            $segments = explode('.', $path);
            $basePath = [];
    
            foreach ($segments as $segment) {
                if ($segment === '{i}') break;
                $basePath[] = $segment;
            }
    
            $rootPath = implode('.', $basePath);
            $neighborAPs = data_get($device, $rootPath);
    
            if (!is_array($neighborAPs)) continue;
    
            foreach ($neighborAPs as $ap) {
                if (!is_array($ap)) continue;
    
                $wifiSignals[] = [
                    'SSID'    => $ap['SSID']['_value'] ?? 'N/A',
                    'Signal'  => $ap['Signal']['_value'] ?? 'N/A',
                    'Channel' => $ap['Channel']['_value'] ?? 'N/A',
                    'BSSID'   => $ap['BSSID']['_value'] ?? 'N/A',
                    'Mode'    => $ap['Mode']['_value'] ?? 'N/A',
                ];
            }
    
            break; // We assume data is only in one relevant section
        }
    
        return response()->json($wifiSignals);
    }
    
    public function create()
    {
        $deviceModels = DeviceModel::all();
        return view('Wifi-Standard-Nodes.create', compact('deviceModels'));
    }
    /**
     * Store the wifi standard nodes in the database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            // Log incoming request
            Log::info('Store request received', $request->all());
    
            // Validate input
            $validated = $request->validate([
                'device_model_id' => 'required|exists:device_models,id',
                'base_path' => 'required|string',
                'attributes' => 'required|array|min:1',
                'attributes.*' => 'required|string'
            ]);
    
            DB::beginTransaction();
    
            foreach ($validated['attributes'] as $attribute) {
                $node = DeviceStandardNode::create([
                    'device_model_id' => $validated['device_model_id'],
                    'node_path' => rtrim($validated['base_path'], '.') . '.' . $attribute,
                ]);
    
                // Log each inserted node
                Log::info('Node inserted', $node->toArray());
            }
    
            DB::commit();
    
            return redirect()->route('standard-nodes.create')->with('success', 'Standard nodes inserted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
    
            // Log the error
            Log::error('Error inserting nodes: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
    
            // Optionally display error in dev mode
            if (app()->environment('local')) {
                dd($e->getMessage(), $e->getTrace());
            }
    
            return back()->with('error', 'Failed to insert standard nodes.');
        }
    }
    
    


}
