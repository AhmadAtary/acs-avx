<?php

namespace App\Services;
use App\Models\Device;

use Illuminate\Support\Facades\Http;

class DiagnosticsService
{
    protected $acsUrl;

    public function __construct()
    {
        ini_set('max_execution_time', 180); // allow up to 3 minutes if needed
        $this->acsUrl = config('services.tr069.url');
    }

    public function runPingTest($deviceId, $host)
    {
        return $this->sendDiagnostics($deviceId, [
            ["InternetGatewayDevice.IPPingDiagnostics.Host", $host, "xsd:string"],
            ["InternetGatewayDevice.IPPingDiagnostics.DiagnosticsState", "Requested", "xsd:string"]
        ]);
    }

    public function runTracerouteTest($deviceId, $host)
    {
        return $this->sendDiagnostics($deviceId, [
            ["InternetGatewayDevice.TraceRouteDiagnostics.Host", $host, "xsd:string"],
            ["InternetGatewayDevice.TraceRouteDiagnostics.DiagnosticsState", "Requested", "xsd:string"]
        ]);
    }

    public function runDownloadTest($deviceId, $url)
    {
        return $this->sendDiagnostics($deviceId, [
            ["InternetGatewayDevice.DownloadDiagnostics.DownloadURL", $url, "xsd:string"],
            ["InternetGatewayDevice.DownloadDiagnostics.DiagnosticsState", "Requested", "xsd:string"]
        ]);
    }

    public function runUploadTest($deviceId, $url)
    {
        return $this->sendDiagnostics($deviceId, [
            ["InternetGatewayDevice.UploadDiagnostics.UploadURL", $url, "xsd:string"],
            ["InternetGatewayDevice.UploadDiagnostics.DiagnosticsState", "Requested", "xsd:string"]
        ]);
    }

    private function url_ID($device)
    {
        $device = Device::where('_deviceId._SerialNumber', $device)->first();

        $model = $device['_deviceId']['_ProductClass'];
        $oui = $device['_deviceId']['_OUI'];
        $serial = $device['_deviceId']['_SerialNumber'];

        if (str_contains($model, '-')) {
            $model = str_replace('-', '%252D', $model);
            $url_Id = $oui . '-' . $model . '-' . $serial;
            return $url_Id;
        }elseif(str_contains($model, ' ')) {
            $model = str_replace(' ', '%2520', $model);
            $url_Id = $oui . '-' . $model . '-' . $serial;
            return $url_Id;
        }
        return $device->_id;
    }

    protected function sendDiagnostics($deviceId, $parameterValues)
    {
        // dd($deviceId);
        $url_Id = $this->url_ID($deviceId);
        $apiUrl = "{$this->acsUrl}/devices/{$url_Id}/tasks?connection_request";

        // Construct the request payload
        $payload = [
            'name' => 'setParameterValues',
            'parameterValues' => $parameterValues,
        ];

        // Send the POST request to the external API
        $response = Http::withOptions(['verify' => false]) // Disable SSL verification if needed
                        ->post($apiUrl, $payload);

        return $response->json();
    }

    public function getPingTestResults($deviceId)
    {
        $url_Id = $this->url_ID($deviceId);

        $parameters = [
            'InternetGatewayDevice.IPPingDiagnostics.SuccessCount',
            'InternetGatewayDevice.IPPingDiagnostics.FailureCount',
        ];

        $body = [
            'name' => 'getParameterValues',
            'parameterNames' => $parameters,
        ];

        $apiUrl = "{$this->acsUrl}/devices/{$url_Id}/tasks?connection_request";

        $response = Http::withOptions(['verify' => false]) // Disable SSL verification if needed
                        ->post($apiUrl, $body);

        if (!$response->successful()) {
            throw new \Exception('Failed to request parameter values from server.');
        }

        // Wait for the device to inform back
        sleep(3); // Consider replacing with a more robust polling mechanism

        $device = Device::where('_deviceId._SerialNumber', $deviceId)->first();

        if (!$device) {
            throw new \Exception('Device not found in the database.');
        }

        // Extract success and failure counts from the device data
        $successCount = $device['InternetGatewayDevice']['IPPingDiagnostics']['SuccessCount']['_value'] ?? null;
        $failureCount = $device['InternetGatewayDevice']['IPPingDiagnostics']['FailureCount']['_value'] ?? null;

        return [
            'successCount' => $successCount,
            'failureCount' => $failureCount,
        ];
    }
    
    public function getTracerouteResults($deviceId)
    {
        $url_Id = $this->url_ID($deviceId);

        $parameters = [
            'InternetGatewayDevice.TraceRouteDiagnostics.RouteHopsNumberOfEntries',
        ];

        $body = [
            'name' => 'getParameterValues',
            'parameterNames' => $parameters,
        ];

        $apiUrl = "{$this->acsUrl}/devices/{$url_Id}/tasks?connection_request";

        $maxRetries = 5; // Maximum number of retries
        $retryDelay = 3; // Delay between retries in seconds
        $routeHopsNumber = null;

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            $response = Http::withOptions(['verify' => false]) // Disable SSL verification if needed
                            ->post($apiUrl, $body);

            if ($response->status() === 202) {
                return ['error' => 'Device connection failed. Please ensure the device is online and try again.'];
            }

            if (!$response->successful()) {
                return ['error' => 'Failed to request parameter values from the server. Please try again later.'];
            }

            // Wait for the device to inform back
            sleep($retryDelay);

            $device = Device::where('_deviceId._SerialNumber', $deviceId)->first();

            if (!$device) {
                return ['error' => 'Device not found in the database. Please ensure the device is registered.'];
            }

            $tracerouteData = $device['InternetGatewayDevice']['TraceRouteDiagnostics'] ?? null;

            if ($tracerouteData) {
                $routeHopsNumber = $tracerouteData['RouteHopsNumberOfEntries']['_value'] ?? null;
                if ($routeHopsNumber) {
                    break; // Exit the retry loop if the value is found
                }
            }
        }

        if (!$routeHopsNumber) {
            return ['error' => 'Failed to retrieve RouteHopsNumberOfEntries after multiple attempts. Please check the device and try again.'];
        }

        $parameters = [
            'InternetGatewayDevice.TraceRouteDiagnostics.RouteHops',
        ];

        $body = [
            'name' => 'getParameterValues',
            'parameterNames' => $parameters,
        ];

        $apiUrl = "{$this->acsUrl}/devices/{$url_Id}/tasks?connection_request";

        $response = Http::withOptions(['verify' => false]) // Disable SSL verification if needed
                            ->post($apiUrl, $body);

        if ($response->status() === 202) {
            return ['error' => 'Device connection failed. Please ensure the device is online and try again.'];
        }

        if (!$response->successful()) {
            return ['error' => 'Failed to retrieve traceroute diagnostics data from the server.'];
        }

        // Extract traceroute diagnostics data
        $routeHops = [];
        for ($i = 1; $i <= $routeHopsNumber; $i++) {
            $hop = $tracerouteData["RouteHops"][$i] ?? null;
            if ($hop) {
                $routeHops[] = [
                    'HopErrorCode' => $hop['HopErrorCode']['_value'] ?? null,
                    'HopHost' => $hop['HopHost']['_value'] ?? null,
                    'HopHostAddress' => $hop['HopHostAddress']['_value'] ?? null,
                    'HopRTTimes' => $hop['HopRTTimes']['_value'] ?? null,
                ];
            }
        }

        if (empty($routeHops)) {
            return ['error' => 'No traceroute data available. The device may not have completed the diagnostics.'];
        }

        $formattedRouteHops = [];
        foreach ($routeHops as $index => $hop) {
            $formattedRouteHops[] = sprintf(
                "%d  %s  %s ms",
                $index + 1,
                $hop['HopHostAddress'] ?? '*',
                $hop['HopRTTimes'] ?? '*'
            );
        }

        return $formattedRouteHops;
    }

}
