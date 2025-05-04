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
        // Clear existing results
        NetworkAnalysisResult::truncate();

        $devices = Device::all();
        $results = [];

        if ($devices->isEmpty()) {
            \Log::error('No devices found in the database.');
            return response()->json(['error' => 'No devices found'], 404);
        }

        foreach ($devices as $device) {
            $deviceData = json_decode(json_encode($device), true);

            $productClass = data_get($deviceData, '_deviceId._ProductClass');

            if (!$productClass) {
                \Log::error('Device missing _ProductClass: ' . json_encode($deviceData));
                continue;
            }

            $model = DeviceModel::where('product_class', $productClass)->first();

            if (!$model) {
                \Log::error('No model found for product class: ' . $productClass);
                continue;
            }

            $signalNodes = SignalNode::where('model_id', $model->id)->get();

            if ($signalNodes->isEmpty()) {
                \Log::error('No signal nodes found for model ID: ' . $model->id);
                continue;
            }

            $entry = [
                'device_id' => $device->_id ?? null,
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

        // Aggregate signal data per cell_id
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

        // Save aggregated results
        $finalResults = [];

        foreach ($aggregatedResults as $cellId => $data) {
            $avg_rsrp = $data['rsrp_count'] > 0 ? $data['total_rsrp'] / $data['rsrp_count'] : null;
            $avg_rssi = $data['rssi_count'] > 0 ? $data['total_rssi'] / $data['rssi_count'] : null;

            if (is_null($avg_rsrp) || is_null($avg_rssi)) {
                \Log::info("Skipping cell ID $cellId due to missing average values.");
                continue;
            }
            
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
