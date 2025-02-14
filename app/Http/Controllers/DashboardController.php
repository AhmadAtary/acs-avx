<?php
namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DModel;
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

        return view('cs.dashboard');
    }
}
