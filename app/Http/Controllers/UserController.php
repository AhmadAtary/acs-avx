<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Access;
use App\Models\DeviceUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str; // Add this at the top of your controller
use App\Http\Controllers\LogController; 



class UserController extends Controller
{
    // List all users
    public function index()
    {
        LogController::saveLog('user_managment_page', "User opened User Managment page");
        $users = User::with('access')->get(); // Load access relationship
        return view('Users.indexUsers', compact('users'));
    }



    public function store(Request $request)
    {
        // dd($request);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:owner,eng,cs',
            'permissions' => 'nullable|array',
            'assign_devices' => 'nullable|boolean',
            'device_csv' => 'nullable|file|mimes:csv,txt',
        ]);
    
        $authUser = auth()->user();
    
        if (!$authUser->access) {
            return redirect()->back()->with('error', 'You do not have the required access.');
        }
    
        if ($authUser->access->role === 'eng' && $validated['role'] !== 'cs') {
            return redirect()->back()->with('error', 'Engineers can only create Customer Support users.');
        }
    
        $permissions = [
            'bulk_actions' => ['view' => false, 'create' => false, 'delete' => false, 'edit' => false],
            'files_management' => ['view' => false, 'create' => false, 'delete' => false, 'edit' => false],
            'models_management' => ['view' => false, 'create' => false, 'delete' => false, 'edit' => false],
            'user_management' => ['view' => false, 'create' => false, 'delete' => false, 'edit' => false],
            'assign_devices' => ['assign' => false], // New permission
        ];
    
        if ($validated['role'] !== 'cs') {
            foreach ($request->input('permissions', []) as $section => $actions) {
                foreach ($actions as $action => $value) {
                    if (isset($permissions[$section][$action])) {
                        $permissions[$section][$action] = (bool) $value;
                    }
                }
            }
    
            
        }
    

        // Set assign_devices permission
        $permissions['assign_devices']['assign'] = (bool) ($validated['assign_devices'] ?? false);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_otp_verified' => false,
        ]);
    
        Access::create([
            'user_id' => $user->id,
            'account_number' => 'ACC-' . Str::random(8),
            'role' => $validated['role'],
            'permissions' => $permissions
        ]);
    
        // ✅ Process devices CSV file (make sure input name matches: device_csv)
        if ($request->hasFile('device_csv')) {
            $file = $request->file('device_csv');
            $lines = array_map('str_getcsv', file($file->getRealPath()));
            $header = array_map('strtolower', $lines[0]);
            $addedDevices = 0;
            $duplicateDevices = 0;

            foreach (array_slice($lines, 1) as $row) {
            $serialNumber = trim($row[0]);
            if (!empty($serialNumber)) {
                $existingDevice = \App\Models\DeviceUser::where('serial_number', $serialNumber)->first();
                if ($existingDevice) {
                $duplicateDevices++;
                } else {
                \App\Models\DeviceUser::create([
                    'user_id' => $user->id,
                    'serial_number' => $serialNumber,
                ]);
                $addedDevices++;
                }
            }
            }

            return redirect()->back()->with('success', "User and devices created successfully! Added Devices: {$addedDevices}, Duplicate Devices: {$duplicateDevices}");
        }
    
        $logDetails = "User created by {$authUser->name} (ID: {$authUser->id}) - Created User: {$user->name} (ID: {$user->id}), Role: {$validated['role']}, Permissions: " . json_encode($permissions);
        \App\Http\Controllers\LogController::saveLog('user_create_action', $logDetails);
    
        return redirect()->back()->with('success', 'User and devices created successfully!');
    }
    
    
    // Update an existing user
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
    
        // dd($request);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:owner,eng,cs',
            'permissions' => 'nullable|array',
            'assign_devices' => 'nullable|boolean',
            'device_csv' => 'nullable|file|mimes:csv,txt',
        ]);
    
        $authUser = auth()->user();
    
        if (!$authUser->access) {
            return redirect()->back()->with('error', 'You do not have the required access.');
        }
    
        if ($authUser->access->role === 'eng' && $validated['role'] !== 'cs') {
            return redirect()->back()->with('error', 'Engineers can only modify Customer Support users.');
        }
    
        // Update user details
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);
    
        // Default permissions structure
        $permissions = [
            'bulk_actions' => ['view' => false, 'create' => false, 'delete' => false, 'edit' => false],
            'files_management' => ['view' => false, 'create' => false, 'delete' => false, 'edit' => false],
            'models_management' => ['view' => false, 'create' => false, 'delete' => false, 'edit' => false],
            'user_management' => ['view' => false, 'create' => false, 'delete' => false, 'edit' => false],
            'assign_devices' => ['assign' => false],
        ];
    
        // Assign role and permissions
        if ($validated['role'] !== 'cs') {
            foreach ($request->input('permissions', []) as $section => $actions) {
                foreach ($actions as $action => $value) {
                    if (isset($permissions[$section][$action])) {
                        $permissions[$section][$action] = (bool) $value;
                    }
                }
            }
        }
    
        $permissions['assign_devices']['assign'] = (bool) ($validated['assign_devices'] ?? false);
    
        // dd($request->hasFile('device_csv'));
        // Check for file upload if 'assign_devices' is true
        if ($permissions['assign_devices']['assign'] && !$request->hasFile('device_csv')) {
            return redirect()->back()->with('error', 'Device CSV file is required when assigning devices.');
        }
    
        // Update the user's access permissions
        $user->access()->update([
            'role' => $validated['role'],
            'permissions' => $permissions,
        ]);
    
        // Process CSV upload and assign devices
        if ($request->hasFile('device_csv')) {
            // Handle CSV processing and assignment
            $result = $this->processDeviceCsvUpload($request, $user, 'device_csv');
            
            return redirect()->back()->with('success', "User updated and devices assigned! Added Devices: {$result['added']}, Duplicate Devices: {$result['duplicates']}");
        }
    
        return redirect()->back()->with('success', 'User updated successfully!');
    }
    
    
    public function uploadDevices(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'device_file' => 'required|file|mimes:csv,txt',
        ]);
    
        $user = User::findOrFail($request->input('user_id'));
    
        // Remove old device assignments
        $user->devices()->delete();
    
        // Process new device CSV
        $result = $this->processDeviceCsvUpload($request, $user, 'device_file');
    
        $message = 'Devices updated successfully.';
        if ($result['duplicates'] > 0) {
            $message .= " {$result['duplicates']} duplicate serial number(s) were ignored.";
        }
    
        return back()->with('success', $message);
    }
    
    /**
     * Handle CSV upload and assign devices to the given user.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User $user
     * @param string $fieldName
     * @return array ['added' => int, 'duplicates' => int]
     */
    private function processDeviceCsvUpload(Request $request, User $user, string $fieldName): array
    {
        $file = $request->file($fieldName);
        $lines = array_map('str_getcsv', file($file->getRealPath()));
    
        $added = 0;
        $duplicates = 0;
    
        // Skip header if needed
        $rows = count($lines) > 0 && strtolower(trim($lines[0][0])) === 'serial_number'
            ? array_slice($lines, 1)
            : $lines;
    
        foreach ($rows as $row) {
            $serial = trim($row[0] ?? '');
            if (empty($serial)) continue;
    
            $exists = \App\Models\DeviceUser::where('serial_number', $serial)->exists();
    
            if ($exists) {
                $duplicates++;
            } else {
                \App\Models\DeviceUser::create([
                    'user_id' => $user->id,
                    'serial_number' => $serial,
                ]);
                $added++;
            }
        }
    
        return [
            'added' => $added,
            'duplicates' => $duplicates,
        ];
    }
    
    


    // Delete a user
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevent deletion of owner users
        if ($user->access && $user->access->role === 'owner') {
            return redirect()->back()->with('error', 'Cannot delete an owner user!');
        }

        // Delete the access record if it exists
        if ($user->access) {
            $user->access()->delete();
        }

        // Delete the user
        $user->delete();

        return redirect()->back()->with('success', 'User and associated access record deleted successfully!');
    }



}
