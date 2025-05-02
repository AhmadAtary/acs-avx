<?php

namespace App\Http\Controllers\enduser;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceModel;
use App\Models\Node;
use App\Models\EndUserLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class End_user_DeviceController extends Controller
{
    public function show($url_Id)
    {
        // Validate url_Id to prevent invalid values
        if (!is_string($url_Id) || empty($url_Id) || str_contains($url_Id, 'object')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid device ID.'
            ], 400);
        }

        $device = Device::where('_deviceId._SerialNumber', $url_Id)->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found.'
            ], 404);
        }

        // Fetch device model ID
        $model = DeviceModel::where('product_class', $device['_deviceId']['_ProductClass'])->first();

        if (!$model) {
            return response()->json([
                'success' => false,
                'message' => 'Device model not found.'
            ], 404);
        }

        // Fetch CS Nodes using the model_id (treating end-user as CS)
        $csNodes = Node::where('device_model_id', $model->id)->get();

        if ($csNodes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No nodes available for this device model.'
            ], 404);
        }

        // Group nodes by category for tab display
        $nodeCategories = [];
        $nodeValues = [];

        foreach ($csNodes as $node) {
            $category = $node->category ?? 'Other';
            $path = $node->path ?? null;
            $nodeKey = $node->name ?? null;
            $nodeType = $node->type ?? 'string';

            if (!$nodeKey || !$path) {
                continue; // Skip if missing required fields
            }

            $nodeValue = $this->getValueFromJson($device->toArray(), $path);

            // Format node value
            $nodeValueType = null;
            $nodeMode = null;

            if (is_array($nodeValue) && isset($nodeValue['_value'])) {
                $nodeValueType = $nodeValue['_type'] ?? 'string';
                $nodeMode = $nodeValue['_writable'] ?? false;
                $nodeValue = $nodeValue['_value'];
            } elseif (is_array($nodeValue)) {
                $nodeValue = json_encode($nodeValue);
            }

            // Store formatted node values
            $nodeValues[$nodeKey] = [
                'value' => $nodeValue ?? 'No value found',
                'type' => $nodeType,
                'nodeValueType' => $nodeValueType,
                'nodeMode' => $nodeMode,
                'path' => $path,
            ];

            // Organize nodes by category
            $nodeCategories[$category][] = $nodeKey;
        }

        $uniqueNodeTypes = array_keys($nodeCategories);

        return view('End-user-link.device_info', compact('device', 'uniqueNodeTypes', 'nodeCategories', 'nodeValues', 'url_Id'));
    }

    public function updateNodes(Request $request)
    {
        $validated = $request->validate([
            'serialNumber' => 'required|string',
            'action' => 'required|string|in:GET,SET',
            'nodes' => 'required|array',
            'nodes.*.path' => 'string|nullable',
            'nodes.*.value' => 'string|nullable',
            'nodes.*.type' => 'string|nullable',
        ]);

        $serialNumber = $validated['serialNumber'];
        $action = $validated['action'];
        $nodes = $validated['nodes'];

        try {
            if ($action === 'GET') {
                $response = $this->getNodeValue(new Request([
                    'serialNumber' => $serialNumber,
                    'nodes' => $nodes,
                ]));
                return response()->json($response->getData(true));
            } elseif ($action === 'SET') {
                $response = $this->setNodeValue(new Request([
                    'serialNumber' => $serialNumber,
                    'nodes' => $nodes,
                ]));
                return response()->json($response->getData(true));
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating nodes: ' . $e->getMessage(),
            ], 500);
        }
    }

    // public function generateLink(Request $request)
    // {
    //     $validated = $request->validate([
    //         'link' => 'required|string',
    //         'username' => 'required|string',
    //         'password' => 'required|string',
    //         'expires_at' => 'required|date',
    //     ]);

    //     $link = $validated['link'];
    //     $username = $validated['username'];
    //     $password = $validated['password'];
    //     $expiresAt = Carbon::parse($validated['expires_at']);

    //     try {
    //         EndUserLink::create([
    //             'token' => Str::afterLast($link, '/'),
    //             'username' => $username,
    //             'password' => bcrypt($password),
    //             'expires_at' => $expiresAt,
    //             'is_used' => false,
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'End-user link generated successfully',
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to generate end-user link: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function getNodeValue(Request $request)
    {
        $validated = $request->validate([
            'serialNumber' => 'required|string',
            'nodes' => 'required|array',
        ]);

        $serialNumber = $validated['serialNumber'];
        $nodes = $validated['nodes'];

        // Validate nodes
        if (empty($nodes) || !is_array($nodes)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid nodes found in request.',
            ], 400);
        }

        try {
            $device = Device::where('_deviceId._SerialNumber', $serialNumber)->first();

            if (!$device || empty($device['_deviceId']['_OUI']) || empty($device['_deviceId']['_ProductClass']) || empty($device['_deviceId']['_SerialNumber'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Device with serial number $serialNumber not found or incomplete device ID information.",
                ], 404);
            }

            $url_Id = $this->url_ID($device);
            $deviceId = $device->_id;

            // Prepare parameter names
            $parameter_names = [];
            foreach ($nodes as $nodeKey => $nodeData) {
                $path = $nodeData['path'] ?? $nodeKey;
                if (!empty($path)) {
                    $parameter_names[] = $path;
                }
            }

            if (empty($parameter_names)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No parameters selected for GET request.',
                ], 400);
            }

            $client = new Client(['verify' => false]);
            $api_url = "https://10.106.45.1:7557/devices/{$url_Id}/tasks?connection_request";

            $json_body = [
                'device' => $deviceId,
                'name' => 'getParameterValues',
                'parameterNames' => $parameter_names,
            ];

            $response = $client->post($api_url, ['json' => $json_body]);
            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                // Fetch updated device data
                $latestDeviceData = Device::where('_deviceId._SerialNumber', $serialNumber)->first();
                $results = [];

                foreach ($parameter_names as $path) {
                    $value = $this->getValueFromJson($latestDeviceData->toArray(), $path);
                    if (is_array($value) && isset($value['_value'])) {
                        $value = $value['_value'];
                    }
                    $results[$path] = [
                        'value' => $value ?? 'No value found',
                        'success' => !is_null($value),
                    ];
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Node values fetched successfully',
                    'status_code' => $statusCode,
                    'results' => $results,
                    'response_data' => json_decode($response->getBody()->getContents(), true),
                ]);
            } elseif ($statusCode === 202) {
                return response()->json([
                    'success' => true,
                    'message' => "Value fetch request accepted as a task for nodes: " . implode(', ', $parameter_names),
                    'status_code' => $statusCode,
                    'data' => json_decode($response->getBody()->getContents(), true),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Failed to fetch values: " . $response->getBody()->getContents(),
                    'status_code' => $statusCode,
                ], $statusCode);
            }
        } catch (RequestException $e) {
            return response()->json([
                'success' => false,
                'message' => "API request failed: " . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "An error occurred while fetching the values: " . $e->getMessage(),
            ], 500);
        }
    }

    public function setNodeValue(Request $request)
    {
        $validated = $request->validate([
            'serialNumber' => 'required|string',
            'nodes' => 'required|array',
        ]);

        $serialNumber = $validated['serialNumber'];
        $nodes = $validated['nodes'];

        // Validate nodes
        if (empty($nodes) || !is_array($nodes)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid nodes found in request.',
            ], 400);
        }

        try {
            $device = Device::where('_deviceId._SerialNumber', $serialNumber)->first();

            if (!$device || empty($device['_deviceId']['_OUI']) || empty($device['_deviceId']['_ProductClass']) || empty($device['_deviceId']['_SerialNumber'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Device with serial number $serialNumber not found or incomplete device ID information.",
                ], 404);
            }

            $url_Id = $this->url_ID($device);
            $deviceId = $device->_id;

            // Prepare parameter values
            $parameter_values = [];
            foreach ($nodes as $nodeKey => $nodeData) {
                $path = $nodeData['path'] ?? $nodeKey;
                if (isset($nodeData['value']) && !empty($path)) {
                    $parameter_values[] = [$path, $nodeData['value']];
                }
            }

            if (empty($parameter_values)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No writable parameters selected for SET request.',
                ], 400);
            }

            $client = new Client(['verify' => false]);
            $api_url = "https://10.106.45.1:7557/devices/{$url_Id}/tasks?connection_request";

            $json_body = [
                'device' => $deviceId,
                'name' => 'setParameterValues',
                'parameterValues' => $parameter_values,
            ];

            $response = $client->post($api_url, ['json' => $json_body]);
            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                return response()->json([
                    'success' => true,
                    'message' => 'Node values set successfully',
                    'status_code' => $statusCode,
                    'data' => json_decode($response->getBody()->getContents(), true),
                ]);
            } elseif ($statusCode === 202) {
                return response()->json([
                    'success' => true,
                    'message' => "Value set request accepted as a task for nodes",
                    'status_code' => $statusCode,
                    'data' => json_decode($response->getBody()->getContents(), true),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Failed to set values: " . $response->getBody()->getContents(),
                    'status_code' => $statusCode,
                ], $statusCode);
            }
        } catch (RequestException $e) {
            return response()->json([
                'success' => false,
                'message' => "API request failed: " . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "An error occurred while setting the values: " . $e->getMessage(),
            ], 500);
        }
    }

    private function getValueFromJson($json, $nodePath)
    {
        $keys = explode('.', $nodePath);
        $currentNode = $json;

        foreach ($keys as $key) {
            if (isset($currentNode[$key])) {
                $currentNode = $currentNode[$key];
            } else {
                return null;
            }
        }

        return $currentNode;
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
        } elseif (str_contains($model, ' ')) {
            $model = str_replace(' ', '%2520', $model);
            $url_Id = $oui . '-' . $model . '-' . $serial;
            return $url_Id;
        }
        return $device->_id;
    }
}
