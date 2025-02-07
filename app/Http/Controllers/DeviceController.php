<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;

class DeviceController extends Controller
{
    public function index(){
        $devices_count = Device::count();
        $devices = Device::select(
            '_deviceId._SerialNumber', 
            '_deviceId._Manufacturer', 
            '_deviceId._OUI', 
            '_deviceId._ProductClass', 
            'InternetGatewayDevice.DeviceInfo.SoftwareVersion._value', 
            'InternetGatewayDevice.DeviceInfo.UpTime._value', 
            '_lastInform'
        )->paginate(200);
        return view('Devices.allDevices', compact('devices_count','devices'));
    }

    public function searchDevices(Request $request)
    {
        $type = $request->get('type');
        $query = $request->get('query');

        $devices = Device::where($type, 'LIKE', "%{$query}%")->get();

        return response()->json($devices);
    }

    public function info($serialNumber)
    {
        $device = Device::where('_deviceId._SerialNumber', $serialNumber)->first();
    
        if ($device) {
            $deviceData = $device->toArray(); // Convert the model to an array
        } else {
            $deviceData = null; // Handle case where no device is found
        }

        // Pass the raw data to the view
        return view('Devices.deviceInfo', compact('deviceData'));
    }

    // public function devices_status(){
    //     $response = [
    //         'devices_count' => 100, // Example data
    //         'online_devices_count' => 80, // Example data
    //         'offline_devices_count' => 20, // Example data
    //     ];

    //     return response()->json($response);
    // }
}
