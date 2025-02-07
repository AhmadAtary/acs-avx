<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\DModel;


class OwnerController extends Controller
{
    //
    public function index(){
        $devices_count = Device::count();
        $online_devices_count = Device::where('_lastInform', '>=', now()->subMinutes(20))->count();
        $offline_devices_count = $devices_count - $online_devices_count;
        $models = DModel::get();
        $devices = Device::paginate(5);

        return view('Eng.dashboard', compact('devices_count', 'online_devices_count', 'offline_devices_count','models','devices'));
    }
}
