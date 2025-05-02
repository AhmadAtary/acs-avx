<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceModel;
use App\Models\SignalNode;
use App\Models\NetworkAnalysisResult;
use Illuminate\Http\Request;


class AnalysisController extends Controller
{
    public function index()
    {
        return view('analysis.index');
    }

    public function process(Request $request)
    {
        if (!$request->has('refresh')) {
            
            return response()->json(NetworkAnalysisResult::all());
        }
    
       
        NetworkAnalysisResult::truncate();
    
        $devices = Device::all();
        $results = [];
    
        foreach ($devices as $device) {
            $model = DeviceModel::where('product_class', $device->_deviceId['_ProductClass'])->first();
    
            if (!$model) continue;
    
            $signalNodes = SignalNode::where('model_id', $model->id)->get();
            $deviceData = is_array($device) ? $device : json_decode(json_encode($device), true);
    
            $entry = [
                'device_id' => $device->_id,
                'model_name' => $model->model_name,
            ];
    
            foreach ($signalNodes as $node) {
                $value = data_get($deviceData, $node->node_path . '._value');
                $paramKey = strtolower($node->param_name);
    
                if ($paramKey === 'cell_id') {
                    $entry['cell_id'] = self::normalizeCellId($value);
                } elseif ($paramKey === 'rsrp') {
                    $entry['rsrp'] = $value;
                } elseif ($paramKey === 'rssi') {
                    $entry['rssi'] = $value;
                } else {
                    $entry[$paramKey] = $value;
                }
            }
    
            $results[] = $entry;
        }
    
        $aggregatedResults = [];
    
        foreach ($results as $result) {
            if (!isset($result['cell_id'])) continue;
    
            $cellId = $result['cell_id'];
    
            if (!isset($aggregatedResults[$cellId])) {
                $aggregatedResults[$cellId] = [
                    'device_count' => 0,
                    'total_rsrp' => 0,
                    'total_rssi' => 0,
                    'rsrp_count' => 0,
                    'rssi_count' => 0,
                ];
            }
    
            $aggregatedResults[$cellId]['device_count']++;
    
            if (isset($result['rsrp'])) {
                $aggregatedResults[$cellId]['total_rsrp'] += $result['rsrp'];
                $aggregatedResults[$cellId]['rsrp_count']++;
            }
    
            if (isset($result['rssi'])) {
                $aggregatedResults[$cellId]['total_rssi'] += $result['rssi'];
                $aggregatedResults[$cellId]['rssi_count']++;
            }
        }
    
        $finalResults = [];
    
        foreach ($aggregatedResults as $cellId => $data) {
            $avg_rsrp = $data['rsrp_count'] > 0 ? $data['total_rsrp'] / $data['rsrp_count'] : null;
            $avg_rssi = $data['rssi_count'] > 0 ? $data['total_rssi'] / $data['rssi_count'] : null;
    
            $finalResults[] = NetworkAnalysisResult::create([
                'cell_id' => $cellId,
                'device_count' => $data['device_count'],
                'avg_rsrp' => $avg_rsrp,
                'avg_rssi' => $avg_rssi,
            ]);
        }
    
        return response()->json(NetworkAnalysisResult::all());
    }
    
    private static function normalizeCellId($value)
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value) && str_starts_with(strtolower($value), '0x')) {
            return hexdec($value);
        }

        return $value;
    }
}
