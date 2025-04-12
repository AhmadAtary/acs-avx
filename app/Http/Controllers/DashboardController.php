<?php
namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceModel;
use App\Models\DeviceCount;
use App\Models\DeviceUser;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->getRole(); // Fetch role using the helper method in the User model

        // Fetch assigned devices permission from the user's access permissions
        $user_assigned = $user->access->permissions['assign_devices']['assign'] ?? null;

        // Debugging the fetched permission
        // dd($user_assigned);

        switch ($role) {
            case 'owner':
                return $this->ownerDashboard();
            case 'eng':
                if($user_assigned) {
                    return $this->engineerDashboardAssignedDevices();
                }
                return $this->engineerDashboard();
            case 'cs':
                return $this->customerSupportDashboard();
            default:
                abort(403, 'Unauthorized access'); // Redirect unauthorized users
        }
    }

    private function ownerDashboard()
    {
        $devices_count = Device::count();
        $online_devices_count = Device::where('_lastInform', '>=', now()->subMinutes(20))->count();
        $offline_devices_count = $devices_count - $online_devices_count;
        $models = DeviceModel::get();
        $devices = Device::paginate(5);
    
        // ✅ Get the start of the last 60 days
        $startDate = Carbon::now()->subDays(60);
    
        // ✅ Get the start of the current week (Monday)
        $startOfWeek = Carbon::now()->startOfWeek();
    
        // ✅ Get today's date
        $endDate = Carbon::now();
    
        // ✅ Fetch historical device counts within the last 60 days
        $deviceCounts = DeviceCount::whereBetween('Date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('Date', 'ASC')
            ->get()
            ->keyBy('Date'); // ✅ Index by date for easier lookup
    
        // ✅ Generate all dates within the last 60 days
        $dates = [];
        $newDevices = [];
        $currentDate = clone $startDate;
    
        while ($currentDate <= $endDate) {
            $dateString = $currentDate->format('Y-m-d');
            $dates[] = $currentDate->format('Y-m-d'); // ✅ Ensure correct format for JavaScript
            $newDevices[] = $deviceCounts[$dateString]->New_devices ?? 0; // ✅ Fill missing days with 0
            $currentDate->addDay();
        }
    
        return view('Eng.dashboard', compact(
            'devices_count',
            'online_devices_count',
            'offline_devices_count',
            'models',
            'devices',
            'dates',
            'newDevices',
            'startOfWeek' // ✅ Passed back for JavaScript use
        ));
    }
    

    private function engineerDashboard()
    {
        // Example data specific to engineers
        $devices_count = Device::count();
        $online_devices_count = Device::where('_lastInform', '>=', now()->subMinutes(20))->count();
        $offline_devices_count = $devices_count - $online_devices_count;
        $models = DModel::get();
        $devices = Device::paginate(5);

        return view('eng.dashboard', compact(
            'devices_count',
            'online_devices_count',
            'offline_devices_count',
            'models',
            'devices'
        ));
    }

    private function customerSupportDashboard()
    {
        // Example data specific to customer support
        // $tickets = Ticket::where('assigned_to', auth()->id())->paginate(10);

        return view('CS.dashboard');
    }

    private function engineerDashboardAssignedDevices()
{
    // Get all serial numbers assigned to the current user
    $deviceIds = DeviceUser::where('user_id', auth()->id())
        ->pluck('serial_number')
        ->toArray();

    // Fetch only valid serials that actually exist in the Mongo devices collection
    $existingSerials = Device::whereIn('_deviceId._SerialNumber', $deviceIds)
        ->pluck('_deviceId._SerialNumber')
        ->toArray();

    // Remove duplicates
    $existingSerials = array_unique($existingSerials);

    // dd($existingSerials);
    // Fetch the actual device data based on the valid serial numbers only
    $devices = Device::whereIn('_deviceId._SerialNumber', $existingSerials)
        ->select(
            '_deviceId._SerialNumber',
            '_deviceId._Manufacturer',
            '_deviceId._OUI',
            '_deviceId._ProductClass',
            'InternetGatewayDevice.DeviceInfo.SoftwareVersion._value as SoftwareVersion',
            'InternetGatewayDevice.DeviceInfo.UpTime._value as UpTime',
            '_lastInform'
        )
        ->paginate(10);

        // dd($devices);
    return view('Eng.assignedDevices', compact('devices'));
}

    
}
