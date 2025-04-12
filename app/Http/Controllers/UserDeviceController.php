<?php

namespace App\Http\Controllers;
use App\Models\User;

use Illuminate\Http\Request;

class UserDeviceController extends Controller
{
    public function assign(Request $request)
    {
        $request->validate([
            'device_file' => 'nullable|file|mimes:csv,txt',
        ]);

        $userId = $request->input('user_id'); // Pass user_id in a hidden input if needed
        $user = User::findOrFail($userId);

        if ($request->has('full_access')) {
            $user->devices()->detach(); // or mark as full access
            return back()->with('success', 'User given full access to all devices.');
        }

        if ($request->hasFile('device_file')) {
            $file = $request->file('device_file');
            $serials = array_map('trim', file($file->getRealPath()));
            
            // Sync or update DB entries here
            $user->devices()->sync(Device::whereIn('serial', $serials)->pluck('id'));
        }

        return back()->with('success', 'Devices assigned successfully.');
    }

    public function export(User $user)
    {
        $serials = $user->devices()->pluck('serial_number')->toArray();
    
        $filename = "user_{$user->id}_devices.csv";
        $handle = fopen('php://output', 'w');
        ob_start();
        foreach ($serials as $serial) {
            fputcsv($handle, [$serial]);
        }
        fclose($handle);
        $content = ob_get_clean();
    
        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }
    
    public function grantFullAccess(Request $request)
    {
        $user = User::findOrFail($request->input('user_id'));
    
        // Clear specific device assignments
        $user->devices()->delete();
    
        // Update the access permission JSON
        $access = $user->access;
        if ($access && is_array($access->permissions)) {
            $permissions = $access->permissions;
        } else {
            $permissions = [];
        }
    
        // Set 'assign_devices.assign' to false
        $permissions['assign_devices']['assign'] = false;
    
        $access->permissions = $permissions;
        $access->save();
    
        return back()->with('success', 'User granted full access to all devices.');
    }
    
    /**
     * Upload devices from a CSV file and assign them to a user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */    

     public function uploadDevices(Request $request)
     {
         $request->validate([
             'user_id' => 'required|exists:users,id',
             'device_file' => 'required|file|mimes:csv,txt',
         ]);
     
         $user = User::findOrFail($request->input('user_id'));
     
         // Read and trim serial numbers
         $serials = array_map('trim', file($request->file('device_file')->getRealPath()));
     
         // Remove empty values
         $serials = array_filter($serials, fn($s) => !empty($s));
     
         // Count duplicates
         $serialCounts = array_count_values($serials);
         $duplicates = array_filter($serialCounts, fn($count) => $count > 1);
         $duplicateCount = array_sum(array_map(fn($count) => $count - 1, $duplicates));
     
         // Keep only unique serials for saving
         $uniqueSerials = array_keys($serialCounts);
     
         // Remove old device assignments
         $user->devices()->delete();
     
         // Assign new devices
         foreach ($uniqueSerials as $serial) {
             $user->devices()->create(['serial_number' => $serial]);
         }
     
         $message = 'Devices updated successfully.';
         if ($duplicateCount > 0) {
             $message .= " {$duplicateCount} duplicate serial number(s) were ignored.";
         }
     
         return back()->with('success', $message);
     }
     


}
