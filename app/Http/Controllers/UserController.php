<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Access;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str; // Add this at the top of your controller


class UserController extends Controller
{
    // List all users
    public function index()
    {
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
        ]);
    
        $authUser = auth()->user();
    
        // Check if the authenticated user has an access record
        if (!$authUser->access) {
            return redirect()->back()->with('error', 'You do not have the required access.');
        }
    
        // Check if account_limit is reached
        $currentUserCount = User::count(); // Get the current total number of users
        $accountLimit = $authUser->access->account_limit;
    
        if ($currentUserCount >= $accountLimit) {
            return redirect()->back()->with('error', 'Account limit reached. Cannot add more users.');
        }
    
        // Create the user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_otp_verified' => false,
        ]);
    
        // Create the access record
        Access::create([
            'user_id' => $user->id,
            'account_number' => 'ACC-' . Str::random(8), // Generate a unique account number
            'role' => $validated['role'],
            'permissions' => json_encode([
                'create_user' => $validated['role'] === 'owner',
                'update_user' => true,
                'delete_user' => $validated['role'] === 'owner',
                'view_user' => true,
            ]),
        ]);
    
        return redirect()->back()->with('success', 'User created successfully!');
    }
    


    // Update an existing user
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:owner,eng,cs', // Ensure role is one of the allowed values
        ]);

        // Update user details
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Update role in the access table
        $user->access()->update([
            'role' => $validated['role'],
            'permissions' => json_encode([
                'create_user' => $validated['role'] === 'owner',
                'update_user' => true,
                'delete_user' => $validated['role'] === 'owner',
                'view_user' => false,
            ]),
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
