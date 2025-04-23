<?php

namespace App\Http\Controllers;
use App\Models\SignalReport;

use Illuminate\Http\Request;

class SignalController extends Controller
{
    public function generateSignalReports()
    {
        $devices = Device::with('model.signalNodes')->get();

        foreach ($devices as $device) {
            $deviceData = $device['InternetGatewayDevice'];
            $model = $device->model;

            if (!$model || $model->signalNodes->isEmpty()) {
                continue; // Skip devices with no model or nodes
            }

            $reportData = ['device_id' => (string)$device->_id];

            foreach ($model->signalNodes as $node) {
                $value = Arr::get($deviceData, $node->json_path);

                // Try to get the actual value if it's nested (e.g., ['_value'] inside)
                if (is_array($value) && isset($value['_value'])) {
                    $value = $value['_value'];
                }

                $reportData[strtolower($node->param_name)] = $value;
            }

            // Normalize Cell ID
            if (!empty($reportData['cellid'])) {
                $reportData['cellid'] = $this->normalizeCellID($reportData['cellid']);
            }

            // Save report to SQL DB
            SignalReport::create($reportData);
        }

        return response()->json(['message' => 'Reports generated successfully']);
    }

    private function normalizeCellID($cellID)
    {
        if (is_null($cellID)) return null;

        $cellID = trim($cellID);

        // Handle "0151371 - 001"
        if (str_contains($cellID, '-')) {
            [$enodeb, $sector] = explode('-', $cellID);
            return (int)trim($enodeb) * 256 + (int)trim($sector);
        }

        // Handle hexadecimal values
        if (ctype_xdigit($cellID) && !is_numeric($cellID)) {
            return hexdec($cellID);
        }

        // Default: clean numeric
        return preg_replace('/[^0-9]/', '', $cellID);
    }
}
