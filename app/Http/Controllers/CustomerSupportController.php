<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Node;
use App\Models\File;

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

        // dd($serial);
        // Check if serial is null or empty
        if (is_null($serial) || $serial === '') {
            return redirect()->back()->with('error', 'Device not found');
        }

        $device = Device::where('_deviceId._SerialNumber', $serial)->first();

        if (!$device) {
            return redirect()->back()->with('error', 'Device not found');
        }

        $url = $this->url_ID($device); // Generate URL ID for the device
        $url_Id = $url;
        
        $model = $device['_deviceId']['_ProductClass'];
        $nodes = Node::where('Model', $model)->get();
        $id = $device->id;
        // $deviceNote = DeviceNote::where('device_id', $id)->get();

        // Retrieve node values and types for each node path
        $nodeValues = [];
        $nodeTypes = [];
        foreach ($nodes as $node) {
            $path = $node['Path'];
            $nodeKey = $node['Name'];
            $nodeType = $node['Type'];
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

            $nodeValues[$nodeKey] = [
                'value' => $nodeValue,
                'type' => $nodeType,
                'nodeValueType' => $nodeValueType,
                'nodeMode' => $nodeMode,
                'path' => $path
            ];

            $nodeTypes[] = $nodeType;
        }

        // Get unique node types
        $uniqueNodeTypes = array_unique($nodeTypes);
        $productClass = $device->_deviceId['_ProductClass'];
        $swFiles = File::where('metadata.productClass', $productClass)->get();

        // Pass the node values and unique node types to the view
        // dd($nodeValues);
        /*
        #original: array:6 [▼
        "Model" => "LB06HUmniah_1G"
        "Name" => "InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Enable"
        "Type" => "WiFi 2.4GHz"
        "Path" => "InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Enable"
        "NodeType" => "xsd:boolean"
        "id" => MongoDB\BSON\ObjectId {#1301 ▶}
      ]
        */
        return view('CS.device', compact('device', 'nodes', 'nodeValues', 'uniqueNodeTypes', 'url_Id' ,  'swFiles'));

        
        
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
