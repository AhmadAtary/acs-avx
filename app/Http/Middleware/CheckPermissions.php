<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckPermissions
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $module, $action)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'You must be logged in.');
        }

        // Get authenticated user
        $user = Auth::user();

        // Fetch permissions from `access` table based on user ID
        $permissions = DB::table('accesses')
            ->where('user_id', $user->id)
            ->value('permissions'); // Assuming 'permissions' is stored as JSON

        // Convert permissions JSON to an array
        $permissions = json_decode($permissions, true);

        // Debugging (Remove `dd()` after testing)
        // dd($permissions, $module, $action);

        // Check if the module and action exist in permissions
        if (isset($permissions[$module][$action]) && $permissions[$module][$action] === true) {
            return $next($request); // Allow access when permission is `true`
        }

        // If permission is not granted, redirect to 403 error page
        return redirect()->route('Errors.page-error-403');
    }
}
