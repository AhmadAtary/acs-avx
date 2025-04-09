<?php
namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceModel;
use App\Models\DeviceCount;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->getRole(); // Fetch role using the helper method in the User model

        switch ($role) {
            case 'owner':
                return $this->ownerDashboard();
            case 'eng':
                return $this->ownerDashboard();
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
}
