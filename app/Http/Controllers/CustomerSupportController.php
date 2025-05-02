<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\DeviceModel;
use App\Models\Node;
use App\Models\File;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\LogController;

class CustomerSupportController extends Controller
{
    //
    public function getValueFromJson($json, $nodePath)
    {
        $keys = explode('.', $nodePath);
        $currentNode = $json;

        foreach ($keys as $key) {
            if (isset($currentNode[$key])) {
                $currentNode = $currentNode[$key];
            } else {
                // Handle the case where the key is not present in the JSON
                return null;
            }
        }

        return $currentNode;
    }

    public function show(Request $request)
    {
        $serial = $request->query('device_id');
        $userId = auth()->id(); // Get the authenticated user ID

        // Log: User accessed device page
        if (is_null($serial) || $serial === '') {
            LogController::saveLog('Custome_Support_Device_info', "User attempted to access device page with empty device ID.");
            return redirect()->back()->with('error', 'Device not found');
        }



        $device = Device::where('_deviceId._SerialNumber', $serial)->first();

        if (!$device) {
            LogController::saveLog('Custome_Support_Device_info', "Device with Serial Number {$serial} not found.");
            return redirect()->back()->with('error', 'Device not found');
        }

               // Check if the user has permission assigned
               if (auth()->user()->access->permissions['assign_devices']['assign'] === true) {
                $deviceUser = \App\Models\DeviceUser::where('serial_number', $serial)->where('user_id', $userId)->first();

                if (!$deviceUser) {
                LogController::saveLog('Custome_Support_Device_info', "User {$userId} attempted to access device {$serial} without proper assignment.");
                return redirect()->back()->with('error', 'You do not have permission to access this device.');
                }
            }

        $url_Id = $this->url_ID($device); // Generate URL ID for the device


        // Fetch device model ID
        $model = DeviceModel::where('product_class', $device['_deviceId']['_ProductClass'])->first();

        if (!$model) {
            return redirect()->back()->with('error', 'Device model not found.');
        }

        // Fetch CS Nodes using the model_id
        $csNodes = Node::where('device_model_id', $model->id)->get();

        if ($csNodes->isEmpty() && auth()->user()->access->role === 'cs') {
            LogController::saveLog('no_cs_nodes', "No Customer Service Nodes found for device model: {$device['_deviceId']['_ProductClass']}");
            return redirect()->back()->with('error', 'No Customer Service Nodes available for this device model. Please contact the System Administrator.');
        }

        // Group nodes by category for correct tab display
        $nodeCategories = [];
        $nodeValues = [];

        foreach ($csNodes as $node) {
            $category = $node->category ?? 'Unknown';
            $path = $node->path ?? null;
            $nodeKey = $node->name ?? null;
            $nodeType = $node->type ?? 'Unknown';

            if (!$nodeKey || !$path) {
                continue; // Skip if missing required fields
            }

            $nodeValue = $this->getValueFromJson($device->toArray(), $path);

            // Ensure nodeValue is correctly formatted
            $nodeValueType = null;
            $nodeMode = null;

            if (is_array($nodeValue) && isset($nodeValue['_value'])) {
                $nodeValueType = $nodeValue['_type'] ?? null;
                $nodeMode = $nodeValue['_writable'] ?? null;
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
                'path' => $path
            ];

            // Organize nodes by category for correct display
            $nodeCategories[$category][] = $nodeKey;
        }

        $uniqueNodeTypes = array_keys($nodeCategories); // Extract unique categories

        // Fetch Software Files based on device Product Class
        $productClass = $device->_deviceId['_ProductClass'];
        $swFiles = File::where('metadata.productClass', $productClass)->get();

        // Log: Successful device page load with all relevant data
        LogController::saveLog('Custome_Support_Device_info', "Device Info page loaded successfully with Serial Number: {$serial}");

        return view('CS.device', compact('device', 'nodeCategories', 'nodeValues', 'uniqueNodeTypes', 'url_Id', 'swFiles'));
    }


    public function manage(Request $request)
    {
        
        // Debug: Log incoming request data
        Log::debug('Manage Request Data:', $request->all());

        $userId = auth()->id(); // Get the authenticated user ID
        $url_id = $request->input('url_Id');
        $device_id = $request->input('device_id');
        $action = $request->input('action');
        $nodes = $request->input('nodes');

        // Log: User is attempting to manage device
        LogController::saveLog('Custome_Support_Device_Action', "CS User attempted to manage device: {$device_id} with action: {$action}");

        // Validate nodes
        if (!$nodes || !is_array($nodes)) {
            LogController::saveLog('Custome_Support_Device_Action_failed', "Invalid nodes provided for device management (device ID: {$device_id})");
            return redirect()->back()->with('error', 'No valid nodes found in request.');
        }

        $client = new Client(['verify' => false]); // Disable SSL verification
        $api_url = "https://10.106.45.1:7557/devices/{$url_id}/tasks?connection_request";


        try {
            if ($action == 'GET') {
                $parameter_names = array_keys($nodes);


                if (empty($parameter_names)) {
                    LogController::saveLog('Custome_Support_Device_Action_failed', "No parameters selected for GET request (device ID: {$device_id})");
                    return redirect()->back()->with('error', 'No parameters selected for GET request.');
                }

                $json_body = [
                    'device' => $device_id,
                    'name' => 'getParameterValues',
                    'parameterNames' => $parameter_names,
                ];
            } elseif ($action == 'SET') {
                $parameter_values = [];

                foreach ($nodes as $nodePath => $nodeData) {
                    if (isset($nodeData['value'])) {
                        $parameter_values[] = [$nodePath, $nodeData['value']];
                    }
                }


                if (empty($parameter_values)) {
                    LogController::saveLog('Custome_Support_Device_Action_failed', "No writable parameters selected for SET request (device ID: {$device_id})");
                    return redirect()->back()->with('error', 'No writable parameters selected for SET request.');
                }

                $json_body = [
                    'device' => $device_id,
                    'name' => 'setParameterValues',
                    'parameterValues' => $parameter_values,
                ];
            } else {
                LogController::saveLog('Custome_Support_Device_Action_failed', "Invalid action specified for device management (device ID: {$device_id})");
                return redirect()->back()->with('error', 'Invalid action specified.');
            }



            $response = $client->post($api_url, ['json' => $json_body]);

            // Log: API request being sent
            LogController::saveLog('Custome_Support_Device_Action', "CS User Sent {$action} to {$device_id} 'Nodes' => $parameter_values");




            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();



            if ($statusCode == 200) {
                LogController::saveLog('Custome_Support_Device_Action', "API response received for device management (device ID: {$device_id}) 'status' => 'Operation completed successfully' ");
                return redirect()->back()->with('success', 'Operation completed successfully.');
            } elseif ($statusCode == 202) {
                LogController::saveLog('Custome_Support_Device_Action', "API response received for device management (device ID: {$device_id}) 'status' => 'Pending as Task'");
                return redirect()->back()->with('task', 'Pending as Task, Check Device Connection.');
            }

            return redirect()->back()->with('error', 'Unexpected API response received.');
        } catch (RequestException $e) {
            // Debug: Log RequestException details
            Log::error('Guzzle RequestException:', [
                'message' => $e->getMessage(),
                'response' => $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null,
            ]);

            return redirect()->back()->with('error', 'API request failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            // Debug: Log general exception details
            Log::error('General Exception:', ['message' => $e->getMessage()]);

            return redirect()->back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
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
            // return str_replace('-', '%252', $model);
        }
        return $device->_id;
    }



}
