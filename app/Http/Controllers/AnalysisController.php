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
        try {
            // Clear existing results
            // NetworkAnalysisResult::truncate();

            $results = [];

            // Process devices in chunks
            Device::chunk(100, function ($devices) use (&$results) {
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
                        if (is_null($value)) {
                            \Log::warning('No value found for node path: ' . $node->node_path);
                            continue;
                        }

                        $paramKey = strtolower($node->param_name);
                        if ($paramKey === 'cell_id') {
                            $entry['cell_id'] = self::normalizeCellId($value);
                            if (is_null($entry['cell_id'])) {
                                \Log::warning('Invalid cell_id for device: ' . json_encode($deviceData));
                                continue 2; // Skip this device
                            }
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
            });

            if (empty($results)) {
                \Log::error('No valid devices processed.');
                return response()->json(['error' => 'No valid devices processed'], 422);
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
                if (isset($result['rsrp']) && is_numeric($result['rsrp'])) {
                    $aggregatedResults[$cellId]['total_rsrp'] += $result['rsrp'];
                    $aggregatedResults[$cellId]['rsrp_count']++;
                }
                if (isset($result['rssi']) && is_numeric($result['rssi'])) {
                    $aggregatedResults[$cellId]['total_rssi'] += $result['rssi'];
                    $aggregatedResults[$cellId]['rssi_count']++;
                }
            }

            // dd($aggregatedResults);
            // Save aggregated results
            $finalResults = [];
            foreach ($aggregatedResults as $cellId => $data) {
                $avg_rsrp = $data['rsrp_count'] > 0 ? $data['total_rsrp'] / $data['rsrp_count'] : null;
                $avg_rssi = $data['rssi_count'] > 0 ? $data['total_rssi'] / $data['rssi_count'] : null;

                if (is_null($avg_rsrp) || is_null($avg_rssi)) {
                    \Log::info("Skipping cell ID $cellId due to missing average values.");
                    continue;
                }

                try {
                    $finalResults[] = NetworkAnalysisResult::create([
                        'cell_id' => $cellId,
                        'device_count' => $data['device_count'],
                        'avg_rsrp' => $avg_rsrp,
                        'avg_rssi' => $avg_rssi,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to save NetworkAnalysisResult for cell ID ' . $cellId . ': ' . $e->getMessage());
                    continue;
                }
            }

            return response()->json(NetworkAnalysisResult::all());
        } catch (\Exception $e) {
            \Log::error('Error in analysis process: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    private static function normalizeCellId($value)
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        if (is_string($value) && str_starts_with(strtolower($value), '0x')) {
            return hexdec($value);
        }
        \Log::warning('Invalid cell ID value: ' . json_encode($value));
        return null;
    }
}