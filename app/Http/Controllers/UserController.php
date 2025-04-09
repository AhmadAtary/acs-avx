<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Access;
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

    // Store a new user
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:owner,eng,cs',
            'permissions' => 'nullable|array'
        ]);
    
        $authUser = auth()->user();
    
        // Check if the user has access
        if (!$authUser->access) {
            return redirect()->back()->with('error', 'You do not have the required access.');
        }
    
        // Ensure that Engineers can only create Customer Support users
        if ($authUser->access->role === 'eng' && $validated['role'] !== 'cs') {
            return redirect()->back()->with('error', 'Engineers can only create Customer Support users.');
        }
    
        // Default permissions structure
        $permissions = [
            'bulk_actions' => ['view' => false, 'create' => false, 'delete' => false, 'edit' => false],
            'files_management' => ['view' => false, 'create' => false, 'delete' => false, 'edit' => false],
            'models_management' => ['view' => false, 'create' => false, 'delete' => false, 'edit' => false],
            'user_management' => ['view' => false, 'create' => false, 'delete' => false, 'edit' => false],
        ];
    
        // If the role is not 'cs', apply selected permissions
        if ($validated['role'] !== 'cs') {
            foreach ($request->input('permissions', []) as $section => $actions) {
                foreach ($actions as $action => $value) {
                    $permissions[$section][$action] = (bool) $value;
                }
            }
        }
    
        // Create the user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_otp_verified' => false,
        ]);
    
        // Create the access record for the user
        $permissions_json = Access::create([
            'user_id' => $user->id,
            'account_number' => 'ACC-' . Str::random(8),
            'role' => $validated['role'],
            'permissions' => $permissions
        ]);
    
        // Log the user creation using LogController's saveLog function
        $logDetails = "User created by {$authUser->name} (ID: {$authUser->id}) - Created User: {$user->name} (ID: {$user->id}), Role: {$validated['role']}, Permissions: " . json_encode($permissions);
    
        // Call LogController's saveLog method to save the log
        LogController::saveLog('user_create_action', $logDetails);
    
        // Return success response
        return redirect()->back()->with('success', 'User created successfully!');
    }
    
    

    
    // Update an existing user
    public function update(Request $request, $id)
    {
        // dd($request);
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:owner,eng,cs',
            'permissions' => 'nullable|array'
        ]);

        $authUser = auth()->user();

        if (!$authUser->access) {
            return redirect()->back()->with('error', 'You do not have the required access.');
        }

        // Ensure Engineers cannot change a user's role to Owner or Engineer
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
        ];

        // If Owner or Engineer, apply selected permissions
        if ($validated['role'] !== 'cs') {
            foreach ($request->input('permissions', []) as $section => $actions) {
                foreach ($actions as $action => $value) {
                    $permissions[$section][$action] = (bool) $value;
                }
            }
        }

        // Update role & permissions in the access table
        $user->access()->update([
            'role' => $validated['role'],
            'permissions' => json_encode($permissions),
        ]);

        return redirect()->back()->with('success', 'User updated successfully!');
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
